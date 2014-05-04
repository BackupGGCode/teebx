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

// this will replace pbx.inc but at this time mostly is a bridge to it

require_once '/etc/inc/pbx.inc';

function startApplianceBundle(&$config)
{
	if (isset($config['cfgstatic']) && $config['cfgstatic'] != '')
	{
		// extract static configuration, usually from /cf mounted partition

	}
	else
	{
		/* configure platform specific applications*/
		configAppliance($config);
	}

	echo '   |- executing Asterisk...', PHP_EOL;;
	startIpbx();
	echo 'done', PHP_EOL;
}

function loadAppKernelModules(&$config)
{
}

function configAppliance(&$config)
{
	if (isset($config['interfaces']['tdm']['dahdi']['enable']) && $config['interfaces']['tdm']['dahdi']['enable'] == 1)
	{
		echo ' - Auto configuring DAHDI ports... ';
		dahdi_autoconfigure_ports();
		echo 'done', PHP_EOL;

		echo ' - Auto configuring Analog phones... ';
		analog_autoconfigure_phones();
		echo 'done', PHP_EOL;

		echo ' - Auto configuring ISDN phones... ';
		isdn_autoconfigure_phones();
		echo 'done', PHP_EOL;
	}

	/* start up Asterisk */
	echo ' - Configuring Asterisk... ', PHP_EOL;
	pbx_configure();
	echo 'done', PHP_EOL;
}

function startIpbx()
{
	exec('/usr/sbin/asterisk', $discard, $result);
	return $result;
}

function stopIpbx()
{
	exec('/usr/sbin/asterisk -rx \'core stop now\' > /dev/null 2>&1', $discard, $result);
	sleep(1);
	return $result;
}

function configAsterisk()
{
	asterisk_conf_generate();
}

function configCdr()
{

}

function reloadCdr()
{

}

function pbxGetStatusChannels()
{
	$result = array('exitstatus' => 1);
	exec('/usr/sbin/asterisk -rx \'core show channels\' > 2>&1', $out, $exitstatus);
	if ($exitstatus == 0)
	{
		foreach (array_keys($out) as $kRow)
		{
			$row = explode(' ', $out[$kRow]);
			$result[$row[2]] = $row[0];
		}
	}
}

function pbxGetActiveCalls()
{
	$result = 0;
	$status = pbxGetStatusChannels();
	if ($status['exitstatus'] == 0 && isset($status['calls']))
	{
		$result = $status['calls'];
	}
	return $result;
	/* quick testing only stub
	$out = file_get_contents('/tmp/calls');
	return (int) $out;
	*/
}

function pbxPreventCalls()
{
	// stub function waiting for proper dialplan implementation
	return 0;
}

function getStopApplianceOptions()
{
	$stopOptions = array();

	$stopOptions['__app_stop_option_preventcalls'] = array(
		'ftype' => 'checkbox',
		'label' => _('Prevent pbx to accept new calls.'),
		'feedback_wait' => _('Locking pbx application...'),
		'feedback_done' => _('Pbx application locked, no new calls will be possible between now and next start.'),
		'mode' => 'exec',
		'function' => 'pbxPreventCalls',
		'expect' => 0
	);

	$stopOptions['__app_stop_option_nocalls'] = array(
		'ftype' => 'checkbox',
		'label' => _('Check for active calls and wait until the pbx will be free.'),
		'feedback_wait' => _('active calls, updating every 5 seconds. Please wait...'),
		'feedback_done' => _('No pending calls.'),
		'mode' => 'poll',
		'function' => 'pbxGetActiveCalls',
		'expect' => 0
	);

	return $stopOptions;
}

function stopAppliance()
{
	$result = stopIpbx();
	// ...
	return $result;
}

