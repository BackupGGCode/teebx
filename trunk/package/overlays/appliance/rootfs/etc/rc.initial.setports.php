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

	$in = fopen('php://stdin', 'r');
	$out = fopen('php://stdout', 'w');

	$iflist = network_get_interfaces();

	fwrite($out, "Valid interfaces are:\n");
	foreach ($iflist as $iface => $ifa)
	{
		fwrite($out, sprintf("% -8s%s%s\n", $iface, $ifa['mac'],
			$ifa['up'] ? "   (up)" : "")
		);
	}

	fwrite($out,
		"\nIf you don't know the names of your interfaces, you may choose to use\n" .
		"auto-detection. In that case, disconnect all interfaces before you begin,\n" .
		'and reconnect each one when prompted to do so.'
	);
	do
	{
		fwrite($out, "\nEnter the LAN interface name or 'a' for auto-detection:\n >");
		$lanif = chop(fgets($in));
		if ($lanif === '')
		{
			exit(0);
		}

		if ($lanif === 'a')
		{
			$lanif = autodetect_interface('LAN', $in, $out);
		}
		else if (!array_key_exists($lanif, $iflist))
		{
			fwrite($out, "\nInvalid interface name '{$lanif}'\n");
			unset($lanif);
			continue;
		}
	} while (!$lanif);

	fwrite($out, 
		"The interfaces will be assigned as follows:\n" .
		"  LAN  -> {$lanif}\n" .
		"The PBX will reboot after saving the changes.\n" .
		'Do you want to proceed? (y/n)'
	);
	if (strcasecmp(chop(fgets($in)), "y") == 0)
	{
		$config['interfaces']['lan']['if'] = $lanif;
		write_config();
		fwrite($out, "The system is rebooting now, please wait...");
		system_reboot_sync();
	}

	function autodetect_interface($ifname, &$in, &$out)
	{
		$iflist_prev = network_get_interfaces();
		fwrite($out, 
			"Connect the {$ifname} interface now and make sure that the link is up.\n" .
			"Then press RETURN to continue.\n"
		);

		fgets($in);
		$iflist = network_get_interfaces();
		foreach ($iflist_prev as $ifn => $ifa)
		{
			if (!$ifa['up'] && $iflist[$ifn]['up']) {
				fwrite($out,  "Detected link-up on interface {$ifn}.\n");
				return $ifn;
			}
		}

		fwrite($out, "No link-up detected.\n");
		return null;
	}

?>
