 <?php
/*
  $Id$
part of BoneOS build platform (http://www.teebx.com/)
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

- BoneOS source code is available via svn at [http://svn.code.sf.net/p/boneos/code/].
- look at TeeBX website [http://www.teebx.com] to get details about license.
*/

require_once('config.inc');
require_once('util.inc');
require_once('blockdevices.lib.php');

function setupStorageDevices(&$conf)
{
	if (!is_array($conf['system']['storage']))
		return 0;
	//
	$setup = mountStorageDevices($conf);
	// check that service configuration exists
	if (!is_array($conf['system']['storage']['services']))
		return 0;
	//
	// now loop thru $conf['system']['storage']['services'] array to initialize services
	foreach (array_keys($conf['system']['storage']['services']) as $service)
	{
		// first check some required symbols
		if (!is_array($conf['system']['storage']['services'][$service]))
			continue;
		//
		if (!isset($conf['system']['storage']['services'][$service]['active']))
			continue;
		// skip if disabled...
		if ($conf['system']['storage']['services'][$service]['active'] != 1)
			continue;
		// ...same if no mount point was set
		if (!isset($conf['system']['storage']['services'][$service]['fsmount']))
			continue;
		// check that the mount point this service depends on is enabled, else skip it
		$fsMount = $conf['system']['storage']['services'][$service]['fsmount'];
		if ($conf['system']['storage']['fsmounts'][$fsMount]['active'] != 1)
		{
			continue;
		}
		$fCall = 'initsvc' . ucfirst($service);
		if (is_callable($fCall))
		{
			/*
				check that the required directory exists, else make it.
				This also to avoid a system call execept the very first time.
			*/
			$svcPath = "{$conf['system']['storage']['mountroot']}/$fsMount/$service";
			if (!is_dir($svcPath))
			{
				if (!mkdir($svcPath, 0766, true))
				{
					$conf['system']['storage']['services'][$service]['active'] = -1;
					$setup['cfgchanged'] = 1;
					msgToSyslog("making directory for service ($service) failed.");
				}
			}
			$svcResult = call_user_func($fCall, $svcPath);
			if ($svcResult === false)
			{
				// disable that service
				$conf['system']['storage']['services'][$service]['active'] = -2;
				$setup['cfgchanged'] = 1;
				msgToSyslog("handler for service ($service) failed during execution.");
			}
		}
		else
		{
			$conf['system']['storage']['services'][$service]['active'] = -3;
			$setup['cfgchanged'] = 1;
			msgToSyslog("initialization for service ($service) failed because no handler available.");
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

function initsvcAstmedia($basePath, $arrOpt = null)
{
	if (!is_dir("$basePath/moh"))
	{
		mkdir("$basePath/moh/custom", 0766, true);
		exec("cp -Rp /offload/asterisk/moh/* $basePath/moh/");
	}

	if (!is_dir("$basePath/sounds"))
	{
		mkdir("$basePath/sounds/custom", 0766, true);
		exec("cp -Rp /offload/asterisk/sounds/* $basePath/sounds/");
	}

	return true;
}

function initsvcAstdb($basePath, $arrOpt = null)
{
	exec("cp -p /etc/asterisk/db/astdb $basePath/");
	return true;
}

function initsvcAstcdr($basePath, $arrOpt = null)
{
}

function initsvcAstlogs($basePath, $arrOpt = null)
{
}

function initsvcFax($basePath, $arrOpt = null)
{
}

function initsvcVoicemail($basePath, $arrOpt = null)
{
}

function initsvcSystemlogs($arrStorage, $service)
{
}

?>
