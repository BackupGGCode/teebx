#!/usr/bin/php-cgi -f
<?php
/*
	$Id$
	part of BoneOS build platform (http://www.teebx.com/)
	Copyright(C) 2011 - 2014 Giovanni Vallesi.
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

	$prodInfo = getVersionInfo();
	$buildtime = strftime('%c', $prodInfo['timestamp']);
	$copyright_info = "{$prodInfo['prod']} is Copyright (C) 2010-2013 Giovanni Vallesi. All rights reserved.\n" .
		"    AskoziaPBX base Copyright (C) 2007-2011 IKT. All rights reserved.\n" .
		"    m0n0wall base Copyright (C) 2002-2007 Manuel Kasper. All rights reserved.\n";

	$usingDhcp = true;
	if (isset($config['interfaces']['lan']['dhcp']))
	{
		if ($config['interfaces']['lan']['dhcp'] == 'no')
		{
			$usingDhcp = false;
		}
	}
	if ($usingDhcp)
	{
		$ip_line = 'LAN IP address assigned via DHCP';
		$ip_info = 'unassigned';
		$interface = network_get_interface($config['interfaces']['lan']['if']);
		if ($interface['ipaddr'])
		{
			$ip_info = $interface['ipaddr'];
		}
	}
	else
	{
		$ip_line = 'LAN IP address';
		$ip_info = $config['interfaces']['lan']['ipaddr'] . ' on ' . $config['interfaces']['lan']['if'];
	}

	$defPassWarning = '';
	if ($config['system']['username'] == 'admin' & $config['system']['password'] == 'teebx')
	{
		$defPassWarning = 'WARNING! Default username/password (admin/teebx) in use, please change it!!';
	}
	echo <<<EOD
* {$prodInfo['prod']} (version {$prodInfo['buid']}, source rev. {$prodInfo['rev']})
  built on {$buildtime} for {$g['platform']}
  {$copyright_info}


  {$ip_line}: {$ip_info}
  {$defPassWarning}


EOD;
?>
