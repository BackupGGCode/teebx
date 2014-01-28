<?php
/*
  $Id$
part of BoneOS build platform (http://www.teebx.com/)
Copyright(C) 2010 - 2014 Giovanni Vallesi (http://www.teebx.com).
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

require_once('util.inc');
require_once('blockdevices.lib.php');
require_once('services.lib.php');

function getAvailServices()
{
	require('services.def.inc');

	foreach (array_keys($services) as $service)
	{
		if (!is_callable($services[$service]['handler']))
		{
			unset($services[$service]);
			continue;
		}
		foreach (array_keys($services[$service]['rules']) as $rule)
		{
			if (is_callable($services[$service]['rules'][$rule]))
			{
				continue;
			}
			// something misconfigured, break out, this service key will be unset
			unset($services[$service]);
			break;
		}
	}
	return $services;
}

function setupDoBefore(&$conf, $services, $filter = null, $coldStart = false)
{
	$job = array(
		'now' => array(),
		'reconf' => array(),
		'defer' => array()
	);

	if ($filter !== null && is_array($filter))
	{
		$services = array_intersect_key($services, array_flip($filter));
	}

	foreach (array_keys($services) as $service)
	{
		if ($coldStart && isset($services[$service]['ignorecoldstart']) && $services[$service]['ignorecoldstart'])
		{
			continue;
		}
		$app = $services[$service]['application'];
		// fill the list of function(s) to be called to reconfigure application(s)
		if (isset($services[$service]['rules']['reconf']))
		{
			if (!in_array($services[$service]['rules']['reconf'], $job['reconf']))
			{
				$job['reconf'][] = $services[$service]['rules']['reconf'];
			}
		}
		// fill the list of function(s) to be called to stop application(s) before changing configuration
		if (isset($services[$service]['rules']['stop']))
		{
			$job['now'][$app][$services[$service]['rules']['stop']] = 'h';
		}
		elseif (isset($services[$service]['rules']['reload']))
		{
			// skip the reload function if this app need is already set to be restarted
			if (!array_key_exists($app, $job['now']))
			{
				$job['defer'][$app][$services[$service]['rules']['reload']] = 'r';
			}
		}
		if (isset($services[$service]['rules']['start']))
		{
			$job['defer'][$app][$services[$service]['rules']['start']] = 's';
		}
	}
	// reduce $job['defer']
	$tmp = array();
	array_walk($job['defer'],
		function($row) use(&$tmp)
		{
			$tmp[] = key($row);
		},
		$tmp
	);
	$job['defer'] = $tmp;
	unset($tmp);

	// now run any function to stop application(s) as needed
	foreach (array_keys($job['now']) as $application)
	{
		foreach (array_keys($job['now'][$application]) as $fCall)
		{
			call_user_func($fCall, $conf);
		}
	}

	// clean up the job array to keep only the keys to be returned
	unset($job['now']);
	return $job;
}

function setupDoCall(&$conf, $fList)
{
	$result = true;
	foreach (array_keys($fList) as $fCall)
	{
		$result |= call_user_func($fList[$fCall], $conf);
	}
	return $result;
}

function setupStorageDevices(&$conf, $filter = null, $boot = false)
{
	// check that there are mountpoints defined
	if (!is_array($conf['system']['storage']) || !is_array($conf['system']['storage']['fsmounts']))
		return 0;
	//
	$setup = mountStorageDevices($conf);
	// check that service configuration exists
	if (!is_array($conf['system']['storage']['services']))
		return 0;
	//
	// get callable service functions
	$svcFuncs = getAvailServices();
	// get a copy of the storage config array
	$storageCfg = $conf['system']['storage']['services'];
	// reduce it according to $filter if set
	if ($filter !== null && is_array($filter))
	{
		$storageCfg = array_intersect_key($storageCfg, array_flip($filter));
	}
	// now loop thru $storageCfg array to initialize services
	foreach (array_keys($storageCfg) as $service)
	{
		// first check some required symbols
		if (!is_array($storageCfg[$service]))
			continue;
		//
		if (!isset($storageCfg[$service]['active']))
			continue;
		// skip if disabled...
		if ($storageCfg[$service]['active'] != 1)
			continue;
		// ...the same if no mount point was set
		if (!isset($storageCfg[$service]['fsmount']))
			continue;
		// check that the mount point this service depends on is enabled, else skip it
		$fsMount = $storageCfg[$service]['fsmount'];
		if ($conf['system']['storage']['fsmounts'][$fsMount]['active'] != 1)
		{
			continue;
		}
		// check that service has a callable function defined
		if (!isset($svcFuncs[$service]['handler']))
		{
			$conf['system']['storage']['services'][$service]['active'] = -3;
			$setup['cfgchanged'] = 1;
			msgToSyslog("initialization for service ($service) failed because no handler available.");
			continue;
		}
		/*
			check that the required directory exists, else make it.
			This also to avoid a system call except the very first time.
		*/
		$svcPath = "{$conf['system']['storage']['mountroot']}/$fsMount/{$svcFuncs[$service]['dirtree']}";
		if (!is_dir($svcPath))
		{
			if (!mkdir($svcPath, 0766, true))
			{
				$conf['system']['storage']['services'][$service]['active'] = -1;
				$setup['cfgchanged'] = 1;
				msgToSyslog("making directory for service ($service) failed.");
			}
		}
		$svcResult = call_user_func($svcFuncs[$service]['handler'], $svcPath);
		if ($svcResult === false)
		{
			// disable that service
			$conf['system']['storage']['services'][$service]['active'] = -2;
			$setup['cfgchanged'] = 1;
			msgToSyslog("handler for service ($service) failed during execution.");
		}
	}
	// check if configuration was changed due to some errors
	if ($setup['cfgchanged'] != 0)
	{
		// update configuration with current storage status
		write_config();
		// send this event to syslog
		msgToSyslog('something went wrong during storage initialization, please check status using the UI.', LOG_ERR);
	}
}
?>
