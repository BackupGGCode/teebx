<?php
/*
	$Id$
	part of BoneOS build platform (http://www.teebx.com/)
	Copyright(C) 2011 - 2013 Giovanni Vallesi.
	All rights reserved.

	originally part of AskoziaPBX svn trunk revision 1514 (http://askozia.com/pbx)
	Copyright (C) 2007-2009 tecema (a.k.a IKT) <http://www.tecema.de>. All rights reserved.
	originally part of m0n0wall (http://m0n0.ch/wall)
	Copyright (C) 2003-2006 Manuel Kasper <mk@neon1.net>. All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
	AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
	AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
	SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
	INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
	CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
	POSSIBILITY OF SUCH DAMAGE.
*/

if (is_file("/offload/livecd"))
{
	$local_version = trim(file_get_contents("/etc/version"));
	$livecd_version = trim(file_get_contents("/offload/livecd"));

	if (strlen($livecd_version) > 0)
	{
		if ($local_version != $livecd_version)
		{
			die(sprintf(gettext("TeeBX is in update mode. You either have to remove the Live CD or install the newer version of TeeBX. (Installed is %s, Live CD version is %s)"), $local_version, $livecd_version));
		}
	}
}

require('guiconfig.inc');
require('libs-php/htmltable.class.php');
// define some constants referenced in fbegin.inc
define('INCLUDE_TBLSTYLE', true);

$product_name = system_get_product_name();
$pgtitle = array($product_name . ' ' . gettext('web UI'));

//check_update();

// check if some important applications running
$statusMsg = null;
if (pbx_exec("core show version") == 1)
{
	$statusMsg = display_info_box(gettext("Asterisk hasn't started yet. Please wait for a few minutes. If it won't start you need to reboot TeeBX."), "keep");
}

// init a table object
$tbl = new htmlTable('id=table01|class=home');
// fill the table caption
$tbl->caption(gettext('System Information'));
// a tbody is mandatory
$tbl->tbody();
// show system informations
// name
$tbl->tr();
	$tbl->td(gettext('Name'), 'class=tblrowlabel');
	$tbl->td($config['system']['hostname'] . '.' . $config['system']['domain']);
// version name
$tbl->tr();
	$tbl->td(gettext('Version'), 'class=tblrowlabel');
	$tbl->td('<strong>' . system_get_product_name() . '&nbsp;' .
		file_get_contents('/etc/version') . '</strong>' .
		gettext('on') . '&nbsp;' . $g['platform'] . '<br>'
	);
// version build time
$tbl->tr();
	$tbl->td(gettext('built on'), 'class=tblrowlabel');
	$tbl->td(strftime('%c', chop(file_get_contents('/etc/version.buildtime'))));
// last config change
if (isset($config['lastchange']))
{
	if (!empty($config['lastchange']))
	{
		$tbl->tr();
			$tbl->td(gettext('Last Config Change'), 'class=tblrowlabel');
			$tbl->td(strftime('%c', $config['lastchange']));
		//
	}
}
// system date/time
$tbl->tr();
	$tbl->td(gettext('System clock (at page load)'), 'class=tblrowlabel');
	$tbl->td(strftime('%c'));
// uptime
exec('/usr/bin/uptime', $ut);
$start = strpos($ut[0], 'up') + 2;
$end = strpos($ut[0], ',');
$upTime = substr($ut[0], $start, $end - $start);
$tbl->tr();
	$tbl->td(gettext('Uptime'), 'class=tblrowlabel');
	$tbl->td($upTime);
// memory usage
exec('/usr/bin/free', $memory);
$memory = preg_split("/\s+/", $memory[1]);
// eglibc + busybox 1.20.2 reports:
// Array ( [0] => Mem: [1] => 513652 [2] => 40516 [3] => 473136 [4] => 0 [5] => 520 )
$totalMem = $memory[1];
$freeMem = $memory[3];
$usedMem = $totalMem - $freeMem;
$memUsage = round(($usedMem * 100) / $totalMem, 0);
$usageTitle = $usedMem . ' / ' . $totalMem . ' kBytes';
$tbl->tr();
	$tbl->td(gettext('Memory Usage'), 'class=tblrowlabel');
	$tbl->td(getAnalogBar($memUsage));
// system notes
if (isset($config['system']['notes']))
{
	if (!empty($config['system']['notes']))
	{
		$tbl->tr();
			$tbl->td(gettext('Notes'), 'class=tblrowlabel');
			$tbl->td(nl2br(htmlentities(base64_decode($config['system']['notes']), ENT_QUOTES, 'UTF-8'), false));
		//
	}
}

include('fbegin.inc');
$tbl->renderTable();
include("fend.inc");
?>
