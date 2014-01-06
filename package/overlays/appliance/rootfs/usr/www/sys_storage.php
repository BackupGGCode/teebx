<?php
/*
  $Id$
part of BoneOS build platform (http://www.teebx.com/)
Copyright(C) 2010 - 2013 Giovanni Vallesi (http://www.teebx.com).
All rights reserved.

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU Affero General Public License as
  published by the Free Software Foundation, either version 3 of the
  License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU Affero General Public License for more details.

  You should have received a copy of the GNU Affero General Public License
  along with this program.  If not, see [http://www.gnu.org/licenses/].

- BoneOS source code is available via svn at [http://svn.code.sf.net/p/boneos/code/].
- look at TeeBX website [http://www.teebx.com] to get details about license.
*/

session_start();
$_SESSION['diskedit']['token'] = uniqid(md5(microtime()), true);

require('guiconfig.inc');
require_once('util.inc');
require_once('blockdevices.lib.php');
require_once('libs-php/htmltable.class.php');
// define some constants referenced in fbegin.inc
define('INCLUDE_TBLSTYLE', true);
// page title
$pgtitle = array(_('System'), _('Storage'));
// actual configuration reference
$cfgPtr = &$config['system']['storage'];
// retrieve informations about system block devices
$forceDevRefresh = false;
if (isset($_SESSION['refresh']['blockdevice']))
{
	$forceDevRefresh = true;
	unset($_SESSION['refresh']['blockdevice']);
}
$disksInfo = getBlockDevices($forceDevRefresh);
getDiskUsage($disksInfo);
$_SESSION['diskedit']['info'] = $disksInfo;
//
// init a table object
$tblStorage = new htmlTable('id=table01|class=report');
// fill the table caption
$tblStorage->caption(_('Configured storage'));
// table section heading
$tblStorage->thead();
	$tblStorage->tr();
		$tblStorage->th(_('Device name'), 'class=colheader');
		$tblStorage->th(_('Label'), 'class=colheader');
		$tblStorage->th(_('Mount Point'), 'class=colheader');
		$tblStorage->th(_('Free'), 'class=colheader');
		$tblStorage->th(_('Used'), 'class=colheader');
		$tblStorage->th(_('Total'), 'class=colheader');
		$tblStorage->th(_('Usage %'), 'class=colheader');
		$tblStorage->th(_('Services'), 'class=colheader');
	//
// section body
$tblStorage->tbody();
	// list configured devices
	if (isset($cfgPtr['fsmounts']))
	{
		foreach (array_keys($cfgPtr['fsmounts']) as $mntNode)
		{
			if (!is_array($cfgPtr['fsmounts'][$mntNode]))
				continue;
			//
			$fsInfo = getDevByUuid($disksInfo, $cfgPtr['fsmounts'][$mntNode]['uuid']);
			$devText = '';
			if ($fsInfo !== false)
			{
				$devText = basename("{$fsInfo[0]}{$fsInfo[1]}");
			}
			$tblStorage->tr();
			// device/partition
			$tblStorage->td($devText);
			// filesystem label
			$tblStorage->td($cfgPtr['fsmounts'][$mntNode]['label']);
			// mount point
			$tblStorage->td("{$cfgPtr['mountroot']}/$mntNode");
			// size/usage/...
			$fsFree = '';
			$fsUsed = '';
			$fsTotal = '';
			$usage = '';
			if (isset($disksInfo[$fsInfo[0]]['parts'][$fsInfo[1]]['blocks-total']))
			{
				$fsBlocksTotal = $disksInfo[$fsInfo[0]]['parts'][$fsInfo[1]]['blocks-total'];
				$fsBlocksUsed = $disksInfo[$fsInfo[0]]['parts'][$fsInfo[1]]['blocks-used'];
				// we have 1024 bytes blocks, not bytes.
				$fsFree = formatBytes(($fsBlocksTotal * 1024) - ($fsBlocksUsed * 1024));
				$fsUsed = formatBytes($fsBlocksUsed * 1024);
				$fsTotal = formatBytes($fsBlocksTotal * 1024);
				$usage = getAnalogBar(array('total' => $fsBlocksTotal, 'used' => $fsBlocksUsed));
			}
			$tblStorage->td($fsFree);
			$tblStorage->td($fsUsed);
			$tblStorage->td($fsTotal);
			$tblStorage->td($usage);
			// attached services
			$tblStorage->td('');
		}
	}
	else
	{
		$tblStorage->tr();
		$tblStorage->td(_('No additional storage configured.'), 'colspan=8');
	}
//
// report all system devices
// init another table object
$tblDiskRep = new htmlTable('id=table02|class=report');
// fill the table caption
$tblDiskRep->caption(_('Disk Report'));
// table section heading
$tblDiskRep->thead();
	$tblDiskRep->tr();
		$tblDiskRep->th(_('Free disks'), 'class=bodytitle|colspan=3');
	$tblDiskRep->tr();
		$tblDiskRep->th(_('Device name'), 'class=colheader');
		$tblDiskRep->th(_('Size'), 'class=colheader|colspan=2');
	//
// section body
$tblDiskRep->tbody();
	$freeDisks = getFreeDisks($config, $disksInfo);
	if (!empty($freeDisks))
	{
		foreach (array_keys($freeDisks) as $key)
		{
			$diskEditTool = _('Click here to configure this disk');
			$actionCall = "doClickAction('new', '$key', '1', '1')";
			$diskEditLabel = '<a class="doedit" href="#" OnClick="' . $actionCall .'" title="' . $diskEditTool . '">'
				. '<img  src="img/add.png" alt="+">'
				. $disksInfo[$key]['sizelabel']
				.'</a>';
			$noticeLabel = '';
			if ($freeDisks[$key] > 0)
			{
				// this disk is already partitioned, notify the user
				$noticeLabel = sprintf(" Notice: this disk already has %s partition(s)", $freeDisks[$key]);
				$noticeLabel .= '&nbsp;<img  src="img/alert.png" alt="!">';
			}
			$tblDiskRep->tr();
				$tblDiskRep->td($key);
				$tblDiskRep->td($diskEditLabel . $noticeLabel, 'colspan=2');
			//
		}
	}
	else
	{
		$tblDiskRep->tr();
		$tblDiskRep->td(_('No free disk devices.'), 'colspan=3');
	}
	// show system disk summary
// section heading
	$tblDiskRep->tr();
		$tblDiskRep->th(_('System disk status'), 'class=bodytitle|colspan=3');
	$tblDiskRep->tr();
		$tblDiskRep->th(_('Label'), 'class=colheader');
		$tblDiskRep->th(_('Mount Point'), 'class=colheader');
		$tblDiskRep->th(_('Size'), 'class=colheader');
	//
// section body
$tblDiskRep->tbody();
	foreach (array_keys($disksInfo) as $devKey)
	{
		if (isset($disksInfo[$devKey]['__SYS_DISK__']))
		{
			$devFree = $disksInfo[$devKey]['size']/1000/1024;
			// this array will old partitions end cylinder
			$partsEndSect = array();
			foreach (array_keys($disksInfo[$devKey]['parts']) as $partKey)
			{
				$partSize = '';
				$partSizeUnits = '';
				$partsEndSect[] = $disksInfo[$devKey]['parts'][$partKey]['end'];
				if (isset($disksInfo[$devKey]['parts'][$partKey]['blocks-total']))
				{
					$partSize = round($disksInfo[$devKey]['parts'][$partKey]['blocks-total']/1024, 1);
					$devFree = $devFree - $partSize;
				}
				if (is_numeric($partSize))
				{
					$partSizeUnits = ' MB';
				}
				//
				$mountText = '';
				if (isset($disksInfo[$devKey]['parts'][$partKey]['mountpoint']))
				{
					$mountText = $disksInfo[$devKey]['parts'][$partKey]['mountpoint'];
				}
				//
				$labelText = '';
				if (isset($disksInfo[$devKey]['parts'][$partKey]['label']))
				{
					$labelText = $disksInfo[$devKey]['parts'][$partKey]['label'];
				}
				//
				$tblDiskRep->tr();
					$tblDiskRep->td($labelText);
					$tblDiskRep->td($mountText);
					$tblDiskRep->td($partSize . $partSizeUnits);
				//
			}
			//
			$newPartStart = max($partsEndSect) + 1;
			if ($newPartStart < $disksInfo[$devKey]['sectors'])
			{
				$spareSize = $devFree - BDEV_SYS_SPARESIZE;
				if ($spareSize >= BDEV_MIN_SIZE)
				{
					$tblDiskRep->thead();
					$tblDiskRep->tr();
						$tblDiskRep->th(_('Device name'), 'class=colheader');
						$tblDiskRep->th(_('Spare disk space'), 'class=colheader|colspan=2');
					//
					$diskEditTool = _('Click here to configure this disk');
					$actionCall = "doClickAction('use-spare', '$devKey', '3', '$newPartStart')";
					$diskEditLabel = '<a class="doedit" href="#" OnClick="' . $actionCall .'" title="' . $diskEditTool . '">'
						. '<img  src="img/add.png" alt="+">'
						. round($devFree)
						.' MB</a>';
					// this is the system disk, print a notice to the user
					$noticeLabel = ' ' . _('Notice: this is the system disk. Unless you really know what you are doing please avoid using it for additional storage.');
					$noticeLabel .= '&nbsp;<img  src="img/alert.png" alt="!">';
					$tblDiskRep->tbody();
					$tblDiskRep->tr();
						$tblDiskRep->td($devKey);
						$tblDiskRep->td($diskEditLabel . $noticeLabel, 'colspan=2');
					//
				}
			}
		}
	}
	//
// render main layout
require('fbegin.inc');
// render the page content
$tblStorage->renderTable();
// echo '<br>';
$tblDiskRep->renderTable();
?>
<script type="text/javascript">
	function doClickAction(action, device, partnum, partstart)
	{
		var $jhf = jQuery.noConflict();
		$jhf('#act').val(action);
		$jhf('#dev').val(device);
		$jhf('#par').val(partnum);
		$jhf('#start').val(partstart);
		//
		$jhf('form').submit();
		return false;
	}
</script>
<form action="sys_storage_edit.php" method="post">
	<input type="hidden" id="act" name="act" value="">
	<input type="hidden" id="dev" name="dev" value="">
	<input type="hidden" id="par" name="par" value="">
	<input type="hidden" id="start" name="start" value="">
	<input type="hidden" id="stk" name="stk" value="<?php echo $_SESSION['diskedit']['token']; ?>">
</form>
<?php
// end layout
require('fend.inc');

?>
