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
// spare on the system disk reserved for updates
define('BDEV_SYS_SPARESIZE', 5);
// minimum size for additional storage
define('BDEV_MIN_SIZE', 32);
define('CACHE_FILE', '/tmp/blkdevca.che');

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

function getFsIdentifier($fsIdent)
{
	$deviceName = "/dev/$fsIdent";
	if (file_exists($deviceName))
	{
		$deviceType = filetype($deviceName);
		if ($deviceType === 'block')
		{
			return $deviceName;
		}
	}

	// uuid (or fat volume serial number)
	if (preg_match('/^([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}|[A-F0-9]{4}-[A-F0-9]{4})/', $fsIdent))
	{
		return "UUID=$fsIdent";
	}

	// volume label
	// labels created in system should not accept dashes and spaces
	if (preg_match('/^([_a-z0-9]{1,11})$/i', $fsIdent))
	{
		return "LABEL=$fsIdent";
	}

	return false;
}

function getDevModel($device)
{
	$arrDiskInfo = getBlockDevices(false, true);
	if (!is_array($arrDiskInfo))
		return false;
	//
	if (!isset($arrDiskInfo[$device]['info']))
		return false;
	//
	return $arrDiskInfo[$device]['info'];
}

function getDevByUuid(&$arrDiskInfo, $uuid)
{
	if (!is_array($arrDiskInfo))
		return false;
	//
	foreach (array_keys($arrDiskInfo) as $device)
	{
		if (!isset($arrDiskInfo[$device]['parts']))
			continue;
		//
		foreach (array_keys($arrDiskInfo[$device]['parts']) as $part)
		{
			if (!isset($arrDiskInfo[$device]['parts'][$part]['uuid']))
			{
				continue;
			}
			if ($arrDiskInfo[$device]['parts'][$part]['uuid'] != $uuid)
			{
				continue;
			}
			return array($device, $part);
		}
	}
	return false;
}

function isMountpoint($mntNode)
{
	// we could also look at cat  /proc/self/mounts and return which device is mounted on?
	exec("mountpoint $mntNode | grep 'is a mountpoint'", $out);
	if (!empty($out))
		return true;
	//
	return false;
}

function killCacheFile()
{
	if (file_exists(CACHE_FILE))
	{
		unlink(CACHE_FILE);
	}
}

function waitDeviceFs($fsIdent, $waitCount = 5)
{
	for ($i = 0; $i < $waitCount; $i++)
	{
		exec("blkid -c /dev/null -w /dev/null|grep $fsIdent", $out, $retval);
		if (empty($out))
		{
			sleep(1);
			continue;
		}
		return $i;
	}
	return false;
}

function getBlockDevices($refresh = false, $revealMTD = false)
{
	// TODO:
	// check new plugged in device and refresh cache
	//   dmesg | grep -i 'attached scsi\|usb disconnect'
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
			$device = $regs[1];
			$devName = basename($regs[1]);
			$result[$device]['size'] = (float) $regs[2];
			$result[$device]['sizelabel'] = round($result[$device]['size']/1000000). ' MB';
			// get total sectors on next array element
			if (preg_match('/^([\d]+)\sheads,\s([\d]+)\s[a-z\/]+,\s([\d]+)\s[a-z]+,\s[a-z]+\s([\d]+)\ssectors/', $out[$idx+1], $regs))
			{
				$result[$device]['sectors'] = (float) $regs[4];
			}
			// check if the current device is removable
			$result[$device]['removable'] = false;
			if (file_exists("/sys/block/$devName/removable"))
			{
				$flines = file_get_contents("/sys/block/$devName/removable");
				if ($flines !== false)
				{
					$flines = trim($flines);
					if ($flines == 1)
					{
						$result[$device]['removable'] = true;
					}
				}
				unset($flines);
			}
			// get some info about device name/vendor
			$result[$device]['info'] = '';
			if (file_exists("/sys/block/$devName/device/vendor"))
			{
				$flines = file_get_contents("/sys/block/$devName/device/vendor");
				if ($flines !== false)
				{
					$result[$device]['info'] = trim($flines);
				}
				unset($flines);
			}
			if (file_exists("/sys/block/$devName/device/model"))
			{
				$flines = file_get_contents("/sys/block/$devName/device/model");
				if ($flines !== false)
				{
					$flines = trim($flines);
					$result[$device]['info'] .= " $flines";
				}
				unset($flines);
			}
			$result[$device]['info'] = trim($result[$device]['info']);
			if (empty($result[$device]['info']))
			{
				$result[$device]['info'] = _('Unknown brand/model');
			}
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
		//                 Device                            Pnum                                   Boot sect. type            Label                  UUID
		if (preg_match('/^(\/dev\/(?:nand|mmcblk\d|[a-z]{3}))((?:p{1}[\d]+|[\d]+|[a-z]{1})):\s(?:(?:SEC_TYPE="[\w]+"\s)*LABEL="([\w\-]+)")*(?:\sUUID="([a-fA-F\d\-]+)")*/', $out[$idx], $regs))
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
			syslog(LOG_ERR, 'parted mklabel error: ' . implode(' ',$out));
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
		syslog(LOG_ERR, 'parted mkpart error: ' . implode(' ',$out));
	}
	closelog();
	return $retval;
}

function formatPartitionFat($devPart, $label, $fatSize = 32)
{
	openlog('UI dos partition formatting', LOG_INFO, LOG_LOCAL0);
	exec("mkdosfs -F $fatSize -n $label $devPart", $out, $retval);
	syslog(LOG_INFO, "Creating new filesystem on $devPart returned $retval");
	if ($retval !== 0)
	{
		syslog(LOG_ERR, 'mkdosfs error: ' . implode(' ',$out));
	}
	closelog();
	return $retval;
}

function mountPart($devFs, $mountPoint, $mountParams = '', $guessFsIdentifier = true)
{
	openlog('appliance', LOG_PERROR, LOG_LOCAL0);
	syslog(LOG_INFO, "filesystem mount, $devFs to $mountPoint");
	if ($guessFsIdentifier)
	{
		$devFs = getFsIdentifier($devFs);
		if ($devFs === false)
		{
			syslog(LOG_ERR, "Func. mountPart(): unable to guess a filesystem for $devFs");
			return false;
		}
	}

	exec("mount $mountParams $devFs $mountPoint", $out, $retval);
	if ($retval != 0)
	{
		syslog(LOG_ERR, 'mount error: ' . implode(' ',$out));
		return false;
	}
	closelog();
	$retval = $devFs;
	return $retval;
}

function mountStorageDevices(&$conf)
{
	$log = array('cfgchanged' => 0, 'newdevs' => 0, 'fsappend' => array());
	if (!is_array($conf['system']['storage']['fsmounts']))
	{
		return $log;
	}

	$devDisabled = 0;
	$devConfigured = count($conf['system']['storage']['fsmounts']);
	foreach (array_keys($conf['system']['storage']['fsmounts']) as $devMount)
	{
		if (!is_array($conf['system']['storage']['fsmounts'][$devMount]))
		{
			// empty set, ignore it
			$devDisabled += 1;
			continue;
		}
		if ($conf['system']['storage']['fsmounts'][$devMount]['active'] != 1)
		{
			// disabled entry, ignore it
			$devDisabled += 1;
			continue;
		}
		$fullMntPath = "{$conf['system']['storage']['mountroot']}/{$devMount}";
		// make the fs mount point
		if (!is_dir($fullMntPath))
		{
			if (!mkdir($fullMntPath, 0766, true))
			{
				// should never happen... but disable entry because will not mount
				$conf['system']['storage']['fsmounts'][$devMount]['active'] = -1;
				$devDisabled += 1;
				$log['cfgchanged'] = 1;
				continue;
			}
		}
		// check that the device to be mounted is ready
		$devFsId = $conf['system']['storage']['fsmounts'][$devMount]['uuid'];
		$ready = waitDeviceFs($devFsId);
		if ($ready === false)
		{
			// device no longer exists? Anyway mark it as disabled
			$conf['system']['storage']['fsmounts'][$devMount]['active'] = -2;
			$devDisabled += 1;
			$log['cfgchanged'] = 1;
			continue;
		}
		$fsType = $conf['system']['storage']['fsmounts'][$devMount]['filesystem'];
		$fsMntOpts = "-w -t $fsType -o noatime";
		// mount the filesystem
		$fstabFsId = mountPart($devFsId, $fullMntPath, $fsMntOpts);
		if ($fstabFsId === false)
		{
			$conf['system']['storage']['fsmounts'][$devMount]['active'] = -3;
			$devDisabled += 1;
			$log['cfgchanged'] = 1;
			continue;
		}
		// update fstab buffer
		$log['fsappend'][] = "$fstabFsId $fullMntPath $fsType rw,noatime 0 0";
	}
	// write down a new fstab if needed
	$log['newdevs'] = count($log['fsappend']);
	msgToSyslog("storage check complete ($devConfigured devices configured, {$log['newdevs']} activated, $devDisabled disabled).");
	if ($log['newdevs'] > 0)
	{
		setupFstab($log['fsappend'], 'from');
		msgToSyslog('fstab update complete.');
	}
	unset($log['fsappend']);
	return $log;
}

function setupFstab($rows, $copyMode = false, $fstabCopy = '/etc/fstab.boot')
{
	$lines = '';
	$bytesCount = false;
	if ($copyMode == 'from')
	{
		if (file_exists($fstabCopy))
		{
			$lines = file_get_contents($fstabCopy);
		}
	}

	$fHandle = fopen('/etc/fstab', 'w');
	if (is_array($rows))
	{
		foreach (array_keys($rows) as $row)
		{
			$lines .= $rows[$row] . PHP_EOL;
		}
	}
	else
	{
		$lines .= $rows . PHP_EOL;
	}

	$bytesCount = fwrite($fHandle, $lines);
	fflush($fHandle);
	fclose($fHandle);
	//exec('sync');

	if ($copyMode == 'to')
		copy('/etc/fstab', $fstabCopy);
	//
	return $bytesCount;
}

?>
