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

function microtime_float ()
{
	list ($msec, $sec) = explode(' ', microtime());
	$microtime = (float)$msec + (float)$sec;
	return $microtime;
}
session_start();
$_SESSION['diskedit']['token'] = uniqid(md5(microtime()), true);

$start = microtime_float();

require('guiconfig.inc');
require('blockdevices.lib.php');
require('libs-php/htmltable.class.php');
// define some constants referenced in fbegin.inc
define('INCLUDE_TBLSTYLE', true);
// page title
$pgtitle = array(gettext('System'), gettext('Storage'));
// actual configuration reference
$cfgPtr = &$config['system']['storage']['disk'];
// retrieve informations about system block devices
$disksInfo = getBlockDevices();
getDiskUsage($disksInfo);
$_SESSION['diskedit']['info'] = $disksInfo;
// init a table object
$tbl = new htmlTable('id=table01|class=report');
// fill the table caption
$tbl->caption(gettext('Disk Report'));
// table section heading
$tbl->thead();
	$tbl->tr();
		$tbl->th(gettext('Configured storage'), 'class=bodytitle|colspan=3');
	$tbl->tr();
		$tbl->th(gettext('Name'), 'class=colheader');
		$tbl->th(gettext('Mount Point'), 'class=colheader');
		$tbl->th(gettext('Services'), 'class=colheader');
	//
// section body
$tbl->tbody();
	// list configured devices
	$tbl->tr();
	if (!empty($cfgPtr))
	{

	}
	else
	{
		$tbl->td(gettext('No additional storage configured.'), 'colspan=3');
	}
	// list available devices
// table section heading
$tbl->thead();
	$tbl->tr();
		$tbl->th(gettext('Free disks'), 'class=bodytitle|colspan=3');
	$tbl->tr();
		$tbl->th(gettext('Device name'), 'class=colheader');
		$tbl->th(gettext('Size'), 'class=colheader|colspan=2');
	//
// section body
$tbl->tbody();
	$freeDisks = getFreeDisks($cfgPtr, $disksInfo);
	if (!empty($freeDisks))
	{
		foreach (array_keys($freeDisks) as $key)
		{
			$diskEditTool = gettext('Click here to configure this disk');
			$actionCall = "doClickAction('new', '$key', '1', '1')";
			$diskEditLabel = '<a class="doedit" href="#" OnClick="' . $actionCall .'" title="' . $diskEditTool . '">'
				. '<img  src="img/add.png" alt="+">'
				. round($disksInfo[$key]['size']/1000000)
				.' MB</a>';
			$noticeLabel = '';
			if ($freeDisks[$key] > 0)
			{
				// this disk is already partitioned, notify the user
				$noticeLabel = sprintf(" Notice: this disk already has %s partition(s)", $freeDisks[$key]);
				$noticeLabel .= '&nbsp;<img  src="img/alert.png" alt="!">';
			}
			$tbl->tr();
				$tbl->td($key);
				$tbl->td($diskEditLabel . $noticeLabel, 'colspan=2');
			//
		}
	}
	else
	{
		$tbl->tr();
		$tbl->td(gettext('No free disk devices.'), 'colspan=3');
	}
	// show system disk summary
// section heading
$tbl->thead();
	$tbl->tr();
		$tbl->th(gettext('System disk status'), 'class=bodytitle|colspan=3');
	$tbl->tr();
		$tbl->th(gettext('Label'), 'class=colheader');
		$tbl->th(gettext('Mount Point'), 'class=colheader');
		$tbl->th(gettext('Size'), 'class=colheader');
	//
// section body
$tbl->tbody();
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
				$tbl->tr();
					$tbl->td($labelText);
					$tbl->td($mountText);
					$tbl->td($partSize . $partSizeUnits);
				//
			}
			//
			$newPartStart = max($partsEndSect) + 1;
			if ($newPartStart < $disksInfo[$devKey]['sectors'])
			{
				$spareSize = $devFree - BDEV_SYS_SPARESIZE;
				if ($spareSize >= BDEV_MIN_SIZE)
				{
					$tbl->thead();
					$tbl->tr();
						$tbl->th(gettext('Device name'), 'class=colheader');
						$tbl->th(gettext('Spare disk space'), 'class=colheader|colspan=2');
					//
					$diskEditTool = gettext('Click here to configure this disk');
					$actionCall = "doClickAction('use-spare', '$devKey', '3', '$newPartStart')";
					$diskEditLabel = '<a class="doedit" href="#" OnClick="' . $actionCall .'" title="' . $diskEditTool . '">'
						. '<img  src="img/add.png" alt="+">'
						. round($devFree)
						.' MB</a>';
					// this is the system disk, print a notice to the user
					$noticeLabel = ' ' . gettext('Notice: this is the system disk, avoid using it for additional storage.');
					$noticeLabel .= '&nbsp;<img  src="img/alert.png" alt="!">';
					$tbl->tbody();
					$tbl->tr();
						$tbl->td($devKey);
						$tbl->td($diskEditLabel . $noticeLabel, 'colspan=2');
					//
				}
			}
		}
	}
	//
// render main layout
require('fbegin.inc');
// render the page content
$tbl->renderTable();

//debug
$end = microtime_float();
echo '<div><pre>';
var_export($disksInfo);
echo '</pre></div>';

echo 'Script Execution Time: ' . round($end - $start, 3) . ' seconds';
echo '';
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
