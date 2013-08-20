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
require('guiconfig.inc');
require('blockdevices.lib.php');
require('libs-php/cfgform.class.php');
require_once('libs-php/utils.lib.php');
// define some constants referenced in fbegin.inc
define('INCLUDE_FORMSTYLE', true);
// page title
$pgtitle = array(gettext('System'), gettext('Edit Storage Disk'));
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
				$modeCaption = gettext('Edit');
				if (($_POST['act'] === 'new') or ($_POST['act'] === 'use-spare'))
				{
					$modeCaption = gettext('Format & Initialize');
					$modeHint = '<div class="save_warning"><span>' .
						gettext('warning') . ':</span> ' .
						gettext('All information on this disk will be lost after clicking "Format"!') . '</div>';
				}
				elseif ($_POST['act'] === 'edit')
				{
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
	define('REDIRECT_REQ', "http://{$_SERVER['HTTP_HOST']}/");
	define('REDIRECT_DLY', 3000);
	define('CONTENT_TOP', '<a href="' . REDIRECT_REQ . '">' .
		gettext('<b>Direct access not allowed!<b><br>') .
		gettext('Click here to') .' ' .
		gettext('access the web UI.') .
		'</a>');
	include('include/blankpagetpl.php');
	exit();
}
// instantiate the disk initialization form object
$initForm = new cfgForm('sys_storage_edit.php', 'method=post|name=iform|id=iform');
// inizialization UI
$initForm->startFieldset('fset_init', gettext('Disk Device') . ': ' . $_POST['dev']);
	$initForm->startBlock('rw_label');
		$initForm->setLabel(null, gettext('Disk label'), 'label', 'class=labelcol');
		$initForm->startBlock('rw_label', 'right');
		$initForm->setField('disk_label', 'text', 'size=11|maxlength=11|class=required', false, '');
		$initForm->setInputText('disk_label', $cfgPtr['boba']);
		$initForm->setBlockHint('disk_label',
			gettext('Enter the label for this disk partition.') . '<br>' . gettext('Only letters A-z, numbers, dash and underscore allowed.'));
		$initForm->setValidationFunc('disk_label', 'validMountPoint');
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
				$initForm->setCbItems('allowinit', 'yes=' . gettext('I know, thanks for the warning.'), true);
				$initForm->setCbState('allowinit', false);
				$initClick = 'onclick=callInit(\'' .
					escapeStr($_POST['dev']) .
					"', '{$_POST['act']}', '{$_POST['par']}', '{$_POST['start']}', '{$_POST['stk']}'
				)";
				$initForm->setField('startdiskinit', 'button', $initClick . '|class=startjob|disabled=disabled|value=' . gettext('Format'), false);
			$initForm->exitWrapper();
		//
	$initForm->exitBlock();
$initForm->exitFieldSet();
// instantiate the disk configuration form object
$confForm = new cfgForm('sys_storage_edit.php', 'method=post|name=confform|id=confform');
// configuration UI
$confForm->startFieldset('fset_conf', gettext('General Settings'));
	$confForm->startBlock('rw_name');
		$confForm->setLabel(null, gettext('Name'), 'name', 'class=labelcol');
		$confForm->startBlock('rw_name', 'right');
		$confForm->setField('name', 'text', 'size=40|maxlength=40', false, '');
		$confForm->setInputText('name', $cfgPtr['bobo']);
		$confForm->setBlockHint('name', gettext('Enter a descriptive name for this disk.'));
	$confForm->exitBlock();
$confForm->exitFieldSet();
$confForm->setRequired('disk_label', gettext('Disk label'));

$confForm->startWrapper('saveservices');
$saveClick = 'onclick=callSave(\'' .
	escapeStr($_POST['dev']) .
	"', '{$_POST['act']}', '{$_POST['par']}', '{$_POST['stk']}'
)";
$confForm->setField('savecfg', 'button', $saveClick . '|class=startjob|disabled=disabled|value=' . gettext('Save'), false);
$confForm->exitWrapper();

//

// variables for messages populated later from js code
$msgStartPartion = gettext('Creating new partition') . ' ' .
	$_POST['par'] .' ' .
	gettext('on') . ' ' .
	$_POST['dev'] . '... ';
$msgStartFormat = gettext('Formatting partition') . ' ' .
	$_POST['par'] . ' ' .
	gettext('on') . ' ' .
	$_POST['dev'] . '... ';
$msgAskReboot = gettext('Reboot required');
$msgDone = gettext('done.');
// render main layout
require('fbegin.inc');
$initForm->renderForm();
$confForm->renderForm();
echo '<div><pre>';
var_export($_POST);
echo '</pre></div>';
// end layout
require('fend.inc');
?>
<script type="text/javascript">


function callInit(dev, act, par, start, stk)
{
	var uri = '/sys_storage_init.php';
	var wait = '<div id="waiting"><img alt="" src="img/ajax_busy_round.gif"></div>';
	var label = jQuery('#disk_label').val();
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
							jQuery('#mformat').append('<?php echo $msgDone; ?>');
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

// client side field validation

jQuery('#disk_label').keyup(function()
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
