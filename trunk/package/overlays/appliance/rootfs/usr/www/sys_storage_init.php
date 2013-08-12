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
require('guiconfig.inc');
require('blockdevices.lib.php');
define('SYS_PARTCOUNT', 2); // <-- to be moved to larger visibility
// to be returned as json
$data = array();
$data['retval'] = 1;
// messages
$debug = array();
// errors array
$data['errors'] = array();
// sanity checks
$devToPartition = null;
$clearPartTable = null;
$accessAllowed = false;
if (!isset($_POST['mode']))
	$_POST['mode'] = null;
if (!isset($_POST['task']))
	$_POST['task'] = null;
if (!isset($_POST['label']))
	$_POST['label'] = null;
if (isset($_SESSION['diskedit']['token']))
{
	if (isset($_POST))
	{
		if ($_POST['stk'] === $_SESSION['diskedit']['token'])
		{
			$accessAllowed = true;
			if (isset($_POST['dev']))
			{
				if (isset($_POST['part']))
				{
					if (($_POST['part'] >= 1) && ($_POST['part'] <= 4))
					{
						$newPartNum = $_POST['part'];
						if (isset($_SESSION['diskedit']['info']))
						{
							$disksInfo = $_SESSION['diskedit']['info'];
							$devToPartition = $_POST['dev'];
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
							if (is_null($_POST['label']) || ($_POST['label'] == ''))
							{
								$data['errors'][] = gettext('Empty or missing partition label.');
							}
							//
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
						}
						else
						{
							$data['errors'][] = gettext('Invalid or missing disk informations.');
						}
					}
					else
					{
						$data['errors'][] = gettext('Invalid partition number.');
					}
				}
				else
				{
					$data['errors'][] = gettext('Missing partition number.');
				}
			}
			else
			{
				$data['errors'][] = gettext('Missing device name.');
			}
		}
	}
}
if (!$accessAllowed)
{
	$data['errors'][] = gettext('Direct access not allowed!');
}
if (isset($_POST['start']))
{
	$sectStart = $_POST['start'];
}
else
{
	$data['errors'][] = gettext('Missing partition start sector!');
}
if (count($data['errors']) > 0)
{
	die(json_encode($data));
}
//
$opStatus = 0;
$diskDev = $_POST['dev'];
$newPart = "$diskDev$newPartNum";
if (strpos($diskDev, '/dev/mmcblk') !== false)
{
	$newPart = "{$diskDev}p{$newPartNum}";
}
$newPartId = 'fat32';
//
if ($_POST['task'] === 'partinit')
{
	$data['retval'] = newPartition($diskDev, $sectStart, '100%', $newPartId, $clearPartTable);
	if ($_POST['mode'] === 'use-spare')
	{
		/* when initializing a new partition on system disk
		fdisk output a warning and return 1 so we need to check
		that the new partition exists instead of accepting raw result.
		*/
		exec("fdisk -l|grep '$newPart'| awk '{print \$5}'", $out);
		if (isset($out[0]))
		{
			if ($out[0] == $newPartId)
			{
				// return integer 2 instead of 1, evaluating this we will notice the user about actions to be done.
				$data['retval'] = 2;
			}
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
	$data['retval'] = formatPartitionDos($newPart, $newLabel);
}
// exit
exit(json_encode($data));

if ($_POST['mode'] === 'new')
{
	// New partition on dedicated disk
}
elseif ($_POST['mode'] === 'use-spare')
{
	// Partitioning spare space on system device
}
elseif ($_POST['mode'] === 'edit')
{
	// Edit current settings
}
else
{
	$data['retval'] = 1;
	die(json_encode($data));
}

?>
