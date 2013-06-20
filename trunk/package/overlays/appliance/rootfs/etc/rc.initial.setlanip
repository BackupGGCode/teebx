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
	require_once('config.inc');
	require_once('functions.inc');

	$in = fopen('php://stdin', 'r');
	$out = fopen('php://stdout', 'w');

	// LAN IP via DHCP
	fwrite($out, "\nDo you want to use DHCP to configure your LAN interface? (y/n) ");

	if (strcasecmp(chop(fgets($in)), 'y') == 0)
	{
		$config['interfaces']['lan']['dhcp'] = 'yes';
		write_config();
		network_lan_configure();
		system_update_httpd_settings();

		fwrite($out,
			"The LAN interface will now be configured via DHCP.\n" .
			'Press ENTER to continue.'
		);

		fgets($in);
		exit(0);
	}
	else
	{
		$config['interfaces']['lan']['dhcp'] = 'no';
	}

	// Static LAN IP address
	do
	{
		fwrite($out, "\nEnter the new LAN IP address:\n >");
		$lanip = chop(fgets($in));
		if ($lanip === '')
		{
			exit(0);
		}
	}
	while (!verify_is_ipaddress($lanip));

	fwrite($out,
		"\nSubnet masks are to be entered as bit counts (as in CIDR notation).\n" .
		"  e.g. 255.255.255.0 = 24\n" .
		"         255.255.0.0 = 16\n" .
		"           255.0.0.0 =  8\n\n"
	);
	do
	{
		fwrite($out, "Enter the new LAN subnet bit count:\n >");
		$lanbits = chop(fgets($in));
		if ($lanbits === '')
		{
			exit(0);
		}
	}
	while (!is_numeric($lanbits) || ($lanbits < 1) || ($lanbits > 31));

	// LAN Gateway
	do
	{
		fwrite($out, "\nEnter the LAN gateway IP address:\n >");
		$gwip = chop(fgets($in));
		if ($gwip === '') {
			exit(0);
		}
	}
	while (!verify_is_ipaddress($gwip));

	//  DNS
	do
	{
		fwrite($out, "\nEnter the DNS IP address:\n >");
		$dnsip = chop(fgets($in));
		if ($dnsip === '')
		{
			exit(0);
		}
	}
	while (!verify_is_ipaddress($dnsip));


	$config['interfaces']['lan']['ipaddr'] = $lanip;
	$config['interfaces']['lan']['subnet'] = $lanbits;
	$config['interfaces']['lan']['gateway'] = $gwip;

	unset($config['system']['dnsserver']);
	$config['system']['dnsserver'][] = $dnsip;

	$proto = 'http';
	$webgui_restart = false;
	// the next block is disabled,  until https will be available
	/*
	if ($config['system']['webgui']['protocol'] == 'https')
	{
		fwrite($out, "\nDo you want to revert to HTTP as the webGUI protocol? (y/n) ");
		if (strcasecmp(chop(fgets($in)), 'y') == 0)
		{
			$config['system']['webgui']['protocol'] = 'http';
			$webgui_restart = true;
		}
		else
		{
			$proto = 'https';
		}
	}
	*/

	write_config();
	system_resolvconf_generate();
	network_lan_configure();
	if ($webgui_restart)
	{
		system_update_httpd_settings();
	}

	fwrite($out,
		"The LAN IP address has been set to $lanip/$lanbits.\n" .
		"You can now access the webGUI by opening the following URL\n" .
		"in your browser:\n" .
		"  $proto://$lanip/\n\n" .
		'Press RETURN to continue.'
	);
	fgets($in);
?>
