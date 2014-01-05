<?php
/*
	$Id$
	part of BoneOS build platform (http://www.teebx.com/)
	Copyright(C) 2011 - 2014 Giovanni Vallesi.
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
			die(sprintf(_("TeeBX is in update mode. You either have to remove the Live CD or install the newer version of TeeBX. (Installed is %s, Live CD version is %s)"), $local_version, $livecd_version));
		}
	}
}

require('guiconfig.inc');
require_once('libs-php/htmltable.class.php');
require_once('blockdevices.lib.php');

// define some constants referenced in fbegin.inc
define('INCLUDE_TBLSTYLE', true);
define('INCLUDE_JSCRIPTS', 'dashboard.js');

$product_name = system_get_product_name();
$pgtitle = array($product_name . ' ' . _('web UI'));

//check_update();

// check if some important applications running
$statusMsg = null;
if (pbx_exec("core show version") == 1)
{
	// TODO: display this info somewere...
	$statusMsg = _("Asterisk hasn't started yet. Please wait for a few minutes. If it won't start you need to reboot TeeBX.");
}

// init a table object
$tbl = new htmlTable('id=table01|class=home');
// fill the table caption
$tbl->caption(_('System Information'));
// a tbody is mandatory
$tbl->tbody();
// show system informations
// name
$tbl->tr();
	$tbl->td(_('Name'), 'class=tblrowlabel');
	$tbl->td($config['system']['hostname'] . '.' . $config['system']['domain']);
// version name
$tbl->tr();
	$tbl->td(_('Version'), 'class=tblrowlabel');
	$tbl->td('<strong>' . system_get_product_name() . '&nbsp;' .
		file_get_contents('/etc/version') . '</strong>' .
		_('on') . '&nbsp;' . $g['platform'] . '<br>'
	);
// version build time
$tbl->tr();
	$tbl->td(_('built on'), 'class=tblrowlabel');
	$tbl->td(strftime('%c', chop(file_get_contents('/etc/version.buildtime'))));
// last config change
if (isset($config['lastchange']))
{
	if (!empty($config['lastchange']))
	{
		$tbl->tr();
			$tbl->td(_('Last Config Change'), 'class=tblrowlabel');
			$tbl->td(strftime('%c', $config['lastchange']));
		//
	}
}
// system date/time
$tbl->tr();
	$tbl->td(_('System Time'), 'class=tblrowlabel');
	$tbl->td('<span id="systime"></span> (page loaded '. strftime('%c') . ')');
// uptime
exec('/usr/bin/uptime', $ut);
$start = strpos($ut[0], 'up') + 2;
$end = strpos($ut[0], ',');
$upTime = substr($ut[0], $start, $end - $start);
$tbl->tr();
	$tbl->td(_('Uptime'), 'class=tblrowlabel');
	$tbl->td($upTime);
// memory usage
$memStatus = getMemoryStatus();
if ($memStatus !== false)
{
	$memReport = _('Free') . ': '. formatBytes($memStatus['free']) . ' ';
	$memReport .= _('Used') . ': '. formatBytes($memStatus['used']) . ' ';
	$memReport .= _('Total') . ': '. formatBytes($memStatus['total']);
	$memReport .= getAnalogBar($memStatus);
}
else
{
	$memReport = _('Unable to get data.');
}

$tbl->tr();
	$tbl->td(_('Memory Usage'), 'class=tblrowlabel');
	$tbl->td($memReport);
	//
unset($memStatus, $memReport);
// storage status
if (isset($config['system']['storage']['fsmounts']))
{
	if (is_array($config['system']['storage']['fsmounts']))
	{
		$disksInfo = getBlockDevices(true);
		getDiskUsage($disksInfo);
		$storageReport = '';

		foreach (array_keys($config['system']['storage']['fsmounts']) as $mntNode)
		{
			if (!is_array($config['system']['storage']['fsmounts'][$mntNode]))
				continue;
			//
			if ($config['system']['storage']['fsmounts'][$mntNode]['active'] != 1)
			{
				// disabled entry, only report active devices status
				continue;
			}
			// TODO: also report alerts about disks autodisabled at boot time because of errors
			//
			$fsUuid = $config['system']['storage']['fsmounts'][$mntNode]['uuid'];
			$fsInfo = getDevByUuid($disksInfo, $fsUuid);
			$hwName = '';
			if (isset($disksInfo[$fsInfo[0]]['info']) && (!empty($disksInfo[$fsInfo[0]]['info'])))
			{
				$hwName = "{$disksInfo[$fsInfo[0]]['info']} / ";
			}
			if ($fsInfo !== false)
			{
				if (isset($disksInfo[$fsInfo[0]]['parts'][$fsInfo[1]]['blocks-total']))
				{
					$fsBlocksTotal = $disksInfo[$fsInfo[0]]['parts'][$fsInfo[1]]['blocks-total'];
					$fsBlocksUsed = $disksInfo[$fsInfo[0]]['parts'][$fsInfo[1]]['blocks-used'];
					//
					$storageReport .= basename("{$fsInfo[0]}{$fsInfo[1]}");
					$storageReport .= ' (' . $disksInfo[$fsInfo[0]]['info'] . ')<br>';
					// units of 1024 bytes blocks
					$storageReport .= _('Free') . ': ' . formatBytes(($fsBlocksTotal * 1024) - ($fsBlocksUsed * 1024)) . ' ';
					$storageReport .= _('Used') . ': ' . formatBytes($fsBlocksUsed * 1024) . ' ';
					$storageReport .= _('Total') . ': ' . formatBytes($fsBlocksTotal * 1024);
					$storageReport .= getAnalogBar(array('total' => $fsBlocksTotal, 'used' => $fsBlocksUsed));
				}
				else
				{
					$storageReport .= _('Unable to get data.') . " ({$hwName}{$fsUuid})<br>";
				}
			}
			else
			{
				$storageReport .= _('Missing device.') . " ({$hwName}{$fsUuid})<br>";
			}
		}
		//
		if (!empty($storageReport))
		{
		$tbl->tr();
			$tbl->td(_('Configured storage'), 'class=tblrowlabel');
			$tbl->td($storageReport);
		}
		unset($disksInfo, $storageReport, $fsInfo);
	}
}
// system notes
if (isset($config['system']['notes']))
{
	if (!empty($config['system']['notes']))
	{
		$tbl->tr();
			$tbl->td(_('Notes'), 'class=tblrowlabel');
			$tbl->td(nl2br(htmlentities(base64_decode($config['system']['notes']), ENT_QUOTES, 'UTF-8'), false));
		//
	}
}

include('fbegin.inc');
$tbl->renderTable();
echo '<div id="timestamp" style="display: none;">' . (time() + date('Z')) % 86400 . '</div>';
include("fend.inc");
?>
