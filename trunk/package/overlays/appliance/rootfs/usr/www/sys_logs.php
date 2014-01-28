<?php
/*
  $Id$
part of TeeBX(R) VoIP communication platform.
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

- TeeBX Source code is available via svn at [http://code.google.com/p/teebx/source/checkout].
- look at TeeBX website [http://www.teebx.com] to get details about license.
*/

session_start();
require 'guiconfig.inc';
require 'applianceboot.lib.php';
require 'libs-php/cfgform.class.php';

define('INCLUDE_FORMSTYLE', true);
// page title
$pgtitle = array(_('System'), _('Logs'));
$form = new cfgForm('sys_logs.php', 'method=post|name=iform|id=iform');
// set a pointer to the actual configuration variables
$cfgPtr = &$config['system']['syslog'];
// set session name
$sessionName = 'frmSyslogCfg';

if ($_POST)
{
	$form->wake($_SESSION[$sessionName]);
	$form->validForm();
	$input_errors = $form->get_errQueue();
	if (count($input_errors) == 0)
	{
		$cfgPtr['syslogmode'] = $form->getFieldSelectedOpts('syslogmode');
		$cfgPtr['buffer'] = $form->getFieldSelectedOpts('buffer');
		$host = $form->getTextFld('remotehost');
		unset($cfgPtr['remotehost'], $cfgPtr['remoteport']);
		if ($host != '')
		{
			$cfgPtr['remotehost'] = $form->getTextFld('remotehost');
			$cfgPtr['remoteport'] = $form->getTextFld('remoteport');
		}
		// save configuration
		$retval = 0;
		write_config();
		if (!file_exists($d_sysrebootreqd_path))
		{
			config_lock();
			$retval |= restartSyslog($config);
			config_unlock();
		}
		$savemsg = get_std_save_message($retval);
	}
}
else
{
	// prepare the configuration form
	$form->startFieldSet('fset_local', _('Local log'));
	if (($logPath = getSvcState($config, 'systemlog')) ==! false)
	{
		$form->startBlock('rw_localmode');
		$form->setLabel(null, _('Mode'), 'syslogmode', 'class=labelcol');
		$form->startBlock('rw_localmode', 'right');

			$form->setField('syslogmode', 'select', 'name=syslogmode');
			//
			$modeOptions = array(
				'membuffer' => _('Shared memory circular buffer'),
				'disk' => _('Disk') . " ({$logPath}/messages)"
			);
			$form->setSelectOptFill('syslogmode', $modeOptions);
			$form->setFieldOptionsState('syslogmode', $cfgPtr['syslogmode'], 'membuffer');
			$form->setBlockHint('hint-syslogmode',
				_('Choose where log application writes the log messages it receives.') .
				'<br>' .
				_('Messages written to shared memory will not survive across reboots.')
				);
			//
		$form->exitBlock();
	}
		$form->startBlock('rw_buffer');
		$form->setLabel(null, _('Buffer size'), 'buffer', 'class=labelcol');
		$form->startBlock('rw_buffer', 'right');
			$form->setField('buffer', 'select', 'name=buffer');
			//
			$modeOptions = array(
				'16' => '16 KB',
				'32' => '32 KB',
				'64' => '64 KB',
				'128' => '128 KB',
				'256' => '256 KB',
				'512' => '512 KB',
				'1024' => '1024 KB',
				'2048' => '2048 KB',
				'4096' => '4096 KB'
			);
			$form->setSelectOptFill('buffer', $modeOptions);
			$form->setFieldOptionsState('buffer', $cfgPtr['buffer'], '512');
			//
		$form->exitBlock();
	$form->exitFieldSet();
	//
	$form->startFieldSet('fset_remote', _('Remote log'));
		$form->startBlock('rw_remote');
			$form->setLabel(null, _('Syslog server'), null, 'class=labelcol');
			$form->startBlock('rw_remote', 'right');
				$form->setLabel(null, _('IP address'), 'remotehost');
				$form->setField('remotehost', 'text', "size=16|maxlength=15|class=required");
				$form->setInputText('remotehost', $cfgPtr['remotehost']);
				$form->setLabel(null, _('Port'), 'remoteport');
				$form->setField('remoteport', 'text', "size=6|maxlength=5");
				$form->setDefault('remoteport', 514);
				$form->setInputText('remoteport', $cfgPtr['remoteport']);
				$form->setBlockHint('hint-remote',
					_('Log messages will also be sent to the host address/port above if set.') .
					'<br>' .
					_('Local logs will still be updated regardless of this setting.')
					);
		$form->exitBlock();
	$form->exitFieldSet();
	$form->setField('submit', 'submit', 'value=' . _('Save'));
	// set validation constraints
	$hostValidArgs = array(
		'errorMsg' => _('Please enter a valid ip address for') . _('Syslog server'),
		'except' => 'empty'
		);
	$form->setValidationFunc('remotehost', 'validIpAddr', $hostValidArgs);
	$form->setValidationFunc('remoteport', 'validPort', array('unprivileged' => false));
	// hold form data in a session variable
	$_SESSION[$sessionName] = $form->serialize();
}
// render the page content
require('fbegin.inc');
$form->renderForm();
require('fend.inc');
?>
