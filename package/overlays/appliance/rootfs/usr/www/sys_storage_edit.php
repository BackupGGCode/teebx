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

session_start();
require 'guiconfig.inc';
require '/etc/inc/blockdevices.lib.php';
require 'libs-php/cfgform.class.php';
require_once 'libs-php/uiutils.lib.php';
require_once '/etc/inc/initsvc.storage.php';
require_once '/etc/inc/appliancebone.lib.php';
require_once '/etc/inc/appliance.lib.php';

// define some constants referenced in fbegin.inc
define('INCLUDE_FORMSTYLE', true);
// actual configuration reference and variables initialization
$cfgPtr = &$config['system']['storage'];
$partLabel = '';
$partComment = '';
// page title
$pgtitle = array(_('System'), _('Edit Storage Disk'));
$accessAllowed = false;
// sanity checks
if (isset($_SESSION['diskedit']['token']))
{
	if (isset($_POST))
	{
		if (isset($_POST['stk']))
		{
			if ($_POST['stk'] === $_SESSION['diskedit']['token'])
			{
				$modeCaption = _('Edit');
				if (($_POST['act'] === 'new') or ($_POST['act'] === 'use-spare'))
				{
					$modeCaption = _('Format & Initialize');
					$modeHint = '<div class="save_warning"><span>' .
						_('warning') . ':</span> ' .
						_('All information on this disk will be lost after clicking "Format"!') . '</div>';
						$mntDir = getNewMixedIndex('media', $cfgPtr['fsmounts']);
				}
				elseif ($_POST['act'] === 'edit')
				{
					if (isset($cfgPtr['fsmounts'][$_POST['fsmount']]))
					{
						$mntDir = $cfgPtr['fsmounts'][$_POST['fsmount']];
						if (isset($cfgPtr['fsmounts'][$_POST['fsmount']]['label']))
						{
							$partLabel = '';
						}
						if (isset($cfgPtr['fsmounts'][$_POST['fsmount']]['comment']))
						{
							$partComment = '';
						}
					}
				}
				$accessAllowed = true;
			}
		}
	}
}
// forbidden
if(!$accessAllowed)
{
	//redirect to an error page
	include('include/blankpagetpl.php');
	exit();
}

// instantiate the disk initialization form object
$initForm = new cfgForm('sys_storage_init.php', 'method=post|name=iform|id=iform');
// instantiate the service binding configuration form object
$confForm = new cfgForm('sys_services_set.php', 'method=post|name=confform|id=confform');
$cfgSvcName = 'frmSvcCfg';

// label for the device fieldset
$fsetLabel = $_POST['dev'];
$devModel = getDevModel($_POST['dev']);
if ($devModel !== false)
{
	$fsetLabel .= " ($devModel)";
}

// inizialization UI
$initForm->startFieldset('fset_init', _('Disk Device') . ": $fsetLabel");
	$initForm->startBlock('rw_label');
		$initForm->setLabel(null, _('Partition label'), 'label', 'class=labelcol');
		$initForm->startBlock('rw_label', 'right');
		$initForm->setField('part_label', 'text', 'size=11|maxlength=11|class=required', false, '');
		$initForm->setInputText('part_label', $partLabel);
		$initForm->setBlockHint('part_label',
			_('Enter the label for this disk partition.') . '<br>' . _('Only letters A-z, numbers and underscore allowed.'));
		$initForm->setValidationFunc('part_label', 'validMountPoint');
	$initForm->exitBlock();

	$initForm->startBlock('rw_part_desc');
		$initForm->setLabel(null, _('Comment'), 'part_desc', 'class=labelcol');
		$initForm->startBlock('rw_part_desc', 'right');
		$initForm->setField('part_desc', 'text', 'size=40|maxlength=40', false, '');
		$initForm->setInputText('part_desc', $partComment);
		$initForm->setBlockHint('part_desc', _('Enter a descriptive comment about this partition.'));
	$initForm->exitBlock();

	$newMount = "{$cfgPtr['mountroot']}/$mntDir";
	$initForm->startBlock('rw_mountpoint');
		$initForm->setLabel(null, _('Mount Point'), 'mountpoint', 'class=labelcol');
		$initForm->startBlock('rw_mountpoint', 'right');
		$initForm->setField('mountpoint', 'text', 'disabled=disabled|size=40|maxlength=40', false, '');
		$initForm->setInputText('mountpoint', $newMount);
		$initForm->setBlockHint('mountpoint', _('This partition will be mounted on the path set above.'));
	$initForm->exitBlock();

	$initForm->startBlock('rw_device');
		$initForm->setLabel(null, $modeCaption, 'allowinit', 'class=labelcol');
		$initForm->startBlock('rw_device', 'right');
			$initForm->startWrapper('warning', 'controls');
				$initForm->setBlockHint('warning-text', $modeHint);
			$initForm->exitWrapper();
			$initForm->startWrapper('progress', 'cloneable', 'class=starthidden');
				$initForm->setBlockHint('init-progress');
			$initForm->exitWrapper();
			$initForm->startWrapper('initstart');
				$initForm->setField('allowinit', 'checkbox', 'onclick=jQuery(\'#startdiskinit\').attr(\'disabled\', !jQuery(this).attr(\'checked\'));');
				$initForm->setCbItems('allowinit', 'yes=' . _('I know, thanks for the warning.'), true);
				$initForm->setCbState('allowinit', 'yes', 0);
				$initClick = 'onclick=callInit(\'' .
					escapeStr($_POST['dev']) .
					"', '{$_POST['act']}', '{$_POST['par']}', '{$_POST['start']}', '{$_POST['stk']}'
				)";
				$initForm->setField('startdiskinit', 'button', $initClick . '|class=startjob|disabled=disabled|value=' . _('Format'), false);
			$initForm->exitWrapper();
		//
	$initForm->exitBlock();
$initForm->exitFieldSet();

// service configuration UI
$confForm->startFieldset('fset_conf', _('General Settings'), 'disabled=disabled');
	$svcAvail = getAvailServices();
	foreach (array_keys($svcAvail) as $svc)
	{
		$confForm->startBlock("rw_{$svc}");
			$confForm->setLabel(null, $svcAvail[$svc]['fld_label_se'], null, 'class=labelcol');
			$confForm->startBlock("rw_{$svc}", 'right');

			$svcAttrib = null;
			$cbState = 0;
			if (isset($cfgPtr['services'][$svc]['fsmount'], $cfgPtr['services'][$svc]['active']))
			{
				if ($cfgPtr['services'][$svc]['active'] == 1)
				{
					if ($cfgPtr['services'][$svc]['fsmount'] != $mntDir)
					{
						$svcAttrib = 'disabled=disabled';
					}
					elseif ($cfgPtr['services'][$svc]['fsmount'] == $mntDir)
					{
						$cbState = 1;
					}
				}
			}

			$confForm->setField($svc, 'checkbox', $svcAttrib);
			$confForm->setCbItems($svc,
				"yes={$svcAvail[$svc]['fld_desc_se']}",
				true);
			$confForm->setCbState($svc, 'yes', $cbState);

		$confForm->exitBlock();
	}
$confForm->exitFieldSet();

$confForm->setField('label', 'hidden');
$confForm->setField('desc', 'hidden');
$confForm->setField('fsmount', 'hidden');
$confForm->setInputText('fsmount', $mntDir);

$confForm->setField('uuid', 'hidden');
// set fs type to a constant value until a choice will be available
$fsType = 'vfat';
$confForm->setField('filesystem', 'hidden');
$confForm->setInputText('filesystem', $fsType);
// set the active flag to a constant value until editing will be available
$devActive = '1';
$confForm->setField('active', 'hidden');
$confForm->setInputText('active', $devActive);

$confForm->setRequired('uuid', _('Disk unique identifier'));
$confForm->setRequired('fsmount', _('Service mount point'));

$confForm->startWrapper('saveservices');
$saveClick = "onclick=callApply('#confform')";
$confForm->setField('savecfg', 'submit', $saveClick . '|class=startjob|disabled=disabled|value=' . _('Save'), false);
$confForm->exitWrapper();
// hold form data in a session variable
$_SESSION[$cfgSvcName] = $confForm->serialize();

//

// variables for messages populated later from js code
$msgStartPartion = escapeStr(_('Creating new partition') . ' ' .
	$_POST['par'] .' ' .
	_('on') . ' ' .
	$_POST['dev'] . '... '
);
$msgStartFormat = escapeStr(_('Formatting partition') . ' ' .
	$_POST['par'] . ' ' .
	_('on') . ' ' .
	$_POST['dev'] . '... '
);
$msgAskReboot = escapeStr(_('Reboot required'));
$msgDone = escapeStr(_('done.'));
// render main layout
require('fbegin.inc');
$initForm->renderForm();
$confForm->renderForm();
// end layout
require('fend.inc');
?>
<script type="text/javascript">
function callInit(dev, act, par, start, stk)
{
	var uri = '/sys_storage_init.php';
	var wait = '<div id="waiting"><img alt="" src="img/ajax_busy_round.gif"></div>';
	var label = jQuery('#part_label').val();
	var params = 'dev=' + dev +
		'&mode=' + act +
		'&part=' + par +
		'&start=' + start +
		'&label=' + label +
		'&stk=' + stk;
	// any error?
	if (label.length == 0)
	{
		alert('Missing partition label!');
		return false;
	}
	if (jQuery('span.cli-error').length > 0)
	{
		alert('Errors!');
		return false;
	}
	// disable the Submit button and associated check button
	jQuery('#initstart :input').attr('disabled', true);
	// show the progress container
	jQuery('#progress').show();
	//
	jQuery('#init-progress').html('<div id="mpart"><?php echo $msgStartPartion; ?></div>');
	jQuery('#init-progress').append(wait);
	// #1 - send an ajax request to partition disk
	jQuery.ajax({
		type: 'POST',
		url: uri,
		async: false,
		cache: false,
		data: params + '&task=partinit',
		dataType: 'json',
		success: function(data){
			jQuery('#waiting').remove();
			if (data.retval == 0)
			{
				jQuery('#mpart').append('<?php echo $msgDone; ?>');
				jQuery('#init-progress').append('<div id="mformat"><?php echo $msgStartFormat; ?></div>');
				// #2 - send an ajax request to format partition
				jQuery('#init-progress').append(wait);
				jQuery.ajax({
					type: 'POST',
					url: uri,
					cache: false,
					async: false,
					data: params + '&task=partformat',
					dataType: 'json',
					success: function(data){
						jQuery('#waiting').remove();
						if (data.retval == 0)
						{
							if (data.uuid != false)
							{
								jQuery('#mformat').append('<?php echo $msgDone; ?>');
								jQuery('#uuid').val(data.uuid);
								jQuery('#fset_conf').prop('disabled', false);
								jQuery('#savecfg').prop('disabled', false);
								// copy some values from the initialization form
								jQuery('#label').val(jQuery('#part_label').val());
								jQuery('#desc').val(jQuery('#part_desc').val());
							}
							else
							{
								// something went wrong
								jQuery('#init-progress').html('<div>Err: Unable to retrive the new partition unique identifier.</div>');
							}
						}
					},
					failure: function(data){
						jQuery('#init-progress').html('<div>Err: ' + data.retval + '</div>');
					}
				});
			}
			else if (data.retval == 2)
			{
				jQuery('#mpart').append('<?php echo $msgDone; ?>');
				jQuery('#init-progress').append('<div id="mreboot"><?php echo $msgAskReboot; ?></div>');
			}
			else
			{
				jQuery('#init-progress').append('<div>Failure, return value: ' + data.retval + '</div>');
			}
		},
		failure: function(data){
			jQuery('#init-progress').html('<div>Err: ' + data.retval + '</div>');
		}
	});
	return false;
}

function callApply(formIdentifier)
{
	var frm = jQuery(formIdentifier);
	var submitBtn = frm.find(':submit');
	frm.submit(function(event) {
		event.preventDefault();
		submitBtn.attr('disabled', true);
		jQuery.ajax({
			type: frm.attr('method'),
			url: frm.attr('action'),
			async: false,
			dataType: 'json',
			data: frm.serialize(),
			success: function(data){
				alert('ok');
				// now reconfigure/restart any application which depends on changed storage settings
				jQuery('#init-progress').html('<div>Result:<br><pre>' + data.retval + '<pre></div>');
			},
			failure: function(data){
				jQuery('#init-progress').html('<div>Err:<br><pre>' + data.retval + '<pre></div>');
			}
		});
	});
}

// client side field validation

jQuery('#part_label').keyup(function()
{
	jQuery('span.cli-error').remove();
	var inputVal = jQuery(this).val();
	var characterReg = /^\s*[a-zA-Z0-9\_]+\s*$/;
	if(!characterReg.test(inputVal))
	{
		if (inputVal.length > 0)
		{
			jQuery(this).after('<span class="cli-error cli-errormsg">No special characters allowed.</span>');
		}
		else
		{
			jQuery(this).after('<span class="cli-error cli-errormsg">This field cannot be left empty!</span>');
		}
	}
});

</script>
