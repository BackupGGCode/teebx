<?php
/*
  $Id: $
part of BoneOS build platform (http://www.teebx.com/)
Copyright(C) 2013 - 2014 Giovanni Vallesi (http://www.teebx.com).
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

$omit_nocacheheaders = true;

require 'guiconfig.inc';
require 'libs-php/cfgform.class.php';
require_once '/etc/inc/utils.lib.php';
require_once '/etc/inc/initsvc.storage.php';
require_once '/etc/inc/appliancebone.lib.php';
require_once '/etc/inc/appliance.lib.php';

// constants referenced in fbegin.inc
define('INCLUDE_FORMSTYLE', true);

$cfgPtr = &$config['system']['storage'];
$cfgAppliance = sysPreset::cfgAppliance();
$svcAvail = getAvailServices();

if (!empty($_POST) && isset($_POST['task']))
{
	unset($input_errors);

	$jobSet = array();
	$task = $_POST['task'];
	$baseFileName = "config-{$config['system']['hostname']}.{$config['system']['domain']}-" .
		date('YmdHis');

	if (isset($_POST['bck_system']))
	{
		$jobSet[] = array('files' => sysPreset::PATH_CF_CONF . "/config.xml",
			'job' => 'system');
	}
	if (isset($_POST['bck_app']) && is_array($_POST['bck_app']))
	{
		foreach (array_keys($_POST['bck_app']) as $appKey)
		{
			if (isset($cfgAppliance[$_POST['bck_app'][$appKey]]['config']))
			{
				$jobSet[] = array('files' => $cfgAppliance[$_POST['bck_app'][$appKey]]['config'],
					'app' => $_POST['bck_app'][$appKey]);
			}
		}
	}
	if (isset($_POST['bck_svc']) && is_array($_POST['bck_svc']))
	{
		foreach (array_keys($_POST['bck_svc']) as $jobKey)
		{
			if (isset($svcAvail[$_POST['bck_svc'][$jobKey]]['dirtree']))
			{
				$jobSet[] = array('files' => $cfgPtr['mountroot'] . '/' .
					$cfgPtr['services'][$_POST['bck_svc'][$jobKey]]['fsmount'] . '/' .
					$svcAvail[$_POST['bck_svc'][$jobKey]]['dirtree'],
					'svc' => $_POST['bck_svc'][$jobKey]);
			}
		}
	}

	if ($task === 'backup')
	{
		if (empty($jobSet))
		{
			exit;
		}

		config_lock();
		$archiverResult = backupPrepare($jobSet, $baseFileName);
		config_unlock();
		if ($archiverResult['retval'] !== false)
		{
			fileDownloadRequest($archiverResult['retval'], null, 4096);
			unlink($archiverResult['retval']);
			exit;
		}
		else
		{
			//handle error
			$input_errors[] = _('General Error.');
			$input_errors = array_merge($input_errors, $archiverResult);
		}
	}
	else if ($task === 'restore')
	{
		if (is_uploaded_file($_FILES['conffile']['tmp_name']))
		{
			if (config_install($_FILES['conffile']['tmp_name']) == 0)
			{
				system_reboot();
				$keepmsg = _('The configuration has been restored. The PBX is now rebooting.');
			}
			else
			{
				$errstr = _('The configuration could not be restored.');
				if ($xmlerr)
					$errstr .= " (" . sprintf(_("XML error: %s") . ")", $xmlerr);
				$input_errors[] = $errstr;
			}
		}
		else
		{
			$input_errors[] = _('The configuration could not be restored (file upload error).');
		}
	}
}

$form = new cfgForm('maint_backup.php', 'enctype=multipart/form-data|method=post|name=iform|id=iform');
// initialize form
$form->startWrapper('tab-1');
	// let user choose the task
	$form->startFieldset('fset_task', _('Task selection'));
		$form->startBlock('rw_task');
			$form->setLabel(null, _('Task'), 'bck_task', 'class=labelcol');
			$form->startBlock('rw_task', 'right');
				$form->setField('bck_task', 'select', 'name=task');
				$taskOpts['backup'] = _('Backup');
				$taskOpts['restore'] = _('Restore');
				$form->setSelectOptFill('bck_task', $taskOpts);
				$form->setFieldOptionsState('bck_task', 'backup');
			//
		$form->exitBlock();
		$form->startBlock('rw_file');
			$form->setLabel(null, _('Backup file'), 'restore_file', 'class=labelcol');
			$form->startBlock('rw_file', 'right');
				$form->startWrapper('loadfile');
					$form->setField('restore_file', 'file', 'name=restore_file');
					$form->setField('doload', 'button', 'class=startjob|disabled=disabled|value=' . _('Load'), false);
					$form->setBlockHint('hint-file',
						_('Select a backup file, then click the above Load button to check the file content.')
					);
				$form->exitWrapper();
			//
		$form->exitBlock();
	$form->exitFieldSet();
	$form->startFieldset('fset_what', _('Backup subsets'));
		// core system configuration
		$form->startBlock('rw_what');
			$form->setLabel(null, _('System'), 'bck_system', 'class=labelcol');
			$form->startBlock('rw_what', 'right');
				$form->setField('bck_system', 'checkbox', 'name=bck_system');
				$form->setCbItems('bck_system', 'system=' . _('System core configuration'), true);
				$form->setCbState('bck_system', 'system', 1);
			//
		$form->exitBlock();
		// appliance specific configuration sets
		foreach (array_keys($cfgAppliance) as $app)
		{
			if (isset($cfgAppliance[$app]['config']))
			{
				$fieldId = "bck_app_{$app}";
				$form->startBlock("rw_{$app}");
					$form->setLabel(null, $cfgAppliance[$app]['fld_label_bck'], null, 'class=labelcol');
					$form->startBlock("rw_{$app}", 'right');

					$form->setField($fieldId, 'checkbox', 'name=bck_app[]');
					$form->setCbItems($fieldId,
						"{$app}={$cfgAppliance[$app]['fld_desc_bck']}",
						true);
					$form->setCbState($fieldId, $app, 1);
				//
				$form->exitBlock();
			}
		}
		// additional storage sets
		foreach (array_keys($svcAvail) as $svc)
		{
			if (!isset($cfgPtr['services'][$svc]) || $cfgPtr['services'][$svc]['active'] != 1)
				continue;
			//
			$fieldId = "bck_svc_{$svc}";
			if ($svcAvail[$svc]['includedon_bck'] === 1)
			{
				$form->startBlock("rw_{$svc}");
					$form->setLabel(null, $svcAvail[$svc]['fld_label_bck'], null, 'class=labelcol');
					$form->startBlock("rw_{$svc}", 'right');

					$form->setField($fieldId, 'checkbox', 'name=bck_svc[]');
					$form->setCbItems($fieldId,
						"{$svc}={$svcAvail[$svc]['fld_desc_bck']}",
						true);
					$form->setCbState($fieldId, $svc, 0);
				//
				$form->exitBlock();
			}
		}
	//
	$form->exitFieldSet();
$form->exitWrapper();
$form->setField('submit', 'submit', 'value=' . _('Execute'));

include 'fbegin.inc';
// render form
$form->renderForm();

$msg = _('After you click &quot;Execute&quot;, all current calls will be dropped. You may also have to do one or more of the following steps before you can access your system again:') .
	'<ul><li>' . _('change the IP address of your computer') . '</li>' .
	'<li>' . _('access the web UI using the new IP address') . '</li></ul>';
showSaveWarning($msg);
include 'fend.inc';
?>
<script type="text/javascript">
jQuery(document).ready(function()
{
	jQuery('#bck_task').change(function()
	{
		if (jQuery('#bck_task').val() == 'restore')
		{
			jQuery('#restore_file').prop('disabled', false);
			jQuery('#restore_file').addClass('required');
			// these fields will be later enabled or not depending on the uploaded file contents
			jQuery('#fset_what').prop('disabled', true);
			jQuery('#submit').prop('disabled', true);
		}
		else
		{
			jQuery('#restore_file').prop('disabled', true);
			jQuery('#restore_file').removeClass('required');
			jQuery('#doload').prop('disabled', true);
			jQuery('#fset_what').prop('disabled', false);
			jQuery('#submit').prop('disabled', false);
		}
	}).change(); // sync initial state

	jQuery('#restore_file').change(function()
	{
		jQuery('#doload').prop('disabled', false);
	});

	jQuery('#doload').click(function()
	{
		// ajax upload the backup file then wait results to select proper restore options
		alert("Button clicked!");
	});
});
</script>
