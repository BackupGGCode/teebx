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

function getMemoryStatus()
{
	exec('/usr/bin/free -b', $memory, $retval);
	if ($retval == 1)
		return false;
	//
	$memory = preg_split("/\s+/", $memory[1]);
	// eglibc + busybox 1.20.2 reports:
	// Array ( [0] => Mem: [1] => 513652 [2] => 40516 [3] => 473136 [4] => 0 [5] => 520 )
	return array('total' => $memory[1], 'used' => $memory[1] - $memory[3], 'free' => $memory[3]);
}

/**
* Kills a process by pid file
*/
function killbypid($pidfile)
{
	sigkillbypid($pidfile, "TERM");
	unlink_if_exists($pidfile);
}

/**
* Sigkills a process by pid file
*/
function sigkillbypid($pidfile, $sig)
{
	if (file_exists($pidfile))
	{
		mwexec("/bin/kill -s $sig `/bin/cat " . $pidfile . "`");
	}
}

/**
* Kills a process by name
*/
function killbyname($procname)
{
	return mwexec("busybox killall " . escapeshellarg($procname));
}

/**
* Kills a process by name
*/
function sigkillbyname($procname, $sig)
{
	return mwexec("busybox killall -{$sig} " . escapeshellarg($procname));
}

function killbypidnumber($pidnumber)
{
	return mwexec("busybox kill " . escapeshellarg($pidnumber));
}

/**
* Wrapper for exec()
*/
function mwexec($command)
{
	global $g;

	if ($g['debug'])
	{
		if (!$_SERVER['REMOTE_ADDR'])
			echo "mwexec(): $command\n";
		passthru($command, $retval);
	}
	else
	{
		exec("$command > /dev/null 2>&1", $oarr, $retval);
	}

	return $retval;
}

/**
* Wrapper for exec() in background
*/
function mwexec_bg($command)
{
	global $g;

	if ($g['debug'])
	{
		if (!$_SERVER['REMOTE_ADDR'])
			echo "mwexec(): $command\n";
	}

	exec("nohup $command > /dev/null 2>&1 &");
}

/**
* Unlink a file, if it exists
*/
function unlink_if_exists($fn)
{
	if (file_exists($fn))
		unlink($fn);
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

/**
* Manual attributes are needed in a variety of places,
* this generates an appropriate conf snippet.
* XXX : util_gen_*() functions should be written for language, qualify, codecs...
*/
function util_gen_manual_attributes($ma_array)
{
	$conf = "";
	if (is_array($ma_array))
	{
		$conf .= "; manual attributes\n";
		foreach ($ma_array as $ma)
		{
			$conf .= base64_decode($ma) . "\n";
		}
	}

	return $conf;
}

function util_file_put_contents($contents, $filename)
{
	$fd = fopen($filename, "w");
	if (!$fd)
	{
		printf("Error: cannot open $filename in util_file_put_contents().\n");
		return 1;
	}
	fwrite($fd, $contents);
	fclose($fd);

	return 0;
}

/* generate a self-signed SSL cert/key (1024-bit, valid for 2 years) and return it in PEM format */
function generate_self_signed_cert()
{
	global $g, $config;

	$orgname = "TeeBX";
	$cn = $config['system']['hostname'] . "." . $config['system']['domain'];

	/* check local clock first - must be set to something after the release date
	  (subtract a day to avoid TZ problems with early adopters) */
	$buildtime = trim(file_get_contents("{$g['etc_path']}/version.buildtime.unix")) - 86400;
	if (time() < $buildtime)
		return false;

	$dn = array(
		"organizationName" => $orgname,
		"commonName" => $cn
		);

	$conf = array(
		"digest_alg" => "sha1",
		"private_key_bits" => 1024
		);

	$privkey = openssl_pkey_new($conf);
	$csr = openssl_csr_new($dn, $privkey, $conf);
	$sscert = openssl_csr_sign($csr, null, $privkey, 730, $conf, time());

	openssl_x509_export($sscert, $certout);
	openssl_pkey_export($privkey, $pkeyout);

	/* sanity check */
	if (strpos($certout, "BEGIN CERTIFICATE") !== false && strpos($pkeyout, "BEGIN RSA PRIVATE KEY") !== false)
		return array('cert' => $certout, 'key' => $pkeyout);
	else
		return false;
}

function util_pwgen()
{
	return exec("openssl rand -base64 6");
}

function util_file_get($fullpath)
{
	// search config for a stored file
	$files = $GLOBALS['config']['manual-file'];
	$n = count($files);
	for ($i = 0; $i < $n; $i++)
	{
		if ($files[$i]['full-path'] == $fullpath)
		{
			$file = $files[$i];
			$file['contents'] = base64_decode($file['encoded-contents']);
			return $file;
		}
	}
	// nothing found, return a blank file
	$file['full-path'] = $fullpath;
	$file['contents'] = '';
	$file['mode'] = 'append';

	return $file;
}

function util_file_put($fullpath, $contents, $mode)
{
	// search config for a stored file and remove it
	$n = count($GLOBALS['config']['manual-file']);
	for ($i = 0; $i < $n; $i++)
	{
		if ($GLOBALS['config']['manual-file'][$i]['full-path'] == $fullpath)
		{
			unset($GLOBALS['config']['manual-file'][$i]);
			break;
		}
	}
	// construct the new file if there is content
	if (strlen($contents))
	{
		$file['full-path'] = $fullpath;
		$file['encoded-contents'] = base64_encode($contents);
		$file['mode'] = $mode;
		// add it to the config
		$GLOBALS['config']['manual-file'][] = $file;
	}

	write_config();
}

function util_file_write($fullpath, $contents)
{
	// open the file for writing
	$fd = fopen($fullpath, "w");
	if (!$fd)
	{
		printf("Error: cannot open " . $fullpath . "for writing!\n");
		return 1;
	}
	// should this file be appended or overridden with any manual input?
	if (isset($GLOBALS['config']['manual-file']))
	{
		$files = $GLOBALS['config']['manual-file'];
		$n = count($files);
		for ($i = 0; $i < $n; $i++)
		{
			if ($files[$i]['full-path'] == $fullpath)
			{
				if ($files[$i]['mode'] == 'append')
				{
					$append = base64_decode($files[$i]['encoded-contents']);
				}
				else if ($files[$i]['mode'] == 'override')
				{
					$contents = base64_decode($files[$i]['encoded-contents']);
				}
			}
		}
	}
	// write out contents and append
	fwrite($fd, $contents . "\n");
	if (isset($append))
	{
		if (!empty($append))
		{
			fwrite($fd, "\n\n" . $append . "\n");
		}
	}
	fclose($fd);

	return 0;
}

/* modified from:
	- http://icfun.blogspot.com/2008/11/php-get-remote-file-size.html
*/
function util_get_remote_filesize($url)
{
	$url = preg_replace('/http:\/\//', '', $url);
	if (!preg_match('/(.*?)(\/.*)/', $url, $match))
	{
		return - 1;
	}

	$domain = $match[1];
	$url = $match[2];
	$http_request .= "HEAD " . $url . " HTTP/1.0\r\n";
	$http_request .= "\r\n";

	$fp = fsockopen($domain, 80, $errno, $errstr);
	if ($fp)
	{
		fputs($fp, $http_request);
		while (!feof($fp))
		{
			$http_response .= fgets($fp, 128);
		}
		fclose($fp);
	}

	if (preg_match("/Content\-Length: (\d+)/i", $http_response, $match))
	{
		return $match[1];
	}
	else
	{
		return - 1;
	}
}

?>
