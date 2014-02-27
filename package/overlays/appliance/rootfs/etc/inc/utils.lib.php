<?php
/*
  $Id$
part of BoneOS build platform (http://www.teebx.com/)
Copyright(C) 2013 - 2014 Giovanni Vallesi (http://www.teebx.com).
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

function msgToSyslog($msg, $pri = LOG_INFO, $tag = 'appliance')
{
	openlog($tag, LOG_PERROR, LOG_LOCAL0);
	syslog($pri, $msg);
	closelog();
}

function getMilliTime()
{
	return microtime(true) * 1000;
}

function syncFreeCache()
{
	exec('sync; /sbin/sysctl -w vm.drop_caches=3', $discard, $retval);
	unset($discard);
	return $retval;
}

function getMemoryStatus()
{
	// TODO: add a sleep to syncFreeCache() and call this function via ajax to avoid page loading latency
	syncFreeCache();
	exec('/usr/bin/free -b', $memory, $retval);
	if ($retval == 1)
		return false;
	//
	$memory = preg_split("/\s+/", $memory[1]);
	// eglibc + busybox 1.20.2 reports:
	// Array ( [0] => Mem: [1] => 513652 [2] => 40516 [3] => 473136 [4] => 0 [5] => 520 )
	return array('total' => $memory[1], 'used' => $memory[1] - $memory[3], 'free' => $memory[3]);
}

function getProcessCmdline($name)
{
	$result = false;
	exec("ps|grep -v grep|grep $name", $out);
	if (count($out) > 0)
	{
		if (preg_match('/\s[\d]{1,2}:[\d]{2}\s[\w\/]+\s([ \w\-\/\.\:]+)/', $out[0], $regs))
		{
			$result = $regs[1];
		}
	}
	return $result;
}

function formatBytes($bytes, $precision = 1, $siUnits = false)
{
	// default to use IEC units
	$uLabels = array('B', 'KiB', 'MiB', 'GiB', 'TiB');
	$mod = 1024;
	if ($siUnits)
	{
		$uLabels = array('B', 'KB', 'MB', 'GB', 'TB');
		$mod = 1000;
	}
	// set format string
	$fmt = "%.{$precision}f %s";

	$bytes = max($bytes, 0);
	$pow = 0;
	if ($bytes > 0)
	{
		$pow = min(floor(log($bytes) / log($mod)), count($uLabels) - 1);
	}
	$bytes = $bytes/pow($mod, $pow);
	return sprintf($fmt, $bytes, $uLabels[$pow]);
}

function getVersionInfo()
{
	$info = array(
		'timestamp' => '0',
		'buid' => '',
		'rev' => '',
		'sta' => '',
		'codename' => '',
		'prod' => 'BoneOS',
		'spare' => '',
		'brand' => '',
	);
	if (!file_exists('/etc/revision.data'))
	{
		return $info;
	}

	$tmp = file('/etc/revision.data', FILE_IGNORE_NEW_LINES);
	if ($tmp !== false)
	{
		$tmp = explode('|', $tmp[0]);
		$info['timestamp'] = $tmp[0];
		$info['buid'] = $tmp[1];
		$info['rev'] = $tmp[2];
		$info['sta'] = $tmp[3];
		$info['codename'] = $tmp[4];
		$info['prod'] = $tmp[5];
		$info['spare'] = $tmp[6];
		$info['brand'] = $tmp[7];
	}
	return $info;
}

// file utilities

function cfgFileWrite($fileName, &$fileLines, $keySep = ' ', $chgMode = 0664)
{
	// open the target file in write mode
	$fHandle = fopen($fileName, 'w');
	if (!$fHandle)
	{
		return 10;
	}
	/* if $fileLines is an array, assume that each element is a line
		 of key ($keySep) value.
	*/
	if (is_array($fileLines))
	{
		foreach (array_keys($fileLines) as $cfgKey)
		{
			fwrite($fHandle, $cfgKey . $keySep . $fileLines[$cfgKey] . PHP_EOL);
		}
	}
	else
	{
		fwrite($fHandle, $fileLines);
	}
	//
	fclose($fHandle);
	chmod($fileName, $chgMode);
	return 0;
}

function cfgFileRead($fileName, $keySep = ' ', $trimTokens = false)
{
	if(!file_exists($fileName))
	{
		return false;
	}
	//
	$result = array();
	$fileLines = file($fileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	if (is_array($fileLines))
	{
		foreach (array_keys($fileLines) as $lineNum)
		{
			$tokens = explode($keySep, $fileLines[$lineNum]);
			if(count($tokens) == 2)
			{
				if ($trimTokens)
				{
					$tokens[0] = trim($tokens[0]);
					$tokens[1] = trim($tokens[1]);
				}
				$result[$tokens[0]] = $tokens[1];
			}
		}
	}
	return $result;
}

function fileWrite($fileName, &$fileLines, $mode = 'w', $chgMode = 0664)
{
	$bytes = false;
	// open the target file
	$fHandle = fopen($fileName, $mode);
	if (!$fHandle)
	{
		return false;
	}
	if (is_array($fileLines))
	{
		foreach (array_keys($fileLines) as $lineKey)
		{
			$bytes = fwrite($fHandle, $fileLines[$lineKey] . PHP_EOL);
		}
	}
	else
	{
		$bytes = fwrite($fHandle, $fileLines);
	}
	//
	fclose($fHandle);
	chmod($fileName, $chgMode);
	return (bool) $bytes;
}

function fileDownloadRequest($srcFile, $outFilename = null, $immediate = true, $chunk = 0)
{
	$fileSize = false;
	if ($outFilename === null)
	{
		$outFilename = basename($outFilename);
	}

	if (file_exists($srcFile))
	{
		$fileSize = filesize($srcFile);
		header('Content-Type: application/octet-stream');
		header("Content-Disposition: attachment; filename=\"$outFilename\"");
		header("Content-Length: $fileSize");

		if ($chunk > 0)
		{
			$fh = fopen($srcFile, 'rb');
			while (($buf = fread($fh, 4096)) !== false)
			{
				echo $buf;
			}
			fclose($fh);
		}
		else
		{
			readfile($srcFile);
		}

		if ($immediate)
		{
			exit;
		}
	}
	return $fileSize;
}

function getSmtpConf(&$cfgPointer)
{
	return $cfgPointer['config']['notifications']['email'];
}

function saveSysSmtpConf($newCfg, &$cfgPointer)
{
	$cfgPointer['config']['notifications']['email'] = $newCfg;
	write_config();
}

function writeSmtpConf(&$cfgPointer)
{
	$retval = 1;
	$smtpConf = getSmtpConf($cfgPointer);
	// just to be sure not to corrupt an existing configuration if we get a null or empty set
	if (is_array($smtpConf))
	{
		if(!empty($smtpConf))
		{
			$newConfig = array();
			//
			$newConfig['account'] = 'default';
			$newConfig['host'] = $smtpConf['host'];
			//
			if (isset($smtpConf['username']))
			{
				if (!empty($smtpConf['username']))
				{
					if (isset($smtpConf['authtype']))
					{
						$newConfig['auth'] = $smtpConf['authtype'];
					}
					$newConfig['user'] = $smtpConf['username'];
					$newConfig['password'] = $smtpConf['password'];
				}
			}
			//
			if (isset($smtpConf['enctype']))
			{
				switch ($smtpConf['enctype'])
				{
					case 'tls':
					{
						$newConfig['tls'] = 'on';
						$newConfig['tls_starttls'] = 'on';
						break;
					}
					case 'smtps':
					{
						$newConfig['tls'] = 'on';
						$newConfig['tls_starttls'] = 'off';
						break;
					}
				}
				if (isset($newConfig['tls']))
				{
					if (isset($smtpConf['disablecertcheck']))
					{
						$newConfig['tls_certcheck'] = 'off';
					}
					else
					{
						$newConfig['tls_certcheck'] = 'on';
						$newConfig['tls_trust_file'] = '/etc/ssl/certs/ca-certificates.crt';
					}
				}
			}
			//
			$newConfig['from'] = $smtpConf['address'];
			$addressParts = explode('@', $smtpConf['address']);
			$newConfig['maildomain'] = $addressParts[1];
			//
			if (isset($smtpConf['port']))
			{
				if (!empty($smtpConf['port']))
				{
					$newConfig['port'] = $smtpConf['port'];
				}
			}
			//
			$newConfig['syslog'] = 'LOG_LOCAL0';
		}
		// write setting to /etc/msmtp.conf
		$retval = cfgFileWrite('/etc/msmtp.conf', $newConfig);
	}
	return $retval;
}

function getActualMounts($rwonly = false)
{
	$retval = array();
	$ereg = '^(\/dev\/(?:nand|mmcblk\d|[a-z]{3})(?:p{1}[\d]+|[\d]+|[a-z]{1}))\son\s([\/\w\-\_]+)';
	if ($rwonly)
		$ereg .= '\stype\s\w+\s\(rw,';
	//
	exec('/bin/mount', $out);
	foreach (array_keys($out) as $idx)
	{
		// not a block device partition?
		if (strpos($out[$idx], '/dev/') !== 0)
			continue;
		//
		if (preg_match("/$ereg/", $out[$idx], $regs))
		{
			$retval[$regs[2]] = $regs[1];
		}
	}
	return $retval;
}

function whichAppsOwnsFilesOnMounts($rwMounts)
{
	$retval = array();
	foreach (array_keys($rwMounts) as $kMount)
	{
		unset($out);
		exec("/bin/lsof {$kMount}", $out);
		// shift the header off
		array_shift($out);
		foreach (array_keys($out) as $rowIdx)
		{
			$app = preg_split('/\s+/', $out[$rowIdx], 3);
			$retval[$app[0]] = $app[1];
		}
	}
	return $retval;
}

function stopProcess($process, $signal = 'TERM', $goNull = true, $stdInEqStdOut = true)
{
	$cmd = 'busybox kill';
	if (!is_integer($process))
	{
		$cmd = 'busybox killall';
		$process = escapeshellarg($process);
	}
	$nullRedir = ' > /dev/null';
	if (!$goNull)
	{
		$nullRedir = '';
	}
	$inoutEq = ' 2>&1';
	if (!$stdInEqStdOut)
	{
		$inoutEq = '';
	}

	exec("$cmd -{$signal} {$process}{$nullRedir}{$inoutEq}", $discard, $retval);
	return $retval;
}

function getHaltCmds($arrApps, $signal = 'TERM', $filter = array())
{
	$cmds = array();
	// never kill the shell executing it
	$filter[] = 'sh';
	$arrApps = array_diff_key($arrApps, array_flip($filter));
	foreach (array_keys($arrApps) as $app)
	{
		$pid = escapeshellarg($arrApps[$app]);
		$cmds[] = "busybox kill -{$signal} $pid";
	}
	return $cmds;
}

function getUmountCmds($arrUnmount)
{
	function lencmp($a, $b)
	{
		return strlen($b) - strlen($a);
	}

	// deeper mount points first
	uksort($arrUnmount, 'lencmp');
	$cmds = array();
	$cmds[] = 'sync';
	foreach (array_keys($arrUnmount) as $mp)
	{
		$cmds[] = "umount $mp";
	}

	return $cmds;
}

function queueFinalShutdown($mode = 'reboot')
{
	$cmds = array('#!/bin/sh',
		'PATH=/sbin:/bin',
		'export PATH',
		'source /etc/functions.sh',
		'',
		'sleep 1'
	);

	$rwMounts = getActualMounts(true);
	foreach (array_keys($rwMounts) as $mp)
	{
		$cmds[] = "killByMount \"TERM\" \"$mp\"";
		$cmds[] = 'sleep 0.1';
	}
	$cmds = array_merge($cmds, getUmountCmds($rwMounts));

	$cmds[] = 'sleep 0.5';
	$do = '/sbin/reboot';
	if ($mode == 'poweroff')
	{
		$do = '/sbin/poweroff';
	}
	$cmds[] = $do;
	$retval = fileWrite('/tmp/cleanshutdown.sh', $cmds, 'w', 0754);
	exec('sync');
	exec('nohup /tmp/cleanshutdown.sh > /dev/null 2>&1 &');
	if ($retval === false)
		return 1;
	//
	return 0;
}

?>