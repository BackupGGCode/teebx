<?php
/*
  $Id$
part of TeeBX(R) VoIP communication platform.
Copyright(C) 2010 - 2013 Giovanni Vallesi (http://www.teebx.com).
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

- TeeBX Source code is available via svn at [http://code.google.com/p/teebx/source/checkout].
- look at TeeBX website [http://www.teebx.com] to get details about license.
*/

/**
 * Wrapper for echo, output a message to standard output or append to a global "buffer" variable ($msg_buf)
 *
 * Written to give feedback to user during package boot...
 * but the way package routine executed redirect to dev/null :-((
 * This is the reason of $do_echo parameter which defaults to FALSE
 *
 * @param mixed $msg            The message
 * @param bool $do_echo         Default to false, echo $msg when true
 */

define('LOG_FILE', '/tmp/php-debug.log');

function logPushMsg($msg, $immediate_write = true, $do_echo = FALSE)
{
	global $msg_buf;
	if(!isset($msg_buf)) { $msg_buf = ''; }
	if(is_array($msg))
	{
		$msg = print_r($msg, TRUE);
	}
	if(defined('PKG_SYSBOOT') & $do_echo)
	{
		echo $msg;
	}
	$msg_buf .= "$msg\n---[MSG END]---\n\n";
	if ($immediate_write)
		logWrite();
}

/**
 * Dump the message buffer (global $msg_buf) to log file (constant PKG_LOGFILE)
 *
 * @return integer  0 if no error, else return 1
 */
function logWrite()
{
	$result = 0;
	global $msg_buf;
	if($msg_buf <> '')
	{
		if(!$log_fhandle = fopen(LOG_FILE, 'a'))
		{
			$result = 1;
		}
		else
		{
			$msg_buf = '[Message log dump at: ' . date("F j, Y, G:i") . "]\n$msg_buf";
			if(fwrite($log_fhandle, $msg_buf) === FALSE)
			{
				$result = 1;
			}
			$msg_buf = '';
		}
		fclose($log_fhandle);
	}
	return $result;
}

?>
