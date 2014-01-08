<?php
/*
	$Id$
	part of BoneOS build platform (http://www.teebx.com/)
	Copyright(C) 2011 - 2013 Giovanni Vallesi.
	All rights reserved.

	originally part of AskoziaPBX svn trunk revision 1514 (http://askozia.com/pbx)
	Copyright (C) 2009-2010 IKT <http://itison-ikt.de>.
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
	AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
	AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
	SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
	INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
	CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
	POSSIBILITY OF SUCH DAMAGE.
*/

require('guiconfig.inc');
require_once('libs-php/utils.lib.php');
define('INCLUDE_TABSFILES', true);

$pgtitle = array(_('Status'), _('Logs'));
$log_get_cmd = 'dmesg';
$logHeader = _('Kernel log entries');
if (isset($_GET))
{
	if (!empty($_GET['show']))
	{
		switch ($_GET['show'])
		{
			case 'syslog':
				$log_get_cmd = 'logread|grep -v "asterisk\["';
				$logHeader = _('System log entries');
				break;
			case 'asterisk':
				$log_get_cmd = 'logread|grep "asterisk\["';
				$logHeader = _('Asterisk pbx log entries');
				break;
			case 'cdr':
				$logHeader = _('Call detail records') . ' (STOP: Not yet ready!)';
				break;
		}
	}
}
$log_get_cmd = escapeStr($log_get_cmd);

include('fbegin.inc');

?><script type="text/JavaScript">
<!--

	var last_line;
	var displayMarkers = false;
	var auto_updating = true;
	var msg_disabled = '<?php echo _('Stopped updates'); ?>';
	var msg_enabled = '<?php echo _('Resuming updates'); ?>';

	jQuery(document).ready(function(){
		update();
	});

	function toggle_auto_updating() {
		if (auto_updating) {
			auto_updating = false;
		} else {
			auto_updating = true;
			jQuery("#log_contents").append(
				'<div class="logentry">' + msg_enabled + '</div>'
			);
			update();
		}
	}

	function update() {
		jQuery.get("cgi-bin/ajax.cgi", { exec_shell: "<?php echo $log_get_cmd; ?>" }, function(data){
			var i = 0;
			var lines = data.split(/\n/);

			if (last_line) {
				var overlaps = false;
				for (i = 0; i < lines.length - 1; i++)
				{
					if (last_line == lines[i])
					{
						overlaps = true;
						i++;
						break;
					}
				}
				if (!overlaps)
				{
					i = 0;
				}
			}

			for (i = i; i < lines.length - 1; i++)
			{
				var rowStyle = 'logentry';
				var newMarker = '';

				last_line = lines[i];
				if (last_line.search(/ERROR/i) != -1)
				{
					rowStyle = rowStyle + ' logentry_err';
					newMarker = '<a class="mark_err" href="#LE' + i + '">|</a>';
					displayMarkers = true;
				}
				else if (last_line.search(/WARNING/i) != -1)
				{
					rowStyle = rowStyle + ' logentry_warn';
					newMarker = '<a class="mark_warn" href="#LE' + i + '">|</a>';
					displayMarkers = true;
				}
				else if (last_line.search(/FAIL/i) != -1)
				{
					rowStyle = rowStyle + ' logentry_fail';
					newMarker = '<a class="mark_fail" href="#LE' + i + '">|</a>';
					displayMarkers = true;
				}
				jQuery("#log_contents").append(
					'<div class="' + rowStyle + '" id="LE' + i + '">' + last_line + '</div>'
				);
				if (newMarker.length > 0)
				{
					jQuery(newMarker).insertBefore('#markersbar :last');
				}
			}

			if (displayMarkers)
			{
				jQuery("#markersbar").show();
			}
		});

		if (!auto_updating) {
			jQuery("#log_contents").append(
				'<div class="logentry">' + msg_disabled + '</div>'
			);
		} else {
			setTimeout("update()", 5000);
		}
	}

//-->
</script>
<?php
	// prepare logical groups to show tabs
	$arrTabs[] = array('url' => '/maint_status_logs.php?show=kernel', 'label' => _('Kernel'));
	$arrTabs[] = array('url' => '/maint_status_logs.php?show=syslog', 'label' => _('System'));
	$arrTabs[] = array('url' => '/maint_status_logs.php?show=asterisk', 'label' => _('Pbx'));
	$arrTabs[] = array('url' => '/maint_status_logs.php?show=cdr', 'label' => _('Calls'));
	getTabs($arrTabs, true);
?>
<div style="clear: both;"></div>
<div class="content_block">
	<div class="markersbox">
		<?php echo $logHeader; ?>
		<div class="marksbar" id="markersbar">
			<span class="marksend"></span>
		</div>
	</div>
	<div id="log_contents" class="scrollable"></div>
	<div style="clear: both;"></div>
	<div style="float:right; padding-top:5px" id="log_controls">
		<input id="update_toggle" type="submit" class="formbtn" value="<?php echo _("Toggle auto-updates"); ?>" onclick="toggle_auto_updating()">
	</div>
</div>
<?php include('fend.inc'); ?>
