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

define('BDEV_ROOT_LABEL', 'system');
define('BDEV_ROOT_MOUNTP', '/cf');
// 32 MB spare on the system disk reserved for updates
define('BDEV_SYS_SPARESIZE', 5);
// minimum size for additional storage
define('BDEV_MIN_SIZE', 32);
// prefix used to name disk uuid xml element because naming rules forbid to start with a number
define('BDEV_CFG_UUIDPREFIX', 'uuid_');
define('CACHE_FILE', '/tmp/blkdevca.che');

if (file_exists('/etc/inc/initsvc.storage.php'))
{
	include('/etc/inc/initsvc.storage.php');
}

function closestDivisibleBy($in, $divider)
{
	// this function should never return a value smaller than $in
	$closest = $in;
	$remainder = ($in % $divider);
	if ($remainder > 0)
	{
		$closest = $closest + ($divider - $remainder);
	}
	return $closest;
}

function getBlockDevices($refresh = false, $revealMTD = false)
{
	// dmesg | grep -i 'attached scsi\|usb disconnect'
	if (!$refresh)
	{
		if (file_exists(CACHE_FILE))
		{
			$result = unserialize(file_get_contents(CACHE_FILE));
			return $result;
		}
	}

	$result = array();
	exec('/bin/busybox fdisk -u -l;echo 1;/bin/busybox blkid;echo 2;mount', $out);
	foreach (array_keys($out) as $idx)
	{
		// try to match lines that reports info about disks
		//                       Device                      Size
		if (preg_match('/^Disk\s(\/dev\/(?:nand|mmcblk\d|[a-z]{3})):\s[\w\s\.]+,\s([\d]+)\s/', $out[$idx], $regs))
		{
			if (!$revealMTD)
			{
				if ($regs[1] === '/dev/nand')
					continue;
				//
			}
			$result[$regs[1]]['size'] = (float) $regs[2];
		}
	}
	//
	$current = 0;
	foreach (array_keys($out) as $idx)
	{
		$current ++;
		// try to match lines that reports info about partitions
		//                 Device         PNum      Boot          Start     End       Blocks  OddSect      Id           System
		if (preg_match('/^(\/dev\/(?:nand|mmcblk\d|[a-z]{3}))((?:p{1}[\d]+|[\d]+|[a-z]{1}))\s+([\*]{0,1})\s+([\d]+)\s+([\d]+)\s+([\d]+)([\+]{0,1})\s+([a-f\d]+)\s+([\w]+)/', $out[$idx], $regs))
		{
			// disk device must be already set
			if (isset($result[$regs[1]]))
			{
				if (!empty($regs[3]))
				{
					$result[$regs[1]]['parts'][$regs[2]]['f_active'] = true;
				}
				$result[$regs[1]]['parts'][$regs[2]]['start'] = $regs[4];
				$result[$regs[1]]['parts'][$regs[2]]['end'] = $regs[5];
				$result[$regs[1]]['parts'][$regs[2]]['blocks'] = $regs[6];
				if (!empty($regs[7]))
				{
					$result[$regs[1]]['parts'][$regs[2]]['f_oddsec'] = true;
				}
				$result[$regs[1]]['parts'][$regs[2]]['id'] = $regs[8];
				$result[$regs[1]]['parts'][$regs[2]]['system'] = $regs[9];
			}
		}
		elseif ($out[$idx] === '1')
		{
			$out = array_slice($out, $current);
			break;
		}
	}
	//
	$current = 0;
	foreach (array_keys($out) as $idx)
	{
		$current ++;
		// get labels and uuid
		//                 Device         Pnum             Label           UUID
		if (preg_match('/^(\/dev\/(?:nand|mmcblk\d|[a-z]{3}))((?:p{1}[\d]+|[\d]+|[a-z]{1})):(?:\sLABEL="([\w\-]+)")*(?:\sUUID="([a-fA-F\d\-]+)")*/', $out[$idx], $regs))
		{
			if (isset($result[$regs[1]]))
			{
				if (isset($regs[3]))
				{
					$result[$regs[1]]['parts'][$regs[2]]['label'] = $regs[3];
				}
				if (isset($regs[4]))
				{
					$result[$regs[1]]['parts'][$regs[2]]['uuid'] = $regs[4];
				}
			}
		}
		elseif ($out[$idx] === '2')
		{
			$out = array_slice($out, $current);
			break;
		}
	}
	//
	foreach (array_keys($out) as $idx)
	{
		// get mount points
		//                 Device         Pnum         Mount point
		if (preg_match('/^(\/dev\/(?:nand|mmcblk\d|[a-z]{3}))((?:p{1}[\d]+|[\d]+|[a-z]{1}))\son\s([\/\w\-\_]+)\stype\s(\w+)/', $out[$idx], $regs))
		{
			if (isset($result[$regs[1]]))
			{
				$result[$regs[1]]['parts'][$regs[2]]['mountpoint'] = $regs[3];
				$result[$regs[1]]['parts'][$regs[2]]['fstype'] = $regs[4];
				if ($regs[3] === BDEV_ROOT_MOUNTP)
				{
					$result[$regs[1]]['__SYS_DISK__'] = true;
				}
			}
		}
	}
	unset($out, $regs);
	//
	file_put_contents(CACHE_FILE, serialize($result));
	return $result;
}

function getDiskUsage(&$arrDiskInfo)
{
	exec('df', $out);
	foreach (array_keys($out) as $idx)
	{
		// get disk usage
		//                Device         PNum      1K-blocks Used
		if (preg_match('/(\/dev\/(?:nand|mmcblk\d|[a-z]{3}))((?:p{1}[\d]+|[\d]+|[a-z]{1}))\s+([\d]+)\s+([\d]+)/', $out[$idx], $regs))
		{
			if (isset($arrDiskInfo[$regs[1]]))
			{
				$arrDiskInfo[$regs[1]]['parts'][$regs[2]]['blocks-total'] = $regs[3];
				$arrDiskInfo[$regs[1]]['parts'][$regs[2]]['blocks-used'] = $regs[4];
			}
		}
	}
}

function getFreeDisks(&$cfgPtr, &$arrDiskInfo)
{
	$result = array();
	foreach (array_keys($arrDiskInfo) as $devKey)
	{
		if (!isset($arrDiskInfo[$devKey]['__SYS_DISK__']))
		{
			if (!isset($arrDiskInfo[$devKey]['parts']))
			{
				// unpartitioned disk
				$result[$devKey] = 0;
			}
			else
			{
				// check if this disk is already mounted
				foreach (array_keys($arrDiskInfo[$devKey]['parts']) as $partKey)
				{
					if (!isset($arrDiskInfo[$devKey]['parts'][$partKey]['mountpoint']))
					{
						// return the actual number of partitions
						$result[$devKey] = count($arrDiskInfo[$devKey]['parts']);
						break;
					}
				}
			}
		}
		else
		{
			// TODO: allow to use the free space on the system disk
		}
	}
	return $result;
}

function getConfiguredDisks(&$cfgPtr)
{
}

function getLastUsedSector($diskDev, &$arrDiskInfo)
{
	// copy array subset because we won't move the array pointer
	$diskParts = $arrDiskInfo[$diskDev]['parts'];
	$partEnd = array();
	foreach($diskParts as $part)
	{
		$partEnd[] = $part['end'];
	}
	return max($partEnd);
}

function newPartition($dev, $partStart, $partEnd, $fs = 'fat32', $newPartTable = false, $align = 2048)
{
  /* partition types table
    ext2         fat16         fat32
    hfs          hfs+          hfsx
    linux-swap   NTFS          reiserfs
    ufs
  */
	openlog('UI disk partitioning', LOG_INFO, LOG_LOCAL0);

	if ($newPartTable === true)
	{
		exec("parted -a optimal --script $dev -- mklabel msdos", $out, $retval);
		syslog(LOG_INFO, "Writing new partition table on $dev returned $retval");
		if ($retval !== 0)
		{
			// error writing the new partition table, exiting
			syslog(LOG_ERR, "parted mklabel error: $out");
			return $retval;
		}
	}

	if (is_int($align))
	{
		$partStart = closestDivisibleBy($partStart, $align);
	}

	$cmdParams = "{$dev} -- mkpart primary $fs {$partStart}s $partEnd";
	exec("parted -a optimal --script $cmdParams", $out, $retval);
	syslog(LOG_INFO, "parted mkpart returned " . $retval);
	if ($retval !== 0)
	{
		syslog(LOG_ERR, "parted mkpart error: $out");
	}
	closelog();
	return $retval;
}

function formatPartitionDos($devPart, $label, $fatSize = 32)
{
	openlog('UI dos partition formatting', LOG_INFO, LOG_LOCAL0);
	exec("mkdosfs -F $fatSize -n $label $devPart", $out, $retval);
	syslog(LOG_INFO, "Creating new filesystem on $devPart returned $retval");
	if ($retval !== 0)
	{
		syslog(LOG_ERR, "mkdosfs error: $out");
	}
	closelog();
	return $retval;
}

function mountStorageDevices()
{
}

?>
