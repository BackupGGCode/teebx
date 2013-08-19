<?php
/*
  $Id$
part of BoneOS build platform (http://www.teebx.com/)
Copyright(C) 2013 Giovanni Vallesi (http://www.teebx.com).
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

define('CONSOLE_WIDTH', 80);
define('BANNER_CHAR', '*');
define('BANNER_LBL', '[ %s ]');
define('LBL_LEFT', 2);
define('TXT_MARGIN', ' ');

$msg_cfg_nosave = 'No FAT formatted USB memory stick or floppy disk could be found. Your configuration changes will be lost on reboot.';
$msg_cfg_missing = 'The device that contains the configuration file (config.xml) could not be found. %s cannot continue booting.';
$msg_dev_overwrite = array('The Appliance is about to be installed onto the %s device.',
	'- Everything on this device will be erased!',
	'- This cannot be undone!',
	'- The Appliance will reboot after installation.');

function getBannerTop($label, $out = true)
{
	$label = sprintf(BANNER_LBL, $label);
	$label = str_repeat(BANNER_CHAR, LBL_LEFT) . $label;
	$label = str_pad($label, CONSOLE_WIDTH, BANNER_CHAR, STR_PAD_RIGHT) . PHP_EOL;
	if ($out)
	{
		echo $label;
		return;
	}
	return $label;
}

function getBannerBody(&$msg, $embedText = null, $out = true)
{
	$body = '';
	$width = CONSOLE_WIDTH - (strlen(TXT_MARGIN)*2) - 2;

	if (!is_array($msg))
	{
		// looks like a mess? Done that way to avoid copying $msg
		if (!is_null($embedText))
		{
			$lines = explode(PHP_EOL, wordwrap(sprintf($msg, $embedText), $width, PHP_EOL));
		}
		else
			$lines = explode(PHP_EOL, wordwrap($msg, $width, PHP_EOL));
	}
	else
	{
		$lines = $msg;
		if (!is_null($embedText))
		{
			$lines[0] = sprintf($lines[0], $embedText);
		}
	}
	//
	foreach (array_keys($lines) as $idx)
	{
		$body .= BANNER_CHAR .
			TXT_MARGIN .
			str_pad($lines[$idx], $width, ' ', STR_PAD_RIGHT) .
			TXT_MARGIN .
			BANNER_CHAR .
			PHP_EOL;
	}
	if ($out)
	{
		echo $body;
		return;
	}
	return $body;
}

function getBannerBottom($out = true)
{
	$bottom = str_repeat(BANNER_CHAR, CONSOLE_WIDTH);
	if ($out)
	{
		echo $bottom;
		return;
	}
	return $bottom;
}

function getBanner(&$msg, $label, $embedText = null, $clearance = 2)
{
	$spoiler = '';
	if ($clearance > 0)
	{
		$spoiler = str_repeat(PHP_EOL, $clearance);
	}
	echo $spoiler;
	getBannerTop($label);
	getBannerBody($msg, $embedText);
	getBannerBottom();
	echo $spoiler;
}
?>