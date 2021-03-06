#!/usr/bin/php-cgi -f
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

	/* parse the configuration and include all functions used below */
	require_once("config.inc");
	require_once("functions.inc");
	require_once('console.msg.inc');
	require_once('blockdevices.lib.php');

	$rin = fopen('php://stdin', 'r');
	$rout = fopen('php://stdout', 'w');

	$disks = getBlockDevices();
	echo "\n", 'Valid disks are:', "\n\n";
	foreach (array_keys($disks) as $descriptor)
	{
		// skip removable devices
		if ($disks[$descriptor]['removable'])
			continue;
		// show anything else
		echo ' ',
			basename($descriptor),
			' -> ',
			$disks[$descriptor]['info'],
			' (', $disks[$descriptor]['sizelabel'], ')',
			PHP_EOL;
		//
	}

	do
	{
		fwrite($rout, "\nEnter the device name you wish to install onto: ");
		$target_disk = chop(fgets($rin));
		if ($target_disk === '')
		{
			exit(0);
		}
	}
	while (!array_key_exists("/dev/$target_disk", $disks));

	getBanner($msg_dev_overwrite, 'WARNING!', $target_disk);
	fwrite($rout, 'Do you want to proceed? (y/n)');

	if (strcasecmp(chop(fgets($rin)), "y") == 0)
	{
		echo "Installing...";
		mwexec("/bin/gunzip -c /offload/firmware.img.gz | dd of=/dev/{$target_disk} bs=512");
		echo "done\n";

		/* copy existing configuration */
		echo "Copying configuration...";
		@mkdir("/mnttmp");
		mwexec("/bin/mount -w -o noatime /dev/{$target_disk}1 /mnttmp");
		mwexec("cp {$g['conf_path']}/config.xml /mnttmp/conf/");
		mwexec("/bin/umount /mnttmp");
		echo "done\n";

		system_reboot_sync();
	}

?>
