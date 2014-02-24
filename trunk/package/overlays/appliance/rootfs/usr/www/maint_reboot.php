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

require_once 'guiconfig.inc';
require_once 'libs-php/uiutils.lib.php';
if (file_exists('/etc/inc/appliance.lib.php'))
{
	include_once '/etc/inc/appliance.lib.php';
}

session_start();
$_SESSION['maint-halt']['token'] = uniqid(md5(microtime()), true);
$msgConfirmShutdown = escapeStr(_('To confirm please type')) . '\n  Shutdown\n' .escapeStr(_('exactly as suggested above, then press OK to proceed') . '.');
$msgConfirmReboot = escapeStr(_('Click OK to confirm, else cancel') . '.');
$msgProgress = _('Stopping running applications and Unmounting File Systems') . '... ';
$msgDone = _('Done') . '.';

$optionsMarkup = '';
$stopOpts = array();
if (is_callable('getStopApplianceOptions'))
{
	$stopOpts = getStopApplianceOptions();
	foreach (array_keys($stopOpts) as $kOpt)
	{
		$optionsMarkup .= '<div class="optrow">' .
			'<input type="' . $stopOpts[$kOpt]['ftype'] .
			'" value="' . $stopOpts[$kOpt]['mode'] .
			'" id="' . $kOpt .'">&nbsp;' . '
			<label for="'. $kOpt .'">' .
			$stopOpts[$kOpt]['label'] . '</label>' .
			'<input type="hidden" id="expect' . $kOpt . '" value="' . $stopOpts[$kOpt]['expect'] . '">' .
			'</div>';
		//
	}
}

include 'fbegin.inc';
?>
<div id="directions" class="optbox">
<?php
	echo '<ul>',
		'<li>', _('Reboot'), ': ', _('click the button to restart'), '.</li>',
		'<li>', _('Shutdown system'), ': ', _('click the button to shut down'), '.</li>',
		'.</li></ul>',
		'<p class="hint">',
		_('To be safe, before executing the selected action, all running application will be stopped and any read/write enabled filesystem unmounted'),
		'.</p><div id="options">',
		$optionsMarkup,
		'</div>';
	//
?>
</div>
<div id="progress">
</div>
<div id="init-progress"></div>
<div id="controls" class="btnbox">
	<button id="btn_restart" type="button"><?php echo _('Reboot'); ?></button>
	<button id="btn_halt" type="button"><?php echo _('Shutdown system'); ?></button>
</div>
<input id="stk" type="hidden" value="<?php echo $_SESSION['maint-halt']['token']; ?>">
<script type="text/javascript">
jQuery('#btn_restart, #btn_halt').click(function(elem)
{
	var action = null;
	var params = null;
	var pFinal = null;
	var reqUrl = '/maint_action.php';
	var key = jQuery('#stk').val();
	var idClicked = elem.target.id;
	var prepObj = getFieldsInParent('#options');

	jQuery(document).ajaxStop(function()
	{
		var taskDone = 0;
		var taskCount = 0;
		if (params == null)
			return;
		//
		for (var property in prepObj)
		{
			taskCount ++;
			if (jQuery('#expect' + property).val() == jQuery('#retval' + property).val())
			{
				taskDone++;
			}
		}

		if (taskCount == taskDone)
		{
			doExec(reqUrl, pFinal, false, 0, '#progress', false);
		}
	});

	if (idClicked == 'btn_halt')
	{
		var confirmS = window.prompt('<?php echo $msgConfirmShutdown; ?>');
		if (confirmS != null)
		{
			if (confirmS == 'Shutdown')
			{
				action = 'poweroff';
			}
		}
	}
	else if (idClicked == 'btn_restart')
	{
		var confirmR = window.confirm('<?php echo $msgConfirmReboot; ?>');
		if (confirmR == true)
		{
			action = 'reboot';
		}
	}

	if (action != null)
	{
		jQuery('#controls').children().attr('disabled','disabled');
		jQuery('#progress').show();
		pFinal = 'action=' + action + '&stk=' + key;

		for (var property in prepObj)
		{
			params = 'action=prepare' +
				'&' + property + '=' + prepObj[property] +
				'&stk=' + key;
			var expectValue = jQuery('#expect' + property).val();
			var doPoll = (prepObj[property] == 'poll');
			doExec(reqUrl, params, true, expectValue, '#progress', doPoll);
		}
		// if no prepare options set then call the final task directly, else will be called via ajaxStop
		if (params == null)
		{
			doExec(reqUrl, pFinal, false, 0, '#progress', false);
		}
	}
});

function doExec(url, paramsQuery, evGlobal, expectValue, targetContainer, poll, newId)
{
	var ret;

	if (newId == null)
	{
		var newId = appendElement(targetContainer);
	}

	return jQuery.ajax({
		type: 'POST',
		url: url,
		global: evGlobal,
		cache: false,
		async: false,
		timeout: 2000,
		data: paramsQuery,
		dataType: 'json',
		success: function(data)
		{
			jQuery(newId).html(data.msg);
			ret = data.retval;
			if (ret == expectValue)
			{
				jQuery(newId).removeClass('state_wait');
				jQuery(newId).addClass('state_done');
			}
			else if (ret == null)
			{
				jQuery(newId).removeClass('state_wait');
				jQuery(newId).addClass('state_error');
			}
		},
		complete: function(data)
		{
			if (poll & (ret != expectValue))
			{
				setTimeout(function()
				{
					doExec(url, paramsQuery, evGlobal, expectValue, targetContainer, poll, newId)
				}, 5000)
			}
		},
		failure: function(data)
		{
			jQuery(newId).html('Error!');
			jQuery(newId).removeClass('state_wait');
			jQuery(newId).addClass('state_error');
		}
	});
}

function appendElement(updateContainer)
{
	var newId = 'msg' + Date.now() + Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
	var wait = '<div class="state_msg state_wait" id="' + newId + '"> ...</div>';


	jQuery(updateContainer).append(wait);
	return '#' + newId;
}

function getFieldsInParent(parentElem)
{
	var params = {};
	// checkboxes only at this time
	jQuery(parentElem + ' input:checked').each(function()
	{
		params[jQuery(this).attr('id')] = jQuery(this).val();
	});
	return params;
}

function buildParamsQuery(paramsObj)
{
	var params = '';

	for (var property in paramsObj)
	{
		if (paramsObj.hasOwnProperty(property))
		{
			params += '&' + property + '=' + paramsObj[property];
		}
	}
	return params;
}
</script>

<?php include 'fend.inc'; ?>
