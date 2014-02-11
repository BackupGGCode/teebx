#!/usr/bin/php-cgi -f
<?php
/*
  $Id$
part of BoneOS build platform (http://www.teebx.com/)
Copyright(C) 2014 Giovanni Vallesi (http://www.teebx.com).
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

	require_once 'utils.lib.php';
	require_once '/etc/inc/appliancebone.lib.php';

	$in = fopen('php://stdin', 'r');
	$out = fopen('php://stdout', 'w');

	$msgProgress = 'Stopping running applications and Unmounting File Systems';
	fwrite($out, "\nPlease hit [r] to reboot, uppercase [S] to shutdown or any other key to exit,\nthen press RETURN. How do you want to proceed [r/S]?\n >");
	$opt = chop(fgets($in));
	if (strcmp($opt, 'r') === 0)
	{
		$msg = 'The system is rebooting now. Please wait.';
		$mode = 'reboot';
		fwrite($out, $msgProgress . '...' .PHP_EOL);
	}
	elseif(strcmp($opt, 'S') === 0)
	{
		$msg = 'The system is shutting down and will be powered off if your platform support it. Please wait.';
		$mode = 'poweroff';
		fwrite($out, $msgProgress . '...' .PHP_EOL);
	}
	else
	{
		exit;
	}
	doSystemStop($mode);
	fwrite($out, $msg);
	sleep(10);
?>
