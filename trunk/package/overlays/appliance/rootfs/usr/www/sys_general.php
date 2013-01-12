<?php
/*
  $Id$
part of BoneOS build platform (http://www.teebx.com/)
Copyright(C) 2010 - 2012 Giovanni Vallesi (http://www.teebx.com).
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
require('guiconfig.inc');
require_once('tzdata.lib.php');
require('fileutils.lib.php');
require('include/ui.langdef.inc');
require('libs-php/cfgform.class.php');
// define some constants needed in fbegin.inc to conditionally load some js utilities and style sheets
define('INCLUDE_FORMSTYLE', true);
// page title
$pgtitle = array(gettext('System'), gettext('General Setup'));
// instantiate the config form object
$form = new cfgForm('sys_general.php', 'method=post|name=iform|id=iform');
// set session name
$sessionName = 'frmSysGenCfg';
// set pointers to the actual configuration variables
$cfgPtr['hostname'] = &$config['system']['hostname'];
$cfgPtr['domain'] = &$config['system']['domain'];
$cfgPtr['username'] = &$config['system']['username'];
$cfgPtr['password'] = &$config['system']['password'];
$cfgPtr['webuiport'] = &$config['system']['webgui']['port'];
$cfgPtr['language'] = &$config['system']['webgui']['language'];
$cfgPtr['timezone'] = &$config['system']['timezone'];
$cfgPtr['timeupdateinterval'] = &$config['system']['time-update-interval'];
$cfgPtr['timeservers'] = &$config['system']['timeservers'];
// this content is stored encoded as base64
//$cfgPtr['notes'] = &$config['system']['notes'];
//
if ($_POST)
{
	// get back the form object state and update it according to user input
	$form->wake($_SESSION[$sessionName]);
	// validate user input
	$form->validForm($form);
	// check no errors collected...
	$input_errors = $form->get_errQueue();
	//
	if (count($input_errors) == 0)
	{
		$changedFields = $form->getFields($cfgPtr, 'password|webuiport');
		if (in_array('password', $changedFields))
		{
			// set a persistent flag to notify that a system reboot is required
			touch($d_passworddirty_path);
			touch($d_sysrebootreqd_path);
		}
		$retval = 0;
		write_config();
		if (!file_exists($d_sysrebootreqd_path))
		{
			config_lock();
			// update system settings
			$retval = system_hostname_configure();
			$retval |= system_hosts_generate();
			$retval |= writeSysRegSettings($config);
			$retval |= system_cron_configure();
			// update application specific settings
			//
			config_unlock();
		}
		$savemsg = get_std_save_message($retval);
	}
}
else
{
	// prepare the configuration form
	$form->startFieldSet('fset_sysmanage', gettext('Management'));
		$form->startBlock('rw_username');
			$form->setLabel(null, gettext('Username'), 'username', 'class=labelcol');
			$form->startBlock('rw_username', 'right');
				$form->setField('username', 'text', 'size=20|maxlength=64|class=required');
				$form->setDefault('username', 'admin');
				$form->setInputText('username', $cfgPtr['username']);
				$form->setBlockHint('hint-username',
					gettext('If you want to change the username for accessing the webGUI, enter it here.')
				);
			//
		$form->exitBlock();
		$form->startBlock('rw_passwd');
			$form->setLabel(null, gettext('Password'), 'password', 'class=labelcol');
			$form->startBlock('rw_passwd', 'right');
				$empyPass = null;
				$form->setField('password', 'password', 'size=20|maxlength=64|class=required');
				$form->setInputText('password', $empyPass);
				$form->setLabel(null, gettext('confirmation') . ':', 'password-chk');
				$form->setField('password-chk', 'password', 'size=20|maxlength=64|class=required');
				$form->setInputText('password-chk', $empyPass);
				$form->setBlockHint('hint-passwd',
					gettext('If you want to change the password for accessing the web UI, enter it here twice.')
				);
			//
		$form->exitBlock();
		$form->startBlock('rw_httpport');
			$form->setLabel(null, gettext('webGUI port'), 'webuiport', 'class=labelcol');
			$form->startBlock('rw_httpport', 'right');
				$form->setField('webuiport', 'text', 'size=5|maxlength=5');
				$form->setDefault('webuiport', '80');
				$form->setInputText('webuiport', $cfgPtr['webuiport']);
				$form->setBlockHint('hint-httpport',
					gettext('Enter a custom port number for the webGUI above if you want to override the default (80 for HTTP).')
				);
			//
		$form->exitBlock();
	$form->exitFieldSet();
	$form->startFieldSet('fset_sethost', gettext('Hostname'));
		$form->startBlock('rw_hostname');
			$form->setLabel(null, gettext('Hostname'), 'hostname', 'class=labelcol');
			$form->startBlock('rw_hostname', 'right');
				$form->setField('hostname', 'text', 'size=24|maxlength=64|class=required');
				$form->setInputText('hostname', $cfgPtr['hostname']);
				$form->setLabel(null, '.', 'domain');
				$form->setField('domain', 'text', 'size=32|maxlength=64|class=required');
				$form->setInputText('domain', $cfgPtr['domain']);
				$form->setBlockHint('hint-host-domain',
					gettext('Hostname of that system.') . '<br>' .
					gettext('e.g.') . '&nbsp;<em>' . gettext('pbx . mydomain.com') . '</em>'
				);
		$form->exitBlock();
	$form->exitFieldSet();
	//
	$form->startFieldSet('fset_locales', gettext('Regional Settings'));
		$form->startBlock('rw_uilang');
			$form->setLabel(null, gettext('webGUI language'), 'language', 'class=labelcol');
			$form->startBlock('rw_uilang', 'right');
				$form->setField('language', 'select', 'name=language');
				$form->setDefault('language', 'en_US');
				// update the language definition list with translation status
				$translStatus = cfgFileRead('/usr/www/locale/locale_status.txt');
				if (is_array($translStatus))
				{
					foreach (array_keys($ui_language) as $langKey)
					{
						if (isset($translStatus[$langKey]))
						{
							$ui_language[$langKey] = $ui_language[$langKey] . " ({$translStatus[$langKey]}%)";
						}
					}
				}
				unset($translStatus);
				//
				$form->setSelectOptFill('language', $ui_language);
				$form->setFieldOptionsState('language', $cfgPtr['language']);
				$form->setBlockHint('hint-lang',
					gettext('Select in which language you want the webGUI to be displayed.'));
			//
		$form->exitBlock();
		$form->startBlock('rw_tz');
			$form->setLabel(null, gettext('Time zone'), 'timezone', 'class=labelcol');
			$form->startBlock('rw_tz', 'right');
				$form->setField('timezone', 'select', 'name=timezone');
				$form->setDefault('timezone', '99000');
				// fill the timezone select field group
				$tzData = getTzData(false);
				foreach (array_keys($tzData) as $grpKey)
				{
					$arrTz = array();
					$grpName = $tzData[$grpKey]['group'];
					foreach (array_keys($tzData[$grpKey]) as $tzKey)
					{
						if (is_array($tzData[$grpKey][$tzKey]))
						{
							$zoneId = $grpKey . $tzKey;
							$zoneName = $tzData[$grpKey][$tzKey][0];
							$arrTz[$zoneId] = $zoneName;
						}
					}
					$form->setSelectOptFill('timezone', $arrTz, $grpName);
				}
				unset ($tzData, $arrTz, $grpKey, $grpName, $tzKey, $zoneId, $zoneName);
				//
				$form->setFieldOptionsState('timezone', $cfgPtr['timezone']);
				$form->setBlockHint('hint-tz',
					gettext('Select the location closest to you.'));
			//
		$form->exitBlock();
	$form->exitFieldSet();
	//
	$form->startFieldSet('fset_timesync', gettext('Time Synchronization'));
		$form->startBlock('rw_sync');
			$form->setLabel(null, gettext('Update Interval'), 'timeupdateinterval', 'class=labelcol');
			$form->startBlock('rw_sync', 'right');
				$form->setField('timeupdateinterval', 'select', 'name=timeupdateinterval');
				$form->setDefault('timeupdateinterval', $defaults['system']['timeupdateinterval']);
				//
				$syncOptions = array(
					'disable' => gettext('disable time synchronization'),
					'10-minutes' => gettext('every 10 minutes'),
					'30-minutes' => gettext('every 30 minutes'),
					'1-hour' => gettext('every hour'),
					'4-hours' => gettext('every 4 hours'),
					'12-hours' => gettext('every 12 hours'),
					'1-day' => gettext('every day')
				);
				$form->setSelectOptFill('timeupdateinterval', $syncOptions);
				$form->setFieldOptionsState('timeupdateinterval', $cfgPtr['timeupdateinterval']);
				$form->setBlockHint('hint-timeupdateinterval',
					gettext('Select how often the time should be synchronized.'));
			//
		$form->exitBlock();
		$form->startBlock('rw_timeservers');
			$form->setLabel(null, gettext('NTP Server'), 'timeservers', 'class=labelcol');
			$form->startBlock('rw_timeservers', 'right');
				$form->setField('timeservers', 'text', 'size=20|maxlength=64');
				$form->setDefault('timeservers', $defaults['system']['timeservers']);
				$form->setInputText('timeservers', $cfgPtr['timeservers']);
				$form->setBlockHint('hint-timeservers',
					gettext('Enter a server to synchronize with.')
				);
			//
		$form->exitBlock();
	$form->exitFieldSet();
	//
	$form->setField('submit', 'submit', 'value=' . gettext('Save'));
	// set required fields
	$form->setRequired('username', gettext('Username'));
	$form->setRequired('hostname', gettext('Hostname'));
	$form->setRequired('domain', gettext('domain'));
	// set validation constraints
	$validationParams = array(
		'errorMsg' => gettext('The passwords do not match.'),
		'validSet' => array(
			'fld' => 'password-chk', 'cond' => '==')
		);
	$form->setValidationFunc('password', 'validIf', $validationParams);
	// hold form data in a session variable
	$_SESSION[$sessionName] = $form->serialize();
}
// render the page content
require('fbegin.inc');
$form->renderForm();
include('fend.inc');
?>
