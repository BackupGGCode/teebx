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
session_start();
require 'guiconfig.inc';
require_once('blockdevices.lib.php');
require_once('initsvc.storage.php');
require_once('libs-php/cfgform.class.php');
require_once('applianceboot.lib.php');
require_once('appliance.lib.php');

// initialize local variables
//
// set a pointer to the actual configuration variables
$cfgPtr = &$config['system']['storage'];
// form object session name
$cfgSvcName = 'frmSvcCfg';
// json return data
$data = array();
$data['retval'] = 1;
$data['errors'] = array();
// basics checks
if (!isset($_POST))
{
	$data['errors'][] = _('Missing parameters, process aborted!');
}

// instantiate the service binding configuration form object
$confForm = new cfgForm('sys_storage_edit.php', 'method=post|name=confform|id=confform');
// get back the form object state and update it according to user input
$confForm->wake($_SESSION[$cfgSvcName]);
// validate user input
$confForm->validForm();
$data['errors'] = $confForm->get_errQueue();
// something failed?
if (count($data['errors']) > 0)
{
	exit(json_encode($data));
}

$svcMount = $confForm->getFldValue('fsmount');
$cfgPtr['fsmounts'][$svcMount]['uuid'] = $confForm->getFldValue('uuid');
$cfgPtr['fsmounts'][$svcMount]['label'] = $confForm->getFldValue('label');
$cfgPtr['fsmounts'][$svcMount]['filesystem'] = $confForm->getFldValue('filesystem');
$cfgPtr['fsmounts'][$svcMount]['comment'] = $confForm->getFldValue('desc');
$cfgPtr['fsmounts'][$svcMount]['active'] = $confForm->getFldValue('active');

$svcAvail = getAvailServices();
$svcSetNow = null;

foreach (array_keys($svcAvail) as $svc)
{
	$svcActive = $confForm->getCbState($svc, 'yes', 'int');
	if ($svcActive === 1)
	{
		$cfgPtr['services'][$svc]['fsmount'] = $svcMount;
		$cfgPtr['services'][$svc]['active'] = 1;
		$svcSetNow[] = $svc;
	}
	else
	{
		if (isset($cfgPtr['services'][$svc]['fsmount']))
		{
			if ($cfgPtr['services'][$svc]['fsmount'] == $svcMount)
			{
				$cfgPtr['services'][$svc]['active'] = 0;
			}
		}
	}
}

$data['debug'] = $cfgPtr;

$data['retval'] = 0;
write_config();

// stop any application that depends on changing settings
$callQueue = setupDoBefore($config, $svcAvail, $svcSetNow);
// mount and initialize storage
setupStorageDevices($config, $svcSetNow);
// reconfigure applications due to changed settings
$data['results']['reconf'] = setupDoCall($config, $callQueue['reconf']);
// start applications previously halted
$data['results']['defer'] = setupDoCall($config, $callQueue['defer']);

// return json data
exit(json_encode($data));
?>
