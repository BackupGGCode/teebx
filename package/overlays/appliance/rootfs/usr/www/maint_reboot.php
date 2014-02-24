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
// define some constants referenced in fbegin.inc
define('INCLUDE_JSCRIPTS', 'libutils.js');

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
</script>

<?php include 'fend.inc'; ?>
