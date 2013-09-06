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
require_once('guiconfig.inc');
require_once('blockdevices.lib.php');
define('SYS_PARTCOUNT', 2); // <-- to be moved to larger visibility
// initialize some local variables
$devToPartition = null;
$clearPartTable = null;
// to be returned as json
$data = array();
$data['retval'] = 1;
// messages
$debug = array();
// errors array
$data['errors'] = array();
//
if (!isset($_SESSION['diskedit']['token']))
{
	$data['errors'][] = gettext('Missing token, access not allowed!');
	exit(json_encode($data));
}
if (!isset($_POST))
{
	$data['errors'][] = gettext('Missing post data!');
	exit(json_encode($data));
}
// initialize post data that may not exists
if (!isset($_POST['mode']))
	$_POST['mode'] = null;
if (!isset($_POST['task']))
	$_POST['task'] = null;
if (!isset($_POST['label']))
	$_POST['label'] = null;
if (!isset($_POST['stk']))
	$_POST['stk'] = null;
// check for required session data
if ($_POST['stk'] !== $_SESSION['diskedit']['token'])
	$data['errors'][] = gettext('Invalid token, access not allowed!');
if (!isset($_SESSION['diskedit']['info']))
	$data['errors'][] = gettext('Invalid or missing disk informations.');
// check that post data is plausible before continue
if (!isset($_POST['dev']))
	$data['errors'][] = gettext('Missing device name.');
if (!isset($_POST['part']))
	$data['errors'][] = gettext('Missing partition number.');
else
{
	if (($_POST['part'] < 1) || ($_POST['part'] > 4))
		$data['errors'][] = gettext('Invalid partition number.');
	//
}
if (!isset($_POST['start']))
	$data['errors'][] = gettext('Missing partition start sector!');
if (is_null($_POST['label']) || ($_POST['label'] == ''))
{
	$data['errors'][] = gettext('Empty or missing partition label.');
}
// something failed?
if (count($data['errors']) > 0)
{
	exit(json_encode($data));
}

$newPartNum = $_POST['part'];
$disksInfo = $_SESSION['diskedit']['info'];
$diskDev = $_POST['dev'];
$sectStart = $_POST['start'];
$clearPartTable = false;
// be paranoid, ensure not to overwrite system partitions
if (isset($disksInfo[$_POST['dev']]['__SYS_DISK__']))
{
	if ($newPartNum <= SYS_PARTCOUNT)
	{
		$data['errors'][] = gettext('Overwriting partitions not allowed on: ') . $_POST['dev'];
	}
}
else
{
	$clearPartTable = true;
}
// validate options
switch ($_POST['mode'])
{
	case 'new':
	case 'use-spare':
	case 'edit':
		break;
	default:
		$data['errors'][] = gettext('Invalid or missing mode.');
	//
}

switch ($_POST['task'])
{
	case 'partinit':
	case 'partformat':
		break;
	default:
		$data['errors'][] = gettext('Invalid or missing task.');
}

if (count($data['errors']) > 0)
{
	exit(json_encode($data));
}

//
$newPart = "$diskDev$newPartNum";
if (strpos($diskDev, '/dev/mmcblk') !== false)
{
	$newPart = "{$diskDev}p{$newPartNum}";
}
$newPartFs = 'fat32';
//
if ($_POST['task'] === 'partinit')
{
	$data['retval'] = newPartition($diskDev, $sectStart, '100%', $newPartFs, $clearPartTable);
	if ($_POST['mode'] === 'use-spare')
	{
		/* force kernel to reread partition table
		when initializing a new partition on system disk.
		*/
		exec('partprobe');
		exec('cat /proc/partitions |grep ' . basename($newPart), $out);
		if (isset($out[0]))
		{
				// return integer 2 instead of 0, evaluating this we will able to notice the user about actions to be done.
				$data['retval'] = 2;
		}
		else
		{
			$data['errors'][] = gettext('Something went wrong creating new partition.');
		}
	}
}
elseif ($_POST['task'] === 'partformat')
{
	$newLabel = $_POST['label'];
	$data['retval'] = formatPartitionFat($newPart, $newLabel);
}
// we should exit immediately returning the json data to the calling ajax request
exit(json_encode($data));

?>