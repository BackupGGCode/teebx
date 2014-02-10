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

require_once 'util.inc';
require_once 'services.lib.php';

function configSyslog(&$arrCfg)
{
	$logPath = null;
	// safe default command line
	$args = '-S -C512';

	if (isset($arrCfg['system']['syslog']))
	{
		// configure local syslog mode
		if (isset($arrCfg['system']['syslog']['mode']))
		{
			if ($arrCfg['system']['syslog']['mode'] == 'membuffer')
			{
				if (isset($arrCfg['system']['syslog']['buffer']) && !empty($arrCfg['system']['syslog']['buffer']))
				{
					$args = "-S -C{$arrCfg['system']['syslog']['buffer']}";
				}
			}
			elseif ($arrCfg['system']['syslog']['mode'] == 'disk' && ($logPath = getSvcState($arrCfg, 'systemlog')) ==! false)
			{
				$args = "-S -s 1024 -b 5 -O $logPath/messages";
			}
		}
		// configure remote logging
		if (isset($arrCfg['system']['syslog']['remotehost']) && !empty($arrCfg['system']['syslog']['remotehost']))
		{
			$args .= " -R {$arrCfg['system']['syslog']['remotehost']}";
			if (isset($arrCfg['system']['syslog']['remoteport']) && !empty($arrCfg['system']['syslog']['remoteport']))
			{
				$args .= ":{$arrCfg['system']['syslog']['remoteport']}";
			}
			// anyway... keep local logging
			$args .= ' -L';
		}
	}

	$saveOld = false;
	$oldArgs = getProcessCmdline('syslogd');
	if ($oldArgs !== false)
	{
		if ($oldArgs === $args)
		{
			// nothing to do
			return false;
		}
		if (strpos($oldArgs, '-C') !== false)
		{
			$saveOld = true;
		}
	}

	return array('args' => $args, 'saveOld' => $saveOld, 'logPath' => $logPath);
}

function stopSyslog($saveOld = false)
{
	if ($saveOld)
	{
		// copy current memory log buffer to a temporary location
		exec("/sbin/logread > /tmp/messages.old", $discard, $result);
	}
	$result = sigkillbyname('syslogd', 'TERM');
	return $result;
}

function startSyslog($args, $logPath = null)
{
	// if saved buffer file exists and persistent storage is available
	if ($logPath != null && is_file('/tmp/messages.old'))
	{
		rename('/tmp/messages.old', "{$logPath}/messages.old");
	}
	exec("/sbin/syslogd $args", $discard, $result);
	return $result;
}

function restartSyslog(&$config)
{
	$params = configSyslog($config);
	if ($params === false)
	{
		return 0;
	}

	$return = stopSyslog($params['saveOld']);
	usleep(200000);
	$return |= startSyslog($params['args'], $params['logPath']);
	return $return;
}

// functions to be used in admin storage configuration scripts

function sessionQueue($key, $data)
{
	if(session_id() == '')
	{
		session_start();
	}

	$_SESSION[$key] = $data;
}

function storageupd_stopSyslog()
{
	$argCount = func_num_args();
	if (($argCount < 1))
	{
		return false;
	}
	// expect the system configuration array as first argument
	$arrCfg = func_get_arg(0);
	if (!is_array($arrCfg))
	{
		return false;
	}

	$params = configSyslog($arrCfg);
	sessionQueue('cfgqueue_syslog', array('params' => $params));
	if ($params !== false)
	{
		stopSyslog($params['saveOld']);
	}
}

function storageupd_startSyslog()
{
	if (session_id() == '' || $_SESSION['cfgqueue_syslog']['params'] === false)
	{
		// session lost or syslog was not halted
		return;
	}

	startSyslog($_SESSION['cfgqueue_syslog']['params']['args'],
		$_SESSION['cfgqueue_syslog']['params']['logPath']
	);
	unset($_SESSION['cfgqueue_syslog']);
}