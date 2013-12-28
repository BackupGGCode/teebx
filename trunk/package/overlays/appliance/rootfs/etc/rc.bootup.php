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

	require_once('globals.inc');

	/* let the other functions know we're booting */
	$g['booting'] = true;
	touch("{$g['varrun_path']}/booting");

	/* parse the configuration and include all functions used below */
	require_once('config.inc');
	require_once('functions.inc');
	require_once('fileutils.lib.php');
	require_once('smtpconf.lib.php');
	require_once('initsvc.storage.php');

	/* check whether config reset is desired (via hardware button on wrap and alix23x) */
	echo " - Checking reset button... ";
	system_check_reset_button();
	echo 'done', PHP_EOL;

	/* convert configuration, if necessary */
	/*
	echo " - Converting configuration... ";
	convert_config();
	echo 'done', PHP_EOL;
	*/

	/* load kernel modules */
	echo " - Loading kernel modules... ";
	system_load_kernel_modules();
	echo 'done', PHP_EOL;

	/* run any early shell commands specified in config.xml */
	echo ' - Running early shell commands... ';
	system_do_shell_commands(1);
	echo 'done', PHP_EOL;

	echo ' - Configuring storage... ', PHP_EOL;
	setupStorageDevices($config);
	echo 'done', PHP_EOL;

	/* execute package boot routines */
	/*
	echo ' - Booting all packages... ';
	packages_boot_all();
	echo 'done', PHP_EOL;
	*/

	/* set up our timezone */
	echo ' - Configuring timezone and locale settings... ';
	writeSysRegSettings($config);
	echo 'done', PHP_EOL;

	/* set up our hostname */
	echo ' - Configuring hostname... ';
	system_hostname_configure();
	echo 'done', PHP_EOL;

	/* make hosts file */
	echo ' - Generating hosts... ';
	system_hosts_generate();
	echo 'done', PHP_EOL;

	/* generate resolv.conf */
	echo ' - Generating resolv.conf... ';
	system_resolvconf_generate();
	echo 'done', PHP_EOL;

	/* configure loopback interface */
	echo ' - Configuring network loopback interface... ';
	network_loopback_configure();
	echo 'done', PHP_EOL;

	/* set up LAN interface */
	echo ' - Configuring LAN interface... ';
	network_lan_configure();
	echo 'done', PHP_EOL;

	/* initial NTP sync */
	echo ' - Initial NTP time sync... ';
	system_ntp_initial_sync();
	echo 'done', PHP_EOL;

	/* generate machine specific ssl certificate */
	//echo " - Upgrading https certificate... ";
	//system_upgrade_https_certificate();
	//echo 'done', PHP_EOL;

	/* SMTP client configuration */
	echo ' - Configuring SMTP client... ';
	writeSmtpConf($config);
	echo 'done', PHP_EOL;

	/* start web server */
	echo ' - Starting web UI... ';
	system_update_httpd_settings();
	echo 'done', PHP_EOL;

	/* configure console menu */
	echo ' - Configuring console ... ';
	system_update_shell_password();
	system_console_configure();
	system_sshd_configure();
	echo 'done', PHP_EOL;

	/* start dyndns service */
	echo ' - Configuring dyndns... ';
	services_dyndns_configure();
	echo 'done', PHP_EOL;

	/* static IP address? -> attempt DNS update *//*
	if ($config['interfaces']['lan']['topology'] == "public" ||
		$config['interfaces']['lan']['topology'] == "natstatic")
		services_dnsupdate_process();*/

	/* run any shell commands specified in config.xml */
	echo ' - Running shell commands... ';
	system_do_shell_commands();
	echo 'done', PHP_EOL;

	/* run platform specific applications*/
	echo ' - Auto configuring DAHDI ports... ';
	dahdi_autoconfigure_ports();
	echo 'done', PHP_EOL;

	echo ' - Auto configuring Analog phones... ';
	analog_autoconfigure_phones();
	echo 'done', PHP_EOL;

	echo ' - Auto configuring ISDN phones... ';
	isdn_autoconfigure_phones();
	echo 'done', PHP_EOL;

	/* start up Asterisk */
	echo ' - Starting Asterisk... ', PHP_EOL;
	pbx_configure();
	echo 'done', PHP_EOL;

	echo ' - Configuring cron services... ';
	system_cron_configure();
	echo 'done', PHP_EOL;

	echo ' - Configuring displays... ';
	system_displays_configure();
	echo 'done', PHP_EOL;

	/* done */
	unlink("{$g['varrun_path']}/booting");
	$g['booting'] = false;
?>
