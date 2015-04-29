# Introduction #
The TeeBX pbx platform was started on Intel's x86 architectures but has always privileged power balanced low-power processors, fanless boards and solid state storage.
Because of this reason, expand support to the most interesting boards ARM seemed like a natural evolution.

## Reasons that aims the ARM port ##
ARM based boards hold the promise of low power and excellent performance per Watt ratios; power efficiency will mean also less heat dissipation so the components could be kept reasonably cool using only a passive heatsink. No cooling fans anymore, less dust pollution, less noise.

Unfortunately until the Raspberry Pi model B was launched at the end of February 2012 ARM boards was not so cheap. Was the Raspberry Pi over-hyped?
I think the answer is Yes but looks like the Raspberry Pi foundation has opened the Pandora's Box. The Pi has proven that there really is a big market out there for inexpensive ARM based boards because not only the intended target wants one but also developers, hobbysts, integrators.

You know the rest of the story, now there are plenty of cheap and powerful ARM based development boards out there below the 100$ price tag.

## ARM port status & Planning ##
A lot of work already been done to support some very popular boards, much work still remains to be done because interesting new products come in all the time.

### Candidates board requirements ###
Not all boards are suitable for our project and so I decided to outline some requirements, draw up a list of the most interesting cards and track the progress about the card support.

**Compulsory requirements:**

  1. On board ethernet, preferably not over usb.
  1. Serial port for console and debug.
  1. Linux kernel support, mature drivers for most on board peripherals.
  1. Well-established support community or obvious/evident effort from the manufacturer to grow the community around the device.

**Advisable features:**

  * Schematic diagrams available, fully open source hardware preferred.
  * Available worldwide.
  * Suitable for derivative product development.
  * Price less than $ 100.

### Wishlist & Support status ###
Notes:

- _Board names alphabetically sorted_

- _Want Your board supported?_ Make a suggestion or **donate a sample**.

| **Board** | **SoC/CPU** | **Core type** | **Core(s)** | **CPU clock** | **RAM** | **Flash** | **Status** | **Donor/Sponsor**|
|:----------|:------------|:--------------|:------------|:--------------|:--------|:----------|:-----------|:-----------------|
| **Beaglebone Black** |TI AM3359AZCZ100|Cortex-A8|1 |1 GHz|512 MB|2 GB|WIP|Own founds|
|![http://teebx.googlecode.com/svn/wiki/beageboneblack_w360_00.jpg](http://teebx.googlecode.com/svn/wiki/beageboneblack_w360_00.jpg)| | | | | | | | | |
| **Cubieboard** |Allwinner A10|Cortex-A8|1 |1 GHz|1 GB|4 GB|Supported|Own founds|
|![http://teebx.googlecode.com/svn/wiki/cubieboard_w360_00.jpg](http://teebx.googlecode.com/svn/wiki/cubieboard_w360_00.jpg)| | | | | | | | | |
| **Cubieboard2** |Allwinner A20|Cortex-A7|2 |1 GHz|1 GB|4 GB|WIP| [Thanks to Cubietech](http://cubieboard.org/) |
|![http://teebx.googlecode.com/svn/wiki/cubieboard2_w360_00.jpg](http://teebx.googlecode.com/svn/wiki/cubieboard2_w360_00.jpg)| | | | | | | | | |
| **Radxa** |Rockchip RK3188|Cortex-A9|4 |1.6 GHz|1/2 GB|4/8 GB|Planned|Welcome...|
|![http://teebx.googlecode.com/svn/wiki/radxa_w360_00.jpg](http://teebx.googlecode.com/svn/wiki/radxa_w360_00.jpg)| | | | | | | | | |
| **Raspberry Pi** (B) |Broadcom BCM2835|ARM1176JZF-S|1 |700 MHz|256/512 MB|N/A|Supported|Friend|
|![http://teebx.googlecode.com/svn/wiki/raspberrypi_w360_00.jpg](http://teebx.googlecode.com/svn/wiki/raspberrypi_w360_00.jpg)| | | | | | | | | |
| **Wandboard dual** |Freescale i.MX6 Dual Lite|Cortex-A9|2 |1 GHz|1GB|N/A|Supported|Own founds|
|![http://teebx.googlecode.com/svn/wiki/wandboard_dual_w360_00.jpg](http://teebx.googlecode.com/svn/wiki/wandboard_dual_w360_00.jpg)| | | | | | | | | |