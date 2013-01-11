<?php
/*
  $Id$
  part of TeeBX(R) VoIP communication platform. http://www.teebx.com
*/

	$cid_sigmode = array(
		'bell' => gettext('Bell202, USA'),
		'v23' => gettext('V23, ETSI/Europe'),
		'v23_jp' => gettext('V23, Japan V23 variant'),
		'dtmf' => gettext('DTMF, as used in Denmark, Sweden and Netherlands')
		/*smdi = Use SMDI for caller ID. Requires SMDI to be enabled*/
	);
/*
; What signals the start of caller ID*/
	$cid_sigstart = array(
		'ring' => gettext('Ring'),
		'polarity' => gettext('Polarity reversal'),
		'polarity_IN' => gettext('India polarity reversal dtmf cid variant')
	);
?>