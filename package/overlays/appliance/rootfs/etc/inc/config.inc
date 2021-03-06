<?php
/*
	$Id$
	part of TeeBX VoIP Communication platform (http://www.teebx.com/)
	Copyright(C) 2010 - 2014 Giovanni Vallesi.
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
require_once('console.msg.inc');

if (!isset($g['booting']))
{
	$g['booting'] = false;
}
$livecd = false;
$g['platform'] = "unknown";
$product_name = "TeeBX";
$sysDev = false;
$sysPart = false;
$partName = false;

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

	/* check if there's already an fstab (NFS booting?) */
	if (!file_exists("{$g['etc_path']}/fstab"))
	{
		if (file_exists("/offload/livecd"))
		{
			$livecd = true;
			getBanner($msg_cfg_nosave, 'WARNING!');
			sleep(3);
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
				getBanner($msg_cfg_mismatch, 'WARNING!', $product_name);
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
	// TODO: function candidate for removal, placeolder until refactoring finished.
	// no uuid will be used!
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
