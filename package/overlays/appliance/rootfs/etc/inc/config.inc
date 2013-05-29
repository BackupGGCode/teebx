<?php
/*
	$Id$
	part of TeeBX VoIP Communication platform (http://www.teebx.com/)
	Copyright(C) 2010 - 2013 Giovanni Vallesi.
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

/* include globals/utility/XML parser files */
require_once('globals.inc');
require_once('util.inc');
require_once('xmlparse.inc');
require_once('tzdata.lib.php');

if (!isset($g['booting']))
{
	$g['booting'] = false;
}
$livecd = false;
$g['platform'] = "unknown";
$product_name = "TeeBX";

/* read platform */
if (file_exists("{$g['etc_path']}/platform"))
{
	$g['platform'] = chop(file_get_contents("{$g['etc_path']}/platform"));
}

if (file_exists("{$g['etc_path']}/brand.product"))
{
	$product_name = chop(file_get_contents("{$g['etc_path']}/brand.product"));
}

if ($g['booting'])
{
	/* find the device where config.xml resides and write out an fstab */
	unset($cfgdevice);

	/* check if there's already an fstab (NFS booting?) */
	if (!file_exists("{$g['etc_path']}/fstab"))
	{
		if (file_exists("/offload/livecd"))
		{
			$livecd = true;
			echo <<<EOD


*******************************************************************************
* WARNING!                                                                    *
* No FAT formatted USB memory stick or floppy disk could be found.            *
* Your configuration changes will be lost on reboot.                          *
*******************************************************************************


EOD;
			sleep(3);
		}
		else
		{
			/* iterate through the disks fdisk finds until one contains a config.xml */
			$attempts = 3;
			$machine = 'generic';
			if (strpos(exec('uname -m'), 'armv') !== false)
			{
				$machine = 'ARM';
			}
			while ($attempts--)
			{
				unset($disks);
				unset($cfgdevice);
				if ($machine == 'ARM')
				{
					// most arm boards, else if needed in the future will check the $g['platform'] variable
					$disks[] = 'mmcblk0';
				}
				else
				{
					exec('fdisk -l | grep "^/dev/" | cut -c 6-8 | uniq', $disks);
				}
				echo "[      config] Found disks: " . implode(", ", $disks) . "\n";
				foreach ($disks as $disk)
				{
					if (strpos($disk, "mmcblk") !== false)
					{
						$part = 'p1';
					}
					else
					{
						$part = '1';
					}
					echo "[      config] Looking for configuration on: $disk partition $part...\n";
					if (mwexec("/bin/mount -r /dev/{$disk}{$part} {$g['cf_path']}") == 0)
					{
						if (file_exists("{$g['cf_conf_path']}/config.xml"))
						{
							$cfgdevice = $disk;
							$cfgpartition = $cfgdevice . $part;
							echo "[      config] Found configuration on $cfgdevice partition $part.\n";
							// leave partition mounted then exit both the nested loops
							break 2;
						}
						mwexec("/bin/umount -f {$g['cf_path']}");
					}
				}
				if ($attempts > 0)
				{
					echo "[      config] Configuration not found.\n";
					echo "[      config] Waiting for devices to settle and attempting again.\n";
					sleep(2);
				}
			}
		}

		if (!$cfgdevice && !$livecd)
		{
			/* no device found, print an error and die */
			echo <<<EOD


*******************************************************************************
* FATAL ERROR                                                                 *
* The device that contains the configuration file (config.xml) could not be   *
* found. $product_name cannot continue booting.                                  *
*******************************************************************************


EOD;

			mwexec('/sbin/halt');
			exit;
		}

		if (!$livecd)
		{
			/* write device name to a file for rc.firmware */
			$fd = fopen("{$g['varetc_path']}/cfdevice", 'w');
			fwrite($fd, $cfgdevice . "\n");
			fclose($fd);

			/* write out an fstab */
			$fd = fopen('/etc/fstab', 'w');

			$fstab = "/dev/{$cfgpartition} {$g['cf_path']} vfat ro 1 1\n";
			if (strpos($disk, 'mmcblk') !== false)
			{
				$part = 'p2';
			}
			else
			{
				$part = '2';
			}
			$fstab .= "/dev/{$cfgdevice}{$part} /offload ext2 ro 0 0\n";

			fwrite($fd, $fstab);
			fclose($fd);

			/* create a copy for the fstab generator to read in */
			copy('/etc/fstab', '/etc/fstab.boot');
		}
	}

	if (!$livecd)
	{
		/* mount all filesystems */
		echo "[      config] Mounting all filesystems...\n";
		$mountAllRes = mwexec('/bin/mount -a');
		if ($mountAllRes == 0)
		{
			echo "[      config] Done.\n";
		}
		else
		{
			echo "[      config] Mount error: $mountAllRes.\n";
			// TODO: consider dropping into a shell.
		}
	}
}

/* parse configuration */
if (!isset($noparseconfig))
	$noparseconfig = false;
if (!$noparseconfig)
{
	config_lock();

	/* see if there's a newer cache file */
	if (file_exists("{$g['tmp_path']}/configca.che") &&
			(filemtime("{$g['tmp_path']}/configca.che") >=
				filemtime("{$g['conf_path']}/config.xml")))
	{
		/* read cache */
		$config = unserialize(file_get_contents("{$g['tmp_path']}/configca.che"));
		$uniqid_map = unserialize(file_get_contents("{$g['tmp_path']}/uniqidca.che"));
	}
	else
	{
		if (!file_exists("{$g['conf_path']}/config.xml"))
		{
			if ($g['booting'])
			{
				if ($livecd)
				{
					/* try copying the default config. to the floppy/USB stick */
					reset_factory_defaults();

					echo "[      config] No XML configuration file found - using factory defaults.\n";
				}
				else
				{
					echo "[      config] XML configuration file not found. $product_name cannot continue booting.\n";
					// TODO: consider to drop into a shell instead of halt.
					mwexec('/sbin/halt');
					exit;
				}
			}
			else
			{
				config_unlock();
				exit(0);
			}
		}

		$config = xml_to_array("{$g['conf_path']}/config.xml", $g['xml_rootobj']);

		if ((float)$config['version'] > (float)$g['latest_config'])
		{
			if ($g['booting'])
			{
				echo <<<EOD


*******************************************************************************
* WARNING!                                                                    *
* The current configuration has been created with a newer version of          *
* $product_name than this one! This can lead to serious misbehavior and even     *
* security holes! You are urged to either upgrade to a newer version of       *
* $product_name or revert to the default configuration immediately!              *
*******************************************************************************


EOD;
			}
		}

		/* write config cache */
		$fd = @fopen("{$g['tmp_path']}/configca.che", "wb");
		if ($fd)
		{
			fwrite($fd, serialize($config));
			fclose($fd);
		}

		/* write uniqid_map cache */
		$uniqid_map = config_build_uniqid_map($config);
		$fd = @fopen("{$g['tmp_path']}/uniqidca.che", "wb");
		if ($fd)
		{
			fwrite($fd, serialize($uniqid_map));
			fclose($fd);
		}
	}
	config_unlock();
}
//
if (!isset($config['system']['timezone']))
{
	$config['system']['timezone'] = '99000';
}
$arrTz = getTzData($config['system']['timezone']);
date_default_timezone_set($arrTz['name']);

if (isset($config['system']['webgui']['language']))
{
	setlocale(LC_TIME, $config['system']['webgui']['language'] . '.UTF-8');
}

/**
* Mounts the flash card read/write
*/
function conf_mount_rw()
{
	global $g;

	/* don't use mount -u anymore
	   (doesn't sync the files properly and /bin/sync won't help either) */
	mwexec("/bin/umount -f {$g['cf_path']}");
	mwexec("/bin/mount -w -o noatime {$g['cf_path']}");
}

/**
* Mounts the flash card read only
*/
function conf_mount_ro()
{
	global $g;

	mwexec("/bin/umount -f {$g['cf_path']}");
	mwexec("/bin/mount -r {$g['cf_path']}");
}

/**
* Converts the configuration structure if updates require.
*/
function convert_config()
{
	global $config, $g;

	/* already at latest version */
	if ($config['version'] == $g['latest_config'])
	{
		return;
	}

	/* update e-mail notifications tls variable storage now that smtps is supported */
	if ($config['version'] == "2.0")
	{
		if ($config['notifications']['email']['tls'] == "yes")
		{
			$config['notifications']['email']['enctype'] = "tls";
			unset($GLOBALS['config']['notifications']['email']['tls']);
		}
		$config['version'] = "2.01";
	}

	/* update timezone storage format to only store id */
	if ($config['version'] == "2.01")
	{
		if ($config['system']['timezone'])
		{
			$pieces = explode("|", $config['system']['timezone']);
			$config['system']['timezone'] = $pieces[0];
		}
		$config['version'] = "2.02";
	}

	write_config();
}

/**
* Saves the system configuration and uniqid mapping
*/
function write_config()
{
	global $config, $g, $uniqid_map;

	config_lock();

	conf_mount_rw();

	/* make sure the clock settings are plausible */
	if (time() > mktime(0, 0, 0, 9, 1, 2004))
	{
		$config['lastchange'] = time();
	}

	/* generate configuration XML */
	$xmlconfig = array_to_xml($config, $g['xml_rootobj']);

	/* write configuration */
	$fd = fopen("{$g['cf_conf_path']}/config.xml", "w");

	if (!$fd)
	{
		die("Unable to open config.xml for writing in write_config()\n");
	}

	fwrite($fd, $xmlconfig);
	fclose($fd);

	conf_mount_ro();

	/* re-read configuration */
	$config = xml_to_array("{$g['conf_path']}/config.xml", $g['xml_rootobj']);
	/* build uniqid_map */
	$uniqid_map = config_build_uniqid_map($config);

	/* write config cache */
	$fd = @fopen("{$g['tmp_path']}/configca.che", "wb");
	if ($fd)
	{
		fwrite($fd, serialize($config));
		fclose($fd);
	}

	/* write uniqid_map cache */
	$fd = @fopen("{$g['tmp_path']}/uniqidca.che", "wb");
	if ($fd)
	{
		fwrite($fd, serialize($uniqid_map));
		fclose($fd);
	}

	config_unlock();
}

/**
* Builds a mapping of uniqids to their array offsets to speed up element lookups
*/
function config_build_uniqid_map($config)
{
	global $g;
	$uniqid_map = array();

	// providers and internal phones
	foreach ($g['technologies'] as $tech)
	{
		if (isset($config[$tech]['provider']))
		{
			$n = count($config[$tech]['provider']);
			for ($i = 0; $i < $n; $i++)
			{
				if (!is_array($config[$tech]['provider']))
				{
					continue;
				}
				$uniqid_map[$config[$tech]['provider'][$i]['uniqid']] = $i;
			}
		}
		if (isset($config[$tech]['phone']))
		{
			$n = count($config[$tech]['phone']);
			for ($i = 0; $i < $n; $i++)
			{
				if (!is_array($config[$tech]['phone']))
				{
					continue;
				}
				$uniqid_map[$config[$tech]['phone'][$i]['uniqid']] = $i;
			}
		}
	}
	// external phones
	if (isset($config['external']['phone']))
	{
		$n = count($config['external']['phone']);
		for ($i = 0; $i < $n; $i++)
		{
			if (!is_array($config['external']['phone']))
			{
				continue;
			}
			$uniqid_map[$config['external']['phone'][$i]['uniqid']] = $i;
		}
	}
	// conferencing
	if (isset($config['conferencing']['room']))
	{
		$n = count($config['conferencing']['room']);
		for ($i = 0; $i < $n; $i++)
		{
			if (!is_array($config['conferencing']['room']))
			{
				continue;
			}
			$uniqid_map[$config['conferencing']['room'][$i]['uniqid']] = $i;
		}
	}
	// faxes
	foreach ($g['fax_types'] as $tech)
	{
		if (isset($config[$tech]['fax']))
		{
			$n = count($config[$tech]['fax']);
			for ($i = 0; $i < $n; $i++)
			{
				if (!is_array($config[$tech]['fax']))
				{
					continue;
				}
				$uniqid_map[$config[$tech]['fax'][$i]['uniqid']] = $i;
			}
		}
	}
	// callgroups
	if (isset($config['dialplan']['callgroup']))
	{
		$n = count($config['dialplan']['callgroup']);
		for ($i = 0; $i < $n; $i++)
		{
			if (!is_array($config['dialplan']['callgroup']))
			{
				continue;
			}
			$uniqid_map[$config['dialplan']['callgroup'][$i]['uniqid']] = $i;
		}
	}
	// applications
	if (isset($config['dialplan']['application']))
	{
		$n = count($config['dialplan']['application']);
		for ($i = 0; $i < $n; $i++)
		{
			if (!is_array($config['dialplan']['application']))
			{
				continue;
			}
			$uniqid_map[$config['dialplan']['application'][$i]['uniqid']] = $i;
		}
	}
	if (isset($config['dialplan']['application2']))
	{
		$n = count($config['dialplan']['application2']);
		for ($i = 0; $i < $n; $i++)
		{
			if (!is_array($config['dialplan']['application2']))
			{
				continue;
			}
			$uniqid_map[$config['dialplan']['application2'][$i]['uniqid']] = $i;
		}
	}
	// dahdi ports
	if (isset($config['interfaces']['dahdi-port']))
	{
		$n = count($config['interfaces']['dahdi-port']);
		for ($i = 0; $i < $n; $i++)
		{
			if (!is_array($config['interfaces']['dahdi-port']))
			{
				continue;
			}
			$uniqid_map[$config['interfaces']['dahdi-port'][$i]['uniqid']] = $i;
		}
	}
	// dahdi portgroups
	if (isset($config['interfaces']['dahdi-portgroup']))
	{
		$n = count($config['interfaces']['dahdi-portgroup']);
		for ($i = 0; $i < $n; $i++)
		{
			if (!is_array($config['interfaces']['dahdi-portgroup']))
			{
				continue;
			}
			$uniqid_map[$config['interfaces']['dahdi-portgroup'][$i]['uniqid']] = $i;
		}
	}
	// storage disks
	if (isset($config['storage']['disk']))
	{
		$n = count($config['storage']['disk']);
		for ($i = 0; $i < $n; $i++)
		{
			if (!is_array($config['storage']['disk']))
			{
				continue;
			}
			$uniqid_map[$config['storage']['disk'][$i]['uniqid']] = $i;
		}
	}
	//
	return $uniqid_map;
}

/**
* Overwrites the existing config.xml file with factory default values.
*/
function reset_factory_defaults()
{
	global $g;

	config_lock();

	conf_mount_rw();

	/* create conf directory, if necessary */
	if (!file_exists("{$g['cf_conf_path']}"))
	{
		@mkdir("{$g['cf_conf_path']}");
	}

	/* clear out /conf */
	$dh = opendir($g['conf_path']);
	while ($filename = readdir($dh))
	{
		if (($filename != ".") && ($filename != ".."))
		{
			unlink($g['conf_path'] . "/" . $filename);
		}
	}
	closedir($dh);

	/* copy default configuration */
	@copy("{$g['conf_default_path']}/config.xml", "{$g['conf_path']}/config.xml");

	conf_mount_ro();

	config_unlock();

	return 0;
}

/**
* Installs the specified configuration file into the global "conf path"
*/
function config_install($conffile)
{
	global $config, $g;

	if (!file_exists($conffile))
	{
		return 1;
	}

	if (!config_validate($conffile))
	{
		return 1;
	}

	config_lock();
	conf_mount_rw();

	copy($conffile, "{$g['conf_path']}/config.xml");

	conf_mount_ro();
	config_unlock();

	return 0;
}

/**
* Performs an XML validation of the configuration file.
*/
function config_validate($conffile)
{
	global $g, $xmlerr;

	$xml_parser = xml_parser_create();

	if (!($fp = fopen($conffile, "r")))
	{
		$xmlerr = "XML error: unable to open file";
		return false;
	}

	while ($data = fread($fp, 4096))
	{
		if (!xml_parse($xml_parser, $data, feof($fp)))
		{
			$xmlerr = sprintf("%s at line %d",
				xml_error_string(xml_get_error_code($xml_parser)),
				xml_get_current_line_number($xml_parser));
			return false;
		}
	}
	xml_parser_free($xml_parser);

	fclose($fp);

	return true;
}

/**
* Locks access to the configuration file.
* Lock is invalidated after 10 seconds.
*/
function config_lock()
{
	global $g;

	$lockfile = "{$g['varrun_path']}/config.lock";

	$n = 0;
	while ($n < 10)
	{
		/* open the lock file in append mode to avoid race condition */
		if ($fd = @fopen($lockfile, "x"))
		{
			/* succeeded */
			fclose($fd);
			return;
		}
		else
		{
			/* file locked, wait and try again */
			sleep(1);
			$n++;
		}
	}
}

/**
* Unlocks access to the configuration file
*/
function config_unlock()
{
	global $g;

	$lockfile = "{$g['varrun_path']}/config.lock";

	if (file_exists($lockfile))
	{
		unlink($lockfile);
	}
}

?>
