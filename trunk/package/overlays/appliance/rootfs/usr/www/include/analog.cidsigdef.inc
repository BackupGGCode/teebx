<?php
/*
  $Id$
  part of TeeBX(R) VoIP communication platform. http://www.teebx.com
*/

	$cid_sigmode = array(
		'bell' => _('Bell202, USA'),
		'v23' => _('V23, ETSI/Europe'),
		'v23_jp' => _('V23, Japan V23 variant'),
		'dtmf' => _('DTMF, as used in Denmark, Sweden and Netherlands')
		/*smdi = Use SMDI for caller ID. Requires SMDI to be enabled*/
	);
/*
; What signals the start of caller ID*/
	$cid_sigstart = array(
		'ring' => _('Ring'),
		'polarity' => _('Polarity reversal'),
		'polarity_IN' => _('India polarity reversal dtmf cid variant')
	);
?>