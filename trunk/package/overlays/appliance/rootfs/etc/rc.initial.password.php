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

	$default_password = 'teebx';
	if (file_exists("{$g['etc_path']}/brand.password")) {
		$default_password = chop(file_get_contents("{$g['etc_path']}/brand.password"));
	}

	fwrite($out,
		"The system password will be reset to the default (which is '{$default_password}').\n" .
		"  Do you want to proceed? (y/n)"
	);

	if (strcasecmp(chop(fgets($in)), "y") == 0)
	{
		$config['system']['password'] = $default_password;
		write_config();
		system_update_shell_password();
		system_update_httpd_settings();

		fwrite($out,
			"The system for has been reset.\n" .
			"Remember to set the password to something else than\n" .
			"the default as soon as you have logged into the web interface.\n" .
			"  Press RETURN to continue."
		);
		fgets($in);
	}
?>
