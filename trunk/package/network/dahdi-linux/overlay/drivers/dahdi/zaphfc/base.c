/*
 * zaphfc.c - Dahdi driver for HFC-S PCI A based ISDN BRI cards
 *
 * Dahdi rewrite in hardhdlc mode
 * Jose A. Deniz <odicha@hotmail.com>
 *
 * Copyright (C) 2009, Jose A. Deniz
 * Copyright (C) 2006, headiisue GmbH; Jens Wilke
 * Copyright (C) 2004 Daniele Orlandi
 * Copyright (C) 2002, 2003, 2004, Junghanns.NET GmbH
 *
 * Jens Wilke <jw_vzaphfc@headissue.com>
 *
 * Original author of this code is
 * Daniele "Vihai" Orlandi <daniele@orlandi.com>
 *
 * Major rewrite of the driver made by
 * Klaus-Peter Junghanns <kpj@junghanns.net>
 *
 * This program is free software and may be modified and
 * distributed under the terms of the GNU Public License.
 *
 * Please read the README file for important infos.
 */

#include <linux/spinlock.h>
#include <linux/init.h>
#include <linux/pci.h>
#include <linux/interrupt.h>
#include <linux/module.h>
#include <linux/moduleparam.h>
#include <linux/version.h>
#include <linux/kernel.h>
#include <linux/delay.h>
#include <linux/sched.h>
#include <linux/proc_fs.h>
#include <linux/if_arp.h>

#include <dahdi/kernel.h>

#include "zaphfc.h"
#include "fifo.h"

#if CONFIG_PCI

#define DAHDI_B1 0
#define DAHDI_B2 1
#define DAHDI_D 2

#define D 0
#define B1 1
#define B2 2

/*
 * Mode Te for all
 */
static int modes;
static int nt_modes[hfc_MAX_BOARDS];
static int nt_modes_count;
static int force_l1_up;
static struct proc_dir_entry *hfc_proc_zaphfc_dir;

#ifdef DEBUG
int debug_level;
#endif

#ifndef FALSE
#define FALSE 0
#endif
#ifndef TRUE
#define TRUE (!FALSE)
#endif

#if LINUX_VERSION_CODE < KERNEL_VERSION(2,6,30)
#define	SET_PROC_DIRENTRY_OWNER(p)	do { (p)->owner = THIS_MODULE; } while(0);
#else
#define	SET_PROC_DIRENTRY_OWNER(p)	do { } while(0);
#endif

static struct pci_device_id hfc_pci_ids[] = {
	{PCI_VENDOR_ID_CCD, PCI_DEVICE_ID_CCD_2BD0,
		PCI_ANY_ID, PCI_ANY_ID, 0, 0, 0},
	{PCI_VENDOR_ID_CCD, PCI_DEVICE_ID_CCD_B000,
		PCI_ANY_ID, PCI_ANY_ID, 0, 0, 0},
	{PCI_VENDOR_ID_CCD, PCI_DEVICE_ID_CCD_B006,
		PCI_ANY_ID, PCI_ANY_ID, 0, 0, 0},
	{PCI_VENDOR_ID_CCD, PCI_DEVICE_ID_CCD_B007,
		PCI_ANY_ID, PCI_ANY_ID, 0, 0, 0},
	{PCI_VENDOR_ID_CCD, PCI_DEVICE_ID_CCD_B008,
		PCI_ANY_ID, PCI_ANY_ID, 0, 0, 0},
	{PCI_VENDOR_ID_CCD, PCI_DEVICE_ID_CCD_B009,
		PCI_ANY_ID, PCI_ANY_ID, 0, 0, 0},
	{PCI_VENDOR_ID_CCD, PCI_DEVICE_ID_CCD_B00A,
		PCI_ANY_ID, PCI_ANY_ID, 0, 0, 0},
	{PCI_VENDOR_ID_CCD, PCI_DEVICE_ID_CCD_B00B,
		PCI_ANY_ID, PCI_ANY_ID, 0, 0, 0},
	{PCI_VENDOR_ID_CCD, PCI_DEVICE_ID_CCD_B00C,
		PCI_ANY_ID, PCI_ANY_ID, 0, 0, 0},
	{PCI_VENDOR_ID_CCD, PCI_DEVICE_ID_CCD_B100,
		PCI_ANY_ID, PCI_ANY_ID, 0, 0, 0},
	{PCI_VENDOR_ID_ABOCOM, PCI_DEVICE_ID_ABOCOM_2BD1,
		PCI_ANY_ID, PCI_ANY_ID, 0, 0, 0},
	{PCI_VENDOR_ID_ASUSTEK, PCI_DEVICE_ID_ASUSTEK_0675,
		PCI_ANY_ID, PCI_ANY_ID, 0, 0, 0},
	{PCI_VENDOR_ID_BERKOM, PCI_DEVICE_ID_BERKOM_T_CONCEPT,
		PCI_ANY_ID, PCI_ANY_ID, 0, 0, 0},
	{PCI_VENDOR_ID_BERKOM, PCI_DEVICE_ID_BERKOM_A1T,
		PCI_ANY_ID, PCI_ANY_ID, 0, 0, 0},
	{PCI_VENDOR_ID_ANIGMA, PCI_DEVICE_ID_ANIGMA_MC145575,
		PCI_ANY_ID, PCI_ANY_ID, 0, 0, 0},
	{PCI_VENDOR_ID_ZOLTRIX, PCI_DEVICE_ID_ZOLTRIX_2BD0,
		PCI_ANY_ID, PCI_ANY_ID, 0, 0, 0},
	{PCI_VENDOR_ID_DIGI, PCI_DEVICE_ID_DIGI_DF_M_IOM2_E,
		PCI_ANY_ID, PCI_ANY_ID, 0, 0, 0},
	{PCI_VENDOR_ID_DIGI, PCI_DEVICE_ID_DIGI_DF_M_E,
		PCI_ANY_ID, PCI_ANY_ID, 0, 0, 0},
	{PCI_VENDOR_ID_DIGI, PCI_DEVICE_ID_DIGI_DF_M_IOM2_A,
		PCI_ANY_ID, PCI_ANY_ID, 0, 0, 0},
	{PCI_VENDOR_ID_DIGI, PCI_DEVICE_ID_DIGI_DF_M_A,
		PCI_ANY_ID, PCI_ANY_ID, 0, 0, 0},
	{PCI_VENDOR_ID_SITECOM, PCI_DEVICE_ID_SITECOM_3069,
		PCI_ANY_ID, PCI_ANY_ID, 0, 0, 0},
	{0,}
};

MODULE_DEVICE_TABLE(pci, hfc_pci_ids);

static int __devinit hfc_probe(struct pci_dev *dev
			, const struct pci_device_id *ent);
static void __devexit hfc_remove(struct pci_dev *dev);

static struct pci_driver hfc_driver = {
	.name     = hfc_DRIVER_NAME,
	.id_table = hfc_pci_ids,
	.probe    = hfc_probe,
	.remove   = hfc_remove,
};

/******************************************
 * HW routines
 ******************************************/

static void hfc_softreset(struct hfc_card *card)
{
	printk(KERN_INFO hfc_DRIVER_PREFIX
		"card %d: "
		"resetting\n",
		card->cardnum);

/*
 * Softreset procedure. Put it on, wait and off again
 */
	hfc_outb(card, hfc_CIRM, hfc_CIRM_RESET);
	udelay(6);
	hfc_outb(card, hfc_CIRM, 0);

	set_current_state(TASK_UNINTERRUPTIBLE);
	schedule_timeout((hfc_RESET_DELAY * HZ) / 1000);
}

static void hfc_resetCard(struct hfc_card *card)
{
	card->regs.m1 = 0;
	hfc_outb(card, hfc_INT_M1, card->regs.m1);

	card->regs.m2 = 0;
	hfc_outb(card, hfc_INT_M2, card->regs.m2);

	hfc_softreset(card);

	card->regs.trm = 0;
	hfc_outb(card, hfc_TRM, card->regs.trm);

	/*
	 * Select the non-capacitive line mode for the S/T interface
	 */
	card->regs.sctrl = hfc_SCTRL_NONE_CAP;

	if (card->nt_mode) {
		/*
		 * ST-Bit delay for NT-Mode
		 */
		hfc_outb(card, hfc_CLKDEL, hfc_CLKDEL_NT);

		card->regs.sctrl |= hfc_SCTRL_MODE_NT;
	} else {
		/*
		 * ST-Bit delay for TE-Mode
		 */
		hfc_outb(card, hfc_CLKDEL, hfc_CLKDEL_TE);

		card->regs.sctrl |= hfc_SCTRL_MODE_TE;
	}

	hfc_outb(card, hfc_SCTRL, card->regs.sctrl);

	/*
	 * S/T Auto awake
	 */
	card->regs.sctrl_e = hfc_SCTRL_E_AUTO_AWAKE;
	hfc_outb(card, hfc_SCTRL_E, card->regs.sctrl_e);

	/*
	 * No B-channel enabled at startup
	 */
	card->regs.sctrl_r = 0;
	hfc_outb(card, hfc_SCTRL_R, card->regs.sctrl_r);

	/*
	 * HFC Master Mode
	 */
	hfc_outb(card, hfc_MST_MODE, hfc_MST_MODE_MASTER);

	/*
	 * Connect internal blocks
	 */
	card->regs.connect =
		hfc_CONNECT_B1_HFC_from_ST |
		hfc_CONNECT_B1_ST_from_HFC |
		hfc_CONNECT_B1_GCI_from_HFC |
		hfc_CONNECT_B2_HFC_from_ST |
		hfc_CONNECT_B2_ST_from_HFC |
		hfc_CONNECT_B2_GCI_from_HFC;
	hfc_outb(card, hfc_CONNECT, card->regs.connect);

	/*
	 * All bchans are HDLC by default, not useful, actually
	 * since mode is set during open()
	 */
	hfc_outb(card, hfc_CTMT, 0);

	/*
	 * bit order
	 */
	hfc_outb(card, hfc_CIRM, 0);

	/*
	 * Enable D-rx FIFO. At least one FIFO must be enabled (by specs)
	 */
	card->regs.fifo_en = hfc_FIFOEN_DRX;
	hfc_outb(card, hfc_FIFO_EN, card->regs.fifo_en);

	card->late_irqs = 0;

	/*
	 * Clear already pending ints
	 */
	hfc_inb(card, hfc_INT_S1);
	hfc_inb(card, hfc_INT_S2);

	/*
	 * Enable IRQ output
	 */
	card->regs.m1 = hfc_INTS_DREC | hfc_INTS_L1STATE | hfc_INTS_TIMER;
	hfc_outb(card, hfc_INT_M1, card->regs.m1);

	card->regs.m2 = hfc_M2_IRQ_ENABLE;
	hfc_outb(card, hfc_INT_M2, card->regs.m2);

	/*
	 * Unlocks the states machine
	 */
	hfc_outb(card, hfc_STATES, 0);

	/*
	 * There's no need to explicitly activate L1 now.
	 * Activation is managed inside the interrupt routine.
	 */
}

static void hfc_update_fifo_state(struct hfc_card *card)
{
	/*
	 * I'm not sure if irqsave is needed but there could be a race
	 * condition since hfc_update_fifo_state could be called from
	 * both the IRQ handler and the *_(open|close) functions
	 */

	unsigned long flags;
	spin_lock_irqsave(&card->chans[B1].lock, flags);
	if (!card->fifo_suspended &&
		(card->chans[B1].status == open_framed ||
		card->chans[B1].status == open_voice)) {

		if (!(card->regs.fifo_en & hfc_FIFOEN_B1RX)) {
			card->regs.fifo_en |= hfc_FIFOEN_B1RX;
			hfc_clear_fifo_rx(&card->chans[B1].rx);
		}

		if (!(card->regs.fifo_en & hfc_FIFOEN_B1TX)) {
			card->regs.fifo_en |= hfc_FIFOEN_B1TX;
			hfc_clear_fifo_tx(&card->chans[B1].tx);
		}
	} else {
		if (card->regs.fifo_en & hfc_FIFOEN_B1RX)
			card->regs.fifo_en &= ~hfc_FIFOEN_B1RX;
		if (card->regs.fifo_en & hfc_FIFOEN_B1TX)
			card->regs.fifo_en &= ~hfc_FIFOEN_B1TX;
	}
	spin_unlock_irqrestore(&card->chans[B1].lock, flags);

	spin_lock_irqsave(&card->chans[B2].lock, flags);
	if (!card->fifo_suspended &&
		(card->chans[B2].status == open_framed ||
		card->chans[B2].status == open_voice ||
		card->chans[B2].status == sniff_aux)) {

		if (!(card->regs.fifo_en & hfc_FIFOEN_B2RX)) {
			card->regs.fifo_en |= hfc_FIFOEN_B2RX;
			hfc_clear_fifo_rx(&card->chans[B2].rx);
		}

		if (!(card->regs.fifo_en & hfc_FIFOEN_B2TX)) {
			card->regs.fifo_en |= hfc_FIFOEN_B2TX;
			hfc_clear_fifo_tx(&card->chans[B2].tx);
		}
	} else {
		if (card->regs.fifo_en & hfc_FIFOEN_B2RX)
			card->regs.fifo_en &= ~hfc_FIFOEN_B2RX;
		if (card->regs.fifo_en & hfc_FIFOEN_B2TX)
			card->regs.fifo_en &= ~hfc_FIFOEN_B2TX;
	}
	spin_unlock_irqrestore(&card->chans[B2].lock, flags);

	spin_lock_irqsave(&card->chans[D].lock, flags);
	if (!card->fifo_suspended &&
		card->chans[D].status == open_framed) {

		if (!(card->regs.fifo_en & hfc_FIFOEN_DTX)) {
			card->regs.fifo_en |= hfc_FIFOEN_DTX;

			card->chans[D].tx.ugly_framebuf_size = 0;
			card->chans[D].tx.ugly_framebuf_off = 0;
		}
	} else {
		if (card->regs.fifo_en & hfc_FIFOEN_DTX)
			card->regs.fifo_en &= ~hfc_FIFOEN_DTX;
	}
	spin_unlock_irqrestore(&card->chans[D].lock, flags);

	hfc_outb(card, hfc_FIFO_EN, card->regs.fifo_en);
}

static inline void hfc_suspend_fifo(struct hfc_card *card)
{
	card->fifo_suspended = TRUE;

	hfc_update_fifo_state(card);

	/*
	 * When L1 goes down D rx receives garbage; it is nice to
	 * clear it to avoid a CRC error on reactivation
	 * udelay is needed because the FIFO deactivation happens
	 * in 250us
	 */
	udelay(250);
	hfc_clear_fifo_rx(&card->chans[D].rx);

#ifdef DEBUG
	if (debug_level >= 3) {
		printk(KERN_DEBUG hfc_DRIVER_PREFIX
			"card %d: "
			"FIFOs suspended\n",
			card->cardnum);
	}
#endif
}

static inline void hfc_resume_fifo(struct hfc_card *card)
{
	card->fifo_suspended = FALSE;

	hfc_update_fifo_state(card);

#ifdef DEBUG
	if (debug_level >= 3) {
		printk(KERN_DEBUG hfc_DRIVER_PREFIX
			"card %d: "
			"FIFOs resumed\n",
			card->cardnum);
	}
#endif
}

static void hfc_check_l1_up(struct hfc_card *card)
{
	if ((!card->nt_mode && card->l1_state != 7)
		|| (card->nt_mode && card->l1_state != 3)) {

		hfc_outb(card, hfc_STATES, hfc_STATES_DO_ACTION |
			hfc_STATES_ACTIVATE|
				hfc_STATES_NT_G2_G3);

	/*
	 * 0 because this is quite verbose when an inferface is unconnected, jaw
	 */
#if 0
		if (debug_level >= 1) {
			printk(KERN_DEBUG hfc_DRIVER_PREFIX
				"card %d: "
				"L1 is down, bringing up L1.\n",
				card->cardnum);
		}
#endif
	}
}


/*******************
 * Dahdi interface *
 *******************/

static int hfc_zap_open(struct dahdi_chan *zaptel_chan)
{
	struct hfc_chan_duplex *chan = zaptel_chan->pvt;
	struct hfc_card *card = chan->card;

	spin_lock(&chan->lock);

	switch (chan->number) {
	case D:
		if (chan->status != free &&
			chan->status != open_framed) {
			spin_unlock(&chan->lock);
			return -EBUSY;
		}
		chan->status = open_framed;
	break;

	case B1:
	case B2:
		if (chan->status != free) {
			spin_unlock(&chan->lock);
			return -EBUSY;
		}
		chan->status = open_voice;
	break;
	}

	chan->open_by_zaptel = TRUE;
	try_module_get(THIS_MODULE);
	spin_unlock(&chan->lock);

	switch (chan->number) {
	case D:
	break;

	case B1:
		card->regs.m2 |= hfc_M2_PROC_TRANS;
		/*
		 * Enable transparent mode
		 */
		card->regs.ctmt |= hfc_CTMT_TRANSB1;
		/*
		* Reversed bit order
		*/
		card->regs.cirm |= hfc_CIRM_B1_REV;
		/*
		 * Enable transmission
		 */
		card->regs.sctrl |= hfc_SCTRL_B1_ENA;
		/*
		 * Enable reception
		 */
		card->regs.sctrl_r |= hfc_SCTRL_R_B1_ENA;
	break;

	case B2:
		card->regs.m2 |= hfc_M2_PROC_TRANS;
		card->regs.ctmt |= hfc_CTMT_TRANSB2;
		card->regs.cirm |= hfc_CIRM_B2_REV;
		card->regs.sctrl |= hfc_SCTRL_B2_ENA;
		card->regs.sctrl_r |= hfc_SCTRL_R_B2_ENA;
	break;

	}

	/*
	 * If not already enabled, enable processing transition (8KHz)
	 * interrupt
	 */
	hfc_outb(card, hfc_INT_M2, card->regs.m2);
	hfc_outb(card, hfc_CTMT, card->regs.ctmt);
	hfc_outb(card, hfc_CIRM, card->regs.cirm);
	hfc_outb(card, hfc_SCTRL, card->regs.sctrl);
	hfc_outb(card, hfc_SCTRL_R, card->regs.sctrl_r);

	hfc_update_fifo_state(card);

	printk(KERN_INFO hfc_DRIVER_PREFIX
		"card %d: "
		"chan %s opened as %s.\n",
		card->cardnum,
		chan->name,
		zaptel_chan->name);

	return 0;
}

static int hfc_zap_close(struct dahdi_chan *zaptel_chan)
{
	struct hfc_chan_duplex *chan = zaptel_chan->pvt;
	struct hfc_card *card = chan->card;

	if (!card) {
		printk(KERN_CRIT hfc_DRIVER_PREFIX
			"hfc_zap_close called with NULL card\n");
		return -1;
	}

	spin_lock(&chan->lock);

	if (chan->status == free) {
		spin_unlock(&chan->lock);
		return -EINVAL;
	}

	chan->status = free;
	chan->open_by_zaptel = FALSE;

	spin_unlock(&chan->lock);

	switch (chan->number) {
	case D:
	break;

	case B1:
		card->regs.ctmt &= ~hfc_CTMT_TRANSB1;
		card->regs.cirm &= ~hfc_CIRM_B1_REV;
		card->regs.sctrl &= ~hfc_SCTRL_B1_ENA;
		card->regs.sctrl_r &= ~hfc_SCTRL_R_B1_ENA;
	break;

	case B2:
		card->regs.ctmt &= ~hfc_CTMT_TRANSB2;
		card->regs.cirm &= ~hfc_CIRM_B2_REV;
		card->regs.sctrl &= ~hfc_SCTRL_B2_ENA;
		card->regs.sctrl_r &= ~hfc_SCTRL_R_B2_ENA;
	break;
	}

	if (card->chans[B1].status == free &&
		card->chans[B2].status == free)
		card->regs.m2 &= ~hfc_M2_PROC_TRANS;

	hfc_outb(card, hfc_INT_M2, card->regs.m2);
	hfc_outb(card, hfc_CTMT, card->regs.ctmt);
	hfc_outb(card, hfc_CIRM, card->regs.cirm);
	hfc_outb(card, hfc_SCTRL, card->regs.sctrl);
	hfc_outb(card, hfc_SCTRL_R, card->regs.sctrl_r);

	hfc_update_fifo_state(card);

	module_put(THIS_MODULE);

	printk(KERN_INFO hfc_DRIVER_PREFIX
		"card %d: "
		"chan %s closed as %s.\n",
		card->cardnum,
		chan->name,
		zaptel_chan->name);

	return 0;
}

static int hfc_zap_rbsbits(struct dahdi_chan *chan, int bits)
{
	return 0;
}

static int hfc_zap_ioctl(struct dahdi_chan *chan,
		unsigned int cmd, unsigned long data)
{
	switch (cmd) {

	default:
		return -ENOTTY;
	}

	return 0;
}

static void hfc_hdlc_hard_xmit(struct dahdi_chan *d_chan)
{
	struct hfc_chan_duplex *chan = d_chan->pvt;
	struct hfc_card *card = chan->card;
	struct dahdi_hfc *hfccard = card->ztdev;

	atomic_inc(&hfccard->hdlc_pending);

}

static int hfc_zap_startup(struct file *file, struct dahdi_span *span)
{
    struct dahdi_hfc *zthfc = container_of(span, struct dahdi_hfc, span);
    struct hfc_card *hfctmp = zthfc->card;
    int alreadyrunning;

	if (!hfctmp) {
		printk(KERN_INFO hfc_DRIVER_PREFIX
			"card %d: "
			"no card for span at startup!\n",
			hfctmp->cardnum);
	}

	alreadyrunning = span->flags & DAHDI_FLAG_RUNNING;

	if (!alreadyrunning)
		span->flags |= DAHDI_FLAG_RUNNING;

	return 0;
}

static int hfc_zap_shutdown(struct dahdi_span *span)
{
	return 0;
}

static int hfc_zap_maint(struct dahdi_span *span, int cmd)
{
	return 0;
}

static int hfc_zap_chanconfig(struct file *file, struct dahdi_chan *d_chan, int sigtype)
{
	struct hfc_chan_duplex *chan = d_chan->pvt;
	struct hfc_card *card = chan->card;
	struct dahdi_hfc *hfccard = card->ztdev;

	if ((sigtype == DAHDI_SIG_HARDHDLC) && (hfccard->sigchan == d_chan)) {
		hfccard->sigactive = 0;
		atomic_set(&hfccard->hdlc_pending, 0);
	}

	return 0;
}

static int hfc_zap_spanconfig(struct file *file, struct dahdi_span *span,
		struct dahdi_lineconfig *lc)
{
	span->lineconfig = lc->lineconfig;

	return 0;
}

static const struct dahdi_span_ops hfccard_span_ops = {
	.owner = THIS_MODULE,
	.spanconfig = hfc_zap_spanconfig,
	.chanconfig = hfc_zap_chanconfig,
	.startup = hfc_zap_startup,
	.shutdown = hfc_zap_shutdown,
	.maint = hfc_zap_maint,
	.rbsbits = hfc_zap_rbsbits,
	.open = hfc_zap_open,
	.close  = hfc_zap_close,
	.ioctl = hfc_zap_ioctl,
	.hdlc_hard_xmit = hfc_hdlc_hard_xmit,
};

static int hfc_zap_initialize(struct dahdi_hfc *hfccard)
{
	 struct hfc_card *hfctmp = hfccard->card;
	int i;
	
	hfccard->card->ddev = dahdi_create_device();
	if (!hfccard->card->ddev)
		return -ENOMEM;

	memset(&hfccard->span, 0x0, sizeof(struct dahdi_span));
	sprintf(hfccard->span.name, "ZTHFC%d", hfctmp->cardnum + 1);
	sprintf(hfccard->span.desc,
			"HFC-S PCI A ISDN card %d [%s] ",
			hfctmp->cardnum,
			hfctmp->nt_mode ? "NT" : "TE");
 //      hfccard->span.owner = THIS_MODULE;
 	if(hfctmp->nt_mode)
	hfccard->span.spantype = SPANTYPE_DIGITAL_BRI_NT;
	else
	hfccard->span.spantype = SPANTYPE_DIGITAL_BRI_TE;
//	hfccard->span.manufacturer = "Cologne Chips";
	hfccard->card->ddev->manufacturer = "Cologne Chips";
	hfccard->span.ops = &hfccard_span_ops;
	list_add_tail(&hfccard->span.device_node, &hfccard->card->ddev->spans);
//	hfccard->span.spanconfig = hfc_zap_spanconfig;
//	hfccard->span.chanconfig = hfc_zap_chanconfig;
//	hfccard->span.startup = hfc_zap_startup;
//	hfccard->span.shutdown = hfc_zap_shutdown;
//	hfccard->span.maint = hfc_zap_maint;
//	hfccard->span.rbsbits = hfc_zap_rbsbits;
//	hfccard->span.open = hfc_zap_open;
//	hfccard->span.close = hfc_zap_close;
//	hfccard->span.ioctl = hfc_zap_ioctl;
//	hfccard->span.hdlc_hard_xmit = hfc_hdlc_hard_xmit;
	hfccard->span.flags = 0;
/*	dahdi_copy_string(hfccard->card->ddev->devicetype, "HFC-S PCI-A ISDN",
			sizeof(hfccard->card->ddev->devicetype)); */
	hfccard->card->ddev->devicetype = "HFC-S PCI-A ISDN";
			
/*	sprintf(hfccard->ddev->location, "PCI Bus %02d Slot %02d",
			hfctmp->pcidev->bus->number,
			PCI_SLOT(hfctmp->pcidev->devfn) + 1); */
			
	hfccard->card->ddev->location = kasprintf(GFP_KERNEL,
				      "PCI Bus %02d Slot %02d",
				      hfctmp->pcidev->bus->number,
				      PCI_SLOT(hfctmp->pcidev->devfn) + 1);
	hfccard->span.chans = hfccard->_chans;
	hfccard->span.channels = 3;
	for (i = 0; i < hfccard->span.channels; i++)
		hfccard->_chans[i] = &hfccard->chans[i];
	hfccard->span.deflaw = DAHDI_LAW_ALAW;
	hfccard->span.linecompat = DAHDI_CONFIG_AMI | DAHDI_CONFIG_CCS;
	hfccard->span.offset = 0;
//	init_waitqueue_head(&hfccard->span.maintq);
//	hfccard->span.pvt = hfccard;

	for (i = 0; i < hfccard->span.channels; i++) {
		memset(&hfccard->chans[i], 0x0, sizeof(struct dahdi_chan));

		sprintf(hfccard->chans[i].name,
				"ZTHFC%d/%d/%d",
				hfctmp->cardnum + 1, 0, i + 1);

		printk(KERN_INFO hfc_DRIVER_PREFIX
			"card %d: "
			"registered %s\n",
			hfctmp->cardnum,
			hfccard->chans[i].name);

		if (i == hfccard->span.channels - 1) {
			hfccard->chans[i].sigcap = DAHDI_SIG_HARDHDLC;
			hfccard->sigchan = &hfccard->chans[DAHDI_D];
			hfccard->sigactive = 0;
			atomic_set(&hfccard->hdlc_pending, 0);
		} else {
			hfccard->chans[i].sigcap =
				DAHDI_SIG_CLEAR | DAHDI_SIG_DACS;
		}

		hfccard->chans[i].chanpos = i + 1;
	}

	hfccard->chans[DAHDI_D].readchunk  =
		hfctmp->chans[D].rx.zaptel_buffer;

	hfccard->chans[DAHDI_D].writechunk =
		hfctmp->chans[D].tx.zaptel_buffer;

	hfccard->chans[DAHDI_D].pvt = &hfctmp->chans[D];

	hfccard->chans[DAHDI_B1].readchunk  =
		hfctmp->chans[B1].rx.zaptel_buffer;

	hfccard->chans[DAHDI_B1].writechunk =
		hfctmp->chans[B1].tx.zaptel_buffer;

	hfccard->chans[DAHDI_B1].pvt = &hfctmp->chans[B1];

	hfccard->chans[DAHDI_B2].readchunk  =
		hfctmp->chans[B2].rx.zaptel_buffer;

	hfccard->chans[DAHDI_B2].writechunk =
		hfctmp->chans[B2].tx.zaptel_buffer;

	hfccard->chans[DAHDI_B2].pvt = &hfctmp->chans[B2];

	if (dahdi_register_device(hfccard->card->ddev, &hfccard->card->pcidev->dev)) {
		printk(KERN_CRIT "unable to register zaptel device!\n");
		kfree(hfccard->card->ddev->location);
		dahdi_free_device(hfccard->card->ddev);
		hfccard->card->ddev = NULL;
		return -1;
	}

	return 0;
}

static void hfc_zap_transmit(struct hfc_chan_simplex *chan)
{
	hfc_fifo_put(chan, chan->zaptel_buffer, DAHDI_CHUNKSIZE);
}

static void hfc_zap_receive(struct hfc_chan_simplex *chan)
{
	hfc_fifo_get(chan, chan->zaptel_buffer, DAHDI_CHUNKSIZE);
}

/******************************************
 * Interrupt Handler
 ******************************************/

static void hfc_handle_timer_interrupt(struct hfc_card *card);
static void hfc_handle_state_interrupt(struct hfc_card *card);
static void hfc_handle_processing_interrupt(struct hfc_card *card);
static void hfc_frame_arrived(struct hfc_chan_duplex *chan);
static void hfc_handle_voice(struct hfc_card *card);

#if (KERNEL_VERSION(2, 6, 24) < LINUX_VERSION_CODE)
static irqreturn_t hfc_interrupt(int irq, void *dev_id)
#else
static irqreturn_t hfc_interrupt(int irq, void *dev_id, struct pt_regs *regs)
#endif
{
	struct hfc_card *card = dev_id;
	unsigned long flags;
	u8 status, s1, s2;

	if (!card) {
		printk(KERN_CRIT hfc_DRIVER_PREFIX
			"spurious interrupt (IRQ %d)\n",
			irq);
		return IRQ_NONE;
	}

	spin_lock_irqsave(&card->lock, flags);
	status = hfc_inb(card, hfc_STATUS);
	if (!(status & hfc_STATUS_ANYINT)) {
		/*
		 * maybe we are sharing the irq
		 */
		spin_unlock_irqrestore(&card->lock, flags);
		return IRQ_NONE;
	}

	/* We used to ingore the IRQ when the card was in processing
	 * state but apparently there is no restriction to access the
	 * card in such state:
	 *
	 * Joerg Ciesielski wrote:
	 * > There is no restriction for the IRQ handler to access
	 * > HFC-S PCI during processing phase. A IRQ latency of 375 us
	 * > is also no problem since there are no interrupt sources in
	 * > HFC-S PCI which must be handled very fast.
	 * > Due to its deep fifos the IRQ latency can be several ms with
	 * > out the risk of loosing data. Even the S/T state interrupts
	 * > must not be handled with a latency less than <5ms.
	 * >
	 * > The processing phase only indicates that HFC-S PCI is
	 * > processing the Fifos as PCI master so that data is read and
	 * > written in the 32k memory window. But there is no restriction
	 * > to access data in the memory window during this time.
	 *
	 * // if (status & hfc_STATUS_PCI_PROC) {
	 * // return IRQ_HANDLED;
	 * // }
	 */

	s1 = hfc_inb(card, hfc_INT_S1);
	s2 = hfc_inb(card, hfc_INT_S2);

	if (s1 != 0) {
		if (s1 & hfc_INTS_TIMER) {
			/*
			 * timer (bit 7)
			 */
			hfc_handle_timer_interrupt(card);
		}

		if (s1 & hfc_INTS_L1STATE) {
			/*
			 * state machine (bit 6)
			 */
			hfc_handle_state_interrupt(card);
		}

		if (s1 & hfc_INTS_DREC) {
			/*
			 * D chan RX (bit 5)
			 */
			hfc_frame_arrived(&card->chans[D]);
		}

		if (s1 & hfc_INTS_B1REC) {
			/*
			 * B1 chan RX (bit 3)
			 */
			hfc_frame_arrived(&card->chans[B1]);
		}

		if (s1 & hfc_INTS_B2REC) {
			/*
			 * B2 chan RX (bit 4)
			 */
			hfc_frame_arrived(&card->chans[B2]);
		}

		if (s1 & hfc_INTS_DTRANS) {
			/*
			 * D chan TX (bit 2)
			 */
		}

		if (s1 & hfc_INTS_B1TRANS) {
			/*
			 * B1 chan TX (bit 0)
			 */
		}

		if (s1 & hfc_INTS_B2TRANS) {
			/*
			 * B2 chan TX (bit 1)
			 */
		}

	}

	if (s2 != 0) {
		if (s2 & hfc_M2_PMESEL) {
			/*
			 * kaboom irq (bit 7)
			 *
			 * CologneChip says:
			 *
			 * the meaning of this fatal error bit is that HFC-S
			 * PCI as PCI master could not access the PCI bus
			 * within 125us to finish its data processing. If this
			 * happens only very seldom it does not cause big
			 * problems but of course some B-channel or D-channel
			 * data will be corrupted due to this event.
			 *
			 * Unfortunately this bit is only set once after the
			 * problem occurs and can only be reseted by a
			 * software reset. That means it is not easily
			 * possible to check how often this fatal error
			 * happens.
			 *
			 */

			if (!card->sync_loss_reported) {
				printk(KERN_CRIT hfc_DRIVER_PREFIX
					"card %d: "
					"sync lost, pci performance too low!\n",
					card->cardnum);

				card->sync_loss_reported = TRUE;
			}
		}

		if (s2 & hfc_M2_GCI_MON_REC) {
			/*
			 * RxR monitor channel (bit 2)
			 */
		}

		if (s2 & hfc_M2_GCI_I_CHG) {
			/*
			 * GCI I-change  (bit 1)
			 */
		}

		if (s2 & hfc_M2_PROC_TRANS) {
			/*
			 * processing/non-processing transition  (bit 0)
			 */
			hfc_handle_processing_interrupt(card);
		}

	}

	spin_unlock_irqrestore(&card->lock, flags);

	return IRQ_HANDLED;
}

static void hfc_handle_timer_interrupt(struct hfc_card *card)
{
	if (card->ignore_first_timer_interrupt) {
		card->ignore_first_timer_interrupt = FALSE;
		return;
	}

	if ((card->nt_mode && card->l1_state == 3) ||
		(!card->nt_mode && card->l1_state == 7)) {

		card->regs.ctmt &= ~hfc_CTMT_TIMER_MASK;
		hfc_outb(card, hfc_CTMT, card->regs.ctmt);

		hfc_resume_fifo(card);
	}
}

static void hfc_handle_state_interrupt(struct hfc_card *card)
{
	u8 new_state = hfc_inb(card, hfc_STATES)  & hfc_STATES_STATE_MASK;

#ifdef DEBUG
	if (debug_level >= 1) {
		printk(KERN_DEBUG hfc_DRIVER_PREFIX
			"card %d: "
			"layer 1 state = %c%d\n",
			card->cardnum,
			card->nt_mode ? 'G' : 'F',
			new_state);
	}
#endif

	if (card->nt_mode) {
		/*
		 * NT mode
		 */

		if (new_state == 3) {
			/*
			 * fix to G3 state (see specs)
			 */
			hfc_outb(card, hfc_STATES, hfc_STATES_LOAD_STATE | 3);
		}

		if (new_state == 3 && card->l1_state != 3)
			hfc_resume_fifo(card);

		if (new_state != 3 && card->l1_state == 3)
			hfc_suspend_fifo(card);

	} else {
		if (new_state == 3) {
			/*
			 * Keep L1 up... zaptel & libpri expects
			 * a always up L1...
			 * Enable only  when using an unpatched libpri
			 */

			if (force_l1_up) {
				hfc_outb(card, hfc_STATES,
					hfc_STATES_DO_ACTION |
					hfc_STATES_ACTIVATE|
					hfc_STATES_NT_G2_G3);
			}
		}

		if (new_state == 7 && card->l1_state != 7) {
			/*
			 * TE is now active, schedule FIFO activation after
			 * some time, otherwise the first frames are lost
			 */

			card->regs.ctmt |= hfc_CTMT_TIMER_50 |
				hfc_CTMT_TIMER_CLEAR;
			hfc_outb(card, hfc_CTMT, card->regs.ctmt);

			/*
			 * Activating the timer firest an
			 * interrupt immediately, we
			 * obviously need to ignore it
			 */
			card->ignore_first_timer_interrupt = TRUE;
		}

		if (new_state != 7 && card->l1_state == 7) {
			/*
			 * TE has become inactive, disable FIFO
			 */
			hfc_suspend_fifo(card);
		}
	}

	card->l1_state = new_state;
}

static void hfc_handle_processing_interrupt(struct hfc_card *card)
{
	int available_bytes = 0;

	/*
	 * Synchronize with the first enabled channel
	 */
	if (card->regs.fifo_en & hfc_FIFOEN_B1RX)
		available_bytes = hfc_fifo_used_rx(&card->chans[B1].rx);
	if (card->regs.fifo_en & hfc_FIFOEN_B2RX)
		available_bytes = hfc_fifo_used_rx(&card->chans[B2].rx);
	else
		available_bytes = -1;

	if ((available_bytes == -1 && card->ticks == 8) ||
		available_bytes >= DAHDI_CHUNKSIZE + hfc_RX_FIFO_PRELOAD) {
		card->ticks = 0;

		if (available_bytes > DAHDI_CHUNKSIZE*2 + hfc_RX_FIFO_PRELOAD) {
			card->late_irqs++;
			/*
			 * we are out of sync, clear fifos, jaw
			 */
			hfc_clear_fifo_rx(&card->chans[B1].rx);
			hfc_clear_fifo_tx(&card->chans[B1].tx);
			hfc_clear_fifo_rx(&card->chans[B2].rx);
			hfc_clear_fifo_tx(&card->chans[B2].tx);

#ifdef DEBUG
			if (debug_level >= 4) {
				printk(KERN_DEBUG hfc_DRIVER_PREFIX
					"card %d: "
					"late IRQ, %d bytes late\n",
					card->cardnum,
					available_bytes -
						(DAHDI_CHUNKSIZE +
						 hfc_RX_FIFO_PRELOAD));
			}
#endif
		} else {
			hfc_handle_voice(card);
		}
	}

	card->ticks++;
}


static void hfc_handle_voice(struct hfc_card *card)
{
	struct dahdi_hfc *hfccard = card->ztdev;
	int frame_left, res;
	unsigned char buf[hfc_HDLC_BUF_LEN];
	unsigned int size = sizeof(buf) / sizeof(buf[0]);


	if (card->chans[B1].status != open_voice &&
		card->chans[B2].status != open_voice)
		return;

	dahdi_transmit(&hfccard->span);

	if (card->regs.fifo_en & hfc_FIFOEN_B1TX)
		hfc_zap_transmit(&card->chans[B1].tx);
	if (card->regs.fifo_en & hfc_FIFOEN_B2TX)
		hfc_zap_transmit(&card->chans[B2].tx);

	/*
	 * dahdi hdlc frame tx
	 */

	if (atomic_read(&hfccard->hdlc_pending)) {
		hfc_check_l1_up(card);
		res = dahdi_hdlc_getbuf(hfccard->sigchan, buf, &size);
			if (size > 0) {
				hfccard->sigactive = 1;
				memcpy(card->chans[D].tx.ugly_framebuf +
				card->chans[D].tx.ugly_framebuf_size,
				buf, size);
				card->chans[D].tx.ugly_framebuf_size += size;
			if (res != 0) {
					hfc_fifo_put_frame(&card->chans[D].tx,
					card->chans[D].tx.ugly_framebuf,
					card->chans[D].tx.ugly_framebuf_size);
					++hfccard->frames_out;
					hfccard->sigactive = 0;
					card->chans[D].tx.ugly_framebuf_size
						= 0;
					atomic_dec(&hfccard->hdlc_pending);
				}
			}
	}
	/*
	 * dahdi hdlc frame tx done
	 */

	if (card->regs.fifo_en & hfc_FIFOEN_B1RX)
		hfc_zap_receive(&card->chans[B1].rx);
	else
		memset(&card->chans[B1].rx.zaptel_buffer, 0x7f,
			sizeof(card->chans[B1].rx.zaptel_buffer));

	if (card->regs.fifo_en & hfc_FIFOEN_B2RX)
		hfc_zap_receive(&card->chans[B2].rx);
	else
		memset(&card->chans[B2].rx.zaptel_buffer, 0x7f,
			sizeof(card->chans[B1].rx.zaptel_buffer));

	/*
	 * Echo cancellation
	 */
	dahdi_ec_chunk(&hfccard->chans[DAHDI_B1],
			card->chans[B1].rx.zaptel_buffer,
			card->chans[B1].tx.zaptel_buffer);
	dahdi_ec_chunk(&hfccard->chans[DAHDI_B2],
			card->chans[B2].rx.zaptel_buffer,
			card->chans[B2].tx.zaptel_buffer);

	/*
	 * dahdi hdlc frame rx
	 */
	if (hfc_fifo_has_frames(&card->chans[D].rx))
		hfc_frame_arrived(&card->chans[D]);

	if (card->chans[D].rx.ugly_framebuf_size) {
		frame_left = card->chans[D].rx.ugly_framebuf_size -
			card->chans[D].rx.ugly_framebuf_off ;
		if (frame_left > hfc_HDLC_BUF_LEN) {
			dahdi_hdlc_putbuf(hfccard->sigchan,
					card->chans[D].rx.ugly_framebuf +
					card->chans[D].rx.ugly_framebuf_off,
					hfc_HDLC_BUF_LEN);
			card->chans[D].rx.ugly_framebuf_off +=
				hfc_HDLC_BUF_LEN;
		} else {
			dahdi_hdlc_putbuf(hfccard->sigchan,
					card->chans[D].rx.ugly_framebuf +
					card->chans[D].rx.ugly_framebuf_off,
					frame_left);
			dahdi_hdlc_finish(hfccard->sigchan);
			card->chans[D].rx.ugly_framebuf_size = 0;
			card->chans[D].rx.ugly_framebuf_off = 0;
		}
	}
	/*
	 * dahdi hdlc frame rx done
	 */

	if (hfccard->span.flags & DAHDI_FLAG_RUNNING)
		dahdi_receive(&hfccard->span);

}

static void hfc_frame_arrived(struct hfc_chan_duplex *chan)
{
	struct hfc_card *card = chan->card;
	int antiloop = 16;
	struct sk_buff *skb;

	while (hfc_fifo_has_frames(&chan->rx) && --antiloop) {
		int frame_size = hfc_fifo_get_frame_size(&chan->rx);

		if (frame_size < 3) {
#ifdef DEBUG
			if (debug_level >= 2)
				printk(KERN_DEBUG hfc_DRIVER_PREFIX
					"card %d: "
					"chan %s: "
					"invalid frame received, "
					"just %d bytes\n",
					card->cardnum,
					chan->name,
					frame_size);
#endif

			hfc_fifo_drop_frame(&chan->rx);


			continue;
		} else if (frame_size == 3) {
#ifdef DEBUG
			if (debug_level >= 2)
				printk(KERN_DEBUG hfc_DRIVER_PREFIX
					"card %d: "
					"chan %s: "
					"empty frame received\n",
					card->cardnum,
					chan->name);
#endif

			hfc_fifo_drop_frame(&chan->rx);


			continue;
		}

		if (chan->open_by_zaptel &&
			card->chans[D].rx.ugly_framebuf_size) {

				/*
				 * We have to wait for Dahdi to transmit the
				 * frame... wait for next time
				 */

				 break;
		}

		skb = dev_alloc_skb(frame_size - 3);

		if (!skb) {
			printk(KERN_ERR hfc_DRIVER_PREFIX
				"card %d: "
				"chan %s: "
				"cannot allocate skb: frame dropped\n",
				card->cardnum,
				chan->name);

			hfc_fifo_drop_frame(&chan->rx);


			continue;
		}


		/*
		* HFC does the checksum
		*/
#ifndef CHECKSUM_HW
		skb->ip_summed = CHECKSUM_COMPLETE;
#else
		skb->ip_summed = CHECKSUM_HW;
#endif

		if (chan->open_by_zaptel) {
			card->chans[D].rx.ugly_framebuf_size = frame_size - 1;

			if (hfc_fifo_get_frame(&card->chans[D].rx,
				card->chans[D].rx.ugly_framebuf,
				frame_size - 1) == -1) {
				dev_kfree_skb(skb);
				continue;
			}

			memcpy(skb_put(skb, frame_size - 3),
				card->chans[D].rx.ugly_framebuf,
				frame_size - 3);
		} else {
			if (hfc_fifo_get_frame(&chan->rx,
				skb_put(skb, frame_size - 3),
				frame_size - 3) == -1) {
				dev_kfree_skb(skb);
				continue;
			}
		}
	}

	if (!antiloop)
		printk(KERN_CRIT hfc_DRIVER_PREFIX
			"card %d: "
			"Infinite loop detected\n",
			card->cardnum);
}

/******************************************
 * Module initialization and cleanup
 ******************************************/

static int __devinit hfc_probe(struct pci_dev *pci_dev,
	const struct pci_device_id *ent)
{
	static int cardnum;
	int err;
	int i;

	struct hfc_card *card = NULL;
	struct dahdi_hfc *zthfc = NULL;
	card = kmalloc(sizeof(struct hfc_card), GFP_KERNEL);
	if (!card) {
		printk(KERN_CRIT hfc_DRIVER_PREFIX
			"unable to kmalloc!\n");
		err = -ENOMEM;
		goto err_alloc_hfccard;
	}

	memset(card, 0x00, sizeof(struct hfc_card));
	card->cardnum = cardnum;
	card->pcidev = pci_dev;
	spin_lock_init(&card->lock);

	pci_set_drvdata(pci_dev, card);

	err = pci_enable_device(pci_dev);
	if (err)
		goto err_pci_enable_device;

	err = pci_set_dma_mask(pci_dev, PCI_DMA_32BIT);
	if (err) {
		printk(KERN_ERR hfc_DRIVER_PREFIX
			"card %d: "
			"No suitable DMA configuration available.\n",
			card->cardnum);
		goto err_pci_set_dma_mask;
	}

	pci_write_config_word(pci_dev, PCI_COMMAND, PCI_COMMAND_MEMORY);
	err = pci_request_regions(pci_dev, hfc_DRIVER_NAME);
	if (err) {
		printk(KERN_CRIT hfc_DRIVER_PREFIX
			"card %d: "
			"cannot request I/O memory region\n",
			card->cardnum);
		goto err_pci_request_regions;
	}

	pci_set_master(pci_dev);

	if (!pci_dev->irq) {
		printk(KERN_CRIT hfc_DRIVER_PREFIX
			"card %d: "
			"no irq!\n",
			card->cardnum);
		err = -ENODEV;
		goto err_noirq;
	}

	card->io_bus_mem = pci_resource_start(pci_dev, 1);
	if (!card->io_bus_mem) {
		printk(KERN_CRIT hfc_DRIVER_PREFIX
			"card %d: "
			"no iomem!\n",
			card->cardnum);
		err = -ENODEV;
		goto err_noiobase;
	}

	card->io_mem = ioremap(card->io_bus_mem, hfc_PCI_MEM_SIZE);
	if (!(card->io_mem)) {
		printk(KERN_CRIT hfc_DRIVER_PREFIX
			"card %d: "
			"cannot ioremap I/O memory\n",
			card->cardnum);
		err = -ENODEV;
		goto err_ioremap;
	}

	/*
	 * pci_alloc_consistent guarantees alignment
	 * (Documentation/DMA-mapping.txt)
	 */
	card->fifo_mem = pci_alloc_consistent(pci_dev,
			hfc_FIFO_SIZE, &card->fifo_bus_mem);
	if (!card->fifo_mem) {
		printk(KERN_CRIT hfc_DRIVER_PREFIX
			"card %d: "
			"unable to allocate FIFO DMA memory!\n",
			card->cardnum);
		err = -ENOMEM;
		goto err_alloc_fifo;
	}

	memset(card->fifo_mem, 0x00, hfc_FIFO_SIZE);

	card->fifos = card->fifo_mem;

	pci_write_config_dword(card->pcidev, hfc_PCI_MWBA, card->fifo_bus_mem);

	err = request_irq(card->pcidev->irq, &hfc_interrupt,

#if (KERNEL_VERSION(2, 6, 23) < LINUX_VERSION_CODE)
		IRQF_SHARED, hfc_DRIVER_NAME, card);
#else
		SA_SHIRQ, hfc_DRIVER_NAME, card);
#endif

	if (err) {
		printk(KERN_CRIT hfc_DRIVER_PREFIX
			"card %d: "
			"unable to register irq\n",
			card->cardnum);
		goto err_request_irq;
	}

	card->nt_mode = FALSE;

	if (modes & (1 << card->cardnum))
		card->nt_mode = TRUE;

	for (i = 0; i < nt_modes_count; i++) {
		if (nt_modes[i] == card->cardnum)
			card->nt_mode = TRUE;
	}

	/*
	 * D Channel
	 */
	card->chans[D].card = card;
	card->chans[D].name = "D";
	card->chans[D].status = free;
	card->chans[D].number = D;
	spin_lock_init(&card->chans[D].lock);

	card->chans[D].rx.chan      = &card->chans[D];
	card->chans[D].rx.fifo_base = card->fifos + 0x4000;
	card->chans[D].rx.z_base    = card->fifos + 0x4000;
	card->chans[D].rx.z1_base   = card->fifos + 0x6080;
	card->chans[D].rx.z2_base   = card->fifos + 0x6082;
	card->chans[D].rx.z_min     = 0x0000;
	card->chans[D].rx.z_max     = 0x01FF;
	card->chans[D].rx.f_min     = 0x10;
	card->chans[D].rx.f_max     = 0x1F;
	card->chans[D].rx.f1        = card->fifos + 0x60a0;
	card->chans[D].rx.f2        = card->fifos + 0x60a1;
	card->chans[D].rx.fifo_size = card->chans[D].rx.z_max
		- card->chans[D].rx.z_min + 1;
	card->chans[D].rx.f_num     = card->chans[D].rx.f_max
		- card->chans[D].rx.f_min + 1;

	card->chans[D].tx.chan      = &card->chans[D];
	card->chans[D].tx.fifo_base = card->fifos + 0x0000;
	card->chans[D].tx.z_base    = card->fifos + 0x0000;
	card->chans[D].tx.z1_base   = card->fifos + 0x2080;
	card->chans[D].tx.z2_base   = card->fifos + 0x2082;
	card->chans[D].tx.z_min     = 0x0000;
	card->chans[D].tx.z_max     = 0x01FF;
	card->chans[D].tx.f_min     = 0x10;
	card->chans[D].tx.f_max     = 0x1F;
	card->chans[D].tx.f1        = card->fifos + 0x20a0;
	card->chans[D].tx.f2        = card->fifos + 0x20a1;
	card->chans[D].tx.fifo_size = card->chans[D].tx.z_max -
		card->chans[D].tx.z_min + 1;
	card->chans[D].tx.f_num     = card->chans[D].tx.f_max -
		card->chans[D].tx.f_min + 1;

	/*
	 * B1 Channel
	 */
	card->chans[B1].card = card;
	card->chans[B1].name = "B1";
	card->chans[B1].status = free;
	card->chans[B1].number = B1;
	card->chans[B1].protocol = 0;
	spin_lock_init(&card->chans[B1].lock);

	card->chans[B1].rx.chan      = &card->chans[B1];
	card->chans[B1].rx.fifo_base = card->fifos + 0x4200;
	card->chans[B1].rx.z_base    = card->fifos + 0x4000;
	card->chans[B1].rx.z1_base   = card->fifos + 0x6000;
	card->chans[B1].rx.z2_base   = card->fifos + 0x6002;
	card->chans[B1].rx.z_min     = 0x0200;
	card->chans[B1].rx.z_max     = 0x1FFF;
	card->chans[B1].rx.f_min     = 0x00;
	card->chans[B1].rx.f_max     = 0x1F;
	card->chans[B1].rx.f1        = card->fifos + 0x6080;
	card->chans[B1].rx.f2        = card->fifos + 0x6081;
	card->chans[B1].rx.fifo_size = card->chans[B1].rx.z_max -
		card->chans[B1].rx.z_min + 1;
	card->chans[B1].rx.f_num     = card->chans[B1].rx.f_max -
		card->chans[B1].rx.f_min + 1;

	card->chans[B1].tx.chan      = &card->chans[B1];
	card->chans[B1].tx.fifo_base = card->fifos + 0x0200;
	card->chans[B1].tx.z_base    = card->fifos + 0x0000;
	card->chans[B1].tx.z1_base   = card->fifos + 0x2000;
	card->chans[B1].tx.z2_base   = card->fifos + 0x2002;
	card->chans[B1].tx.z_min     = 0x0200;
	card->chans[B1].tx.z_max     = 0x1FFF;
	card->chans[B1].tx.f_min     = 0x00;
	card->chans[B1].tx.f_max     = 0x1F;
	card->chans[B1].tx.f1        = card->fifos + 0x2080;
	card->chans[B1].tx.f2        = card->fifos + 0x2081;
	card->chans[B1].tx.fifo_size = card->chans[B1].tx.z_max -
		card->chans[B1].tx.z_min + 1;
	card->chans[B1].tx.f_num     = card->chans[B1].tx.f_max -
		card->chans[B1].tx.f_min + 1;

	/*
	 * B2 Channel
	 */
	card->chans[B2].card = card;
	card->chans[B2].name = "B2";
	card->chans[B2].status = free;
	card->chans[B2].number = B2;
	card->chans[B2].protocol = 0;
	spin_lock_init(&card->chans[B2].lock);

	card->chans[B2].rx.chan      = &card->chans[B2];
	card->chans[B2].rx.fifo_base = card->fifos + 0x6200,
	card->chans[B2].rx.z_base    = card->fifos + 0x6000;
	card->chans[B2].rx.z1_base   = card->fifos + 0x6100;
	card->chans[B2].rx.z2_base   = card->fifos + 0x6102;
	card->chans[B2].rx.z_min     = 0x0200;
	card->chans[B2].rx.z_max     = 0x1FFF;
	card->chans[B2].rx.f_min     = 0x00;
	card->chans[B2].rx.f_max     = 0x1F;
	card->chans[B2].rx.f1        = card->fifos + 0x6180;
	card->chans[B2].rx.f2        = card->fifos + 0x6181;
	card->chans[B2].rx.fifo_size = card->chans[B2].rx.z_max -
		card->chans[B2].rx.z_min + 1;
	card->chans[B2].rx.f_num     = card->chans[B2].rx.f_max -
		card->chans[B2].rx.f_min + 1;

	card->chans[B2].tx.chan      = &card->chans[B2];
	card->chans[B2].tx.fifo_base = card->fifos + 0x2200;
	card->chans[B2].tx.z_base    = card->fifos + 0x2000;
	card->chans[B2].tx.z1_base   = card->fifos + 0x2100;
	card->chans[B2].tx.z2_base   = card->fifos + 0x2102;
	card->chans[B2].tx.z_min     = 0x0200;
	card->chans[B2].tx.z_max     = 0x1FFF;
	card->chans[B2].tx.f_min     = 0x00;
	card->chans[B2].tx.f_max     = 0x1F;
	card->chans[B2].tx.f1        = card->fifos + 0x2180;
	card->chans[B2].tx.f2        = card->fifos + 0x2181;
	card->chans[B2].tx.fifo_size = card->chans[B2].tx.z_max -
		card->chans[B2].tx.z_min + 1;
	card->chans[B2].tx.f_num     = card->chans[B2].tx.f_max -
		card->chans[B2].tx.f_min + 1;

	/*
	 * All done
	 */

	zthfc = kmalloc(sizeof(struct dahdi_hfc), GFP_KERNEL);
	if (!zthfc) {
		printk(KERN_CRIT hfc_DRIVER_PREFIX
			"unable to kmalloc!\n");
		goto err_request_irq;
	}
	memset(zthfc, 0x0, sizeof(struct dahdi_hfc));

	zthfc->card = card;
	hfc_zap_initialize(zthfc);
	card->ztdev = zthfc;

	snprintf(card->proc_dir_name,
			sizeof(card->proc_dir_name),
			"%d", card->cardnum);
	card->proc_dir = proc_mkdir(card->proc_dir_name, hfc_proc_zaphfc_dir);
	SET_PROC_DIRENTRY_OWNER(card->proc_dir);

	hfc_resetCard(card);

	printk(KERN_INFO hfc_DRIVER_PREFIX
		"card %d configured for %s mode at mem %#lx (0x%p) IRQ %u\n",
		card->cardnum,
		card->nt_mode ? "NT" : "TE",
		card->io_bus_mem,
		card->io_mem,
		card->pcidev->irq);

	cardnum++;

	return 0;

err_request_irq:
	pci_free_consistent(pci_dev, hfc_FIFO_SIZE,
		card->fifo_mem, card->fifo_bus_mem);
err_alloc_fifo:
	iounmap(card->io_mem);
err_ioremap:
err_noiobase:
err_noirq:
	pci_release_regions(pci_dev);
err_pci_request_regions:
err_pci_set_dma_mask:
err_pci_enable_device:
	kfree(card);
err_alloc_hfccard:
	return err;
}

static void __devexit hfc_remove(struct pci_dev *pci_dev)
{
	struct hfc_card *card = pci_get_drvdata(pci_dev);

	printk(KERN_INFO hfc_DRIVER_PREFIX
		"card %d: "
		"shutting down card at %p.\n",
		card->cardnum,
		card->io_mem);

	hfc_softreset(card);

	dahdi_unregister_device(card->ddev);


	/*
	 * disable memio and bustmaster
	 */
	pci_write_config_word(pci_dev, PCI_COMMAND, 0);

	remove_proc_entry("bufs", card->proc_dir);
	remove_proc_entry("fifos", card->proc_dir);
	remove_proc_entry("info", card->proc_dir);
	remove_proc_entry(card->proc_dir_name, hfc_proc_zaphfc_dir);

	free_irq(pci_dev->irq, card);

	pci_free_consistent(pci_dev, hfc_FIFO_SIZE,
		card->fifo_mem, card->fifo_bus_mem);

	iounmap(card->io_mem);

	pci_release_regions(pci_dev);

	pci_disable_device(pci_dev);
	
	kfree(card->ddev->location);
	
	dahdi_free_device(card->ddev);

	kfree(card);
}

/******************************************
 * Module stuff
 ******************************************/

static int __init hfc_init_module(void)
{
	int ret;

	printk(KERN_INFO hfc_DRIVER_PREFIX
		hfc_DRIVER_STRING " loading\n");

#if (KERNEL_VERSION(2, 6, 26) <= LINUX_VERSION_CODE)
	hfc_proc_zaphfc_dir = proc_mkdir(hfc_DRIVER_NAME, NULL);
#else
	hfc_proc_zaphfc_dir = proc_mkdir(hfc_DRIVER_NAME, proc_root_driver);
#endif

	ret = dahdi_pci_module(&hfc_driver);
	return ret;
}

module_init(hfc_init_module);

static void __exit hfc_module_exit(void)
{
	pci_unregister_driver(&hfc_driver);

#if (KERNEL_VERSION(2, 6, 26) <= LINUX_VERSION_CODE)
	remove_proc_entry(hfc_DRIVER_NAME, NULL);
#else
	remove_proc_entry(hfc_DRIVER_NAME, proc_root_driver);
#endif

	printk(KERN_INFO hfc_DRIVER_PREFIX
		hfc_DRIVER_STRING " unloaded\n");
}

module_exit(hfc_module_exit);

#endif

MODULE_DESCRIPTION(hfc_DRIVER_DESCR);
MODULE_AUTHOR("Jens Wilke <jw_vzaphfc@headissue.com>, "
		"Daniele (Vihai) Orlandi <daniele@orlandi.com>, "
		"Jose A. Deniz <odicha@hotmail.com>");
MODULE_ALIAS("vzaphfc");
#ifdef MODULE_LICENSE
MODULE_LICENSE("GPL");
#endif


module_param(modes, int, 0444);

#if LINUX_VERSION_CODE >= KERNEL_VERSION(2, 6, 10)
module_param_array(nt_modes, int, &nt_modes_count, 0444);
#else
module_param_array(nt_modes, int, nt_modes_count, 0444);
#endif

module_param(force_l1_up, int, 0444);
#ifdef DEBUG
module_param(debug_level, int, 0444);
#endif

MODULE_PARM_DESC(modes, "[Deprecated] bit-mask to configure NT mode");
MODULE_PARM_DESC(nt_modes,
		"Comma-separated list of card IDs to configure in NT mode");
MODULE_PARM_DESC(force_l1_up, "Don't allow L1 to go down");
#ifdef DEBUG
MODULE_PARM_DESC(debug_level, "Debug verbosity level");
#endif
