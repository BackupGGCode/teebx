<?php
/*
	$Id$
	part of AskoziaPbx (http://www.askoziapbx.com)
	part of m0n0wall (http://m0n0.ch/wall)

	Copyright (C) 2003-2006 Manuel Kasper <mk@neon1.net>.
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

function gentitle($title)
{
	global $navlevelsep;
	return join($navlevelsep, $title);
}

function genhtmltitle($title)
{
	global $config;
	return $config['system']['hostname'] . "." . $config['system']['domain'] . " - " . gentitle($title);
}

function dynamic_menu(&$menu, $menu_title)
{
	global $allowed, $config;
	// Make sure the admin can see everything
	// ...disable this check since individual user/group permissions and logins
	// are not implemented yet...
	// if ($_SERVER['REMOTE_USER'] === $config['system']['username']) {
	unset($allowed);
	foreach ($menu as $item)
	{
		$allowed[] = $item[0];
	}
	// }
	foreach ($menu as $desc => $links)
	{
		if ($intersect = array_intersect($menu[$desc], $allowed))
		{
			if (!isset($header_displayed) && ($menu_title == 'Dialplan'))
			{
				echo "\n";
				echo "\t\t<li class=\"menuitem\">" . gettext("Dialplan") . "&nbsp;<a href=\"printable_dialplan.php\" class=\"navlnks\">(" . gettext("print") . ")</a>\n";
				echo "\t\t\t<ul class=\"submenu\">\n";

				$header_displayed = true;
			}
			else if (!isset($header_displayed) && !($menu_title == 'Advanced'))
			{
				echo "\n";
				echo "\t\t<li class=\"menuitem\">" . gettext($menu_title) . "\n";
				echo "\t\t\t<ul class=\"submenu\">\n";

				$header_displayed = true;
			}
			else if (!isset($header_displayed))
			{
				if ($menu_title == 'Advanced')
				{
					if (isset($config['system']['webgui']['expandadvanced']) || strstr($_SERVER['SCRIPT_FILENAME'], "advanced_"))
					{
						echo "\n\t\t<li class=\"menuitem\"><a href=\"javascript:showhide('advanced','tri_advanced')\" class=\"navlnk\">";
						echo "<img src=\"img/tri_o.gif\" id=\"tri_advanced\" width=\"14\" height=\"10\" border=\"0\"></a>";
						echo "<a href=\"javascript:showhide('advanced','tri_advanced')\" class=\"navlnk\">" . gettext("Advanced") . "</a>";
						echo "<span id=\"advanced\">\n";
					}
					else
					{
						echo "\n\t\t<li class=\"menuitem\"><a href=\"javascript:showhide('advanced','tri_advanced')\" class=\"navlnk\">";
						echo "<img src=\"img/tri_c.gif\" id=\"tri_advanced\" width=\"14\" height=\"10\" border=\"0\"></a>";
						echo "<a href=\"javascript:showhide('advanced','tri_advanced')\" class=\"navlnk\">" . gettext("Advanced") . "</a>";
						echo "<span id=\"advanced\" style=\"display: none\">\n";
					}

					echo "\t\t\t<ul class=\"submenu\">\n";
				}

				$header_displayed = true;
			}
			// $intersect may contain one or more links, but we only need the first one
			// BUT, we are doing a foreach here because it might not be element [0]
			foreach ($intersect as $link)
			{
				if ((isset($config['system']['webgui']['hidesip']) && $desc == "SIP") ||
						(isset($config['system']['webgui']['hideiax']) && $desc == "IAX") ||
						(isset($config['system']['webgui']['hideisdn']) && $desc == "ISDN") ||
						(isset($config['system']['webgui']['hideanalog']) && $desc == "Analog"))
				{
					break;
				}
				// Below lines are spaced out oddly to help improve the look of resulting HTML code.
				?>

				<li class="submenuitem"><a href="<?=$link?>" class="navlnk"><?=$desc?></a></li><?
				// Only need the first one, so break out of foreach
				break;
			}
		}
	}
	if ($menu_title == 'Advanced')
	{ ?></span><?
	}
	echo "\n";
	echo "\t\t\t</ul>\n";
	echo "\t\t</li>\n";
}

// Check for issues when changing the web admin username
// $allowed is set in auth.inc *IF* the user is not the webGUI admin
// so, if it's not set here, they were the admin user when they submited the admin username change.
if ((!isset($allowed))/*AND (!is_array($allowed))*/ and
		($_SERVER['SCRIPT_NAME'] == '/system.php') and
		($_SERVER['REMOTE_USER'] != $config['system']['username']))
{
	// webGUI username was just changed, let us change what PHP sees the remote user as so
	// the menu will build properly.  When user hits next link, they will be prompted to reauthenticate.
	$_SERVER['REMOTE_USER'] = $config['system']['username'];
}

/* System */
$menu['System'][gettext("General Setup")] = array('system.php');
$menu['System'][gettext("Storage")] = array('system_storage.php');
$menu['System'][gettext("Firmware")] = array('system_firmware.php');
$menu['System'][gettext("Backup/Restore")] = array('system_backup.php');
$menu['System'][gettext("Factory Defaults")] = array('system_defaults.php');
$menu['System'][gettext("Reboot")] = array('system_reboot.php');

/* Connectivity */
$menu['Connectivity'][gettext("Networking")] = array('network_lan.php',
	'network_phones.php');
$menu['Connectivity'][gettext("Telephony Ports")] = array('ports_analog.php',
	'ports_analog_edit.php',
	'ports_isdn.php',
	'ports_isdn_edit.php');

/* Accounts */
$menu['Accounts'][gettext("Phones")] = array('accounts_phones.php',
	'exten_sip_edit.php',
	'phones_iax_edit.php',
	'phones_isdn_edit.php',
	'phones_analog_edit.php',
	'phones_external_edit.php');
$menu['Accounts'][gettext("Providers")] = array('accounts_providers.php',
	'providers_sip_edit.php',
	'providers_iax_edit.php',
	'providers_isdn_edit.php',
	'providers_analog_edit.php');
$menu['Accounts'][gettext("Faxes")] = array('accounts_faxes.php',
	'faxes_analog_edit.php');

/* Dialplan */
$menu['Dialplan'][gettext("Applications")] = array('dialplan_applications.php',
	'dialplan_applications_edit.php');
$menu['Dialplan'][gettext("Call Groups")] = array('dialplan_callgroups.php',
	'dialplan_callgroups_edit.php');
$menu['Dialplan'][gettext("Transfers")] = array('dialplan_transfers.php');

/* Services */
$menu['Services'][gettext("Notifications")] = array('notifications_email.php');
$menu['Services'][gettext("Voicemail")] = array('services_voicemail.php');
$menu['Services'][gettext("Conferencing")] = array('services_conferencing.php',
	'services_conferencing_edit.php');
if (storage_service_is_active("media"))
{
	$menu['Services'][gettext("Media")] = array('services_media_voicemessage.php',
		'services_media_moh.php', 'services_voiceprompts.php');
}

/* Packages */
// if ($nav = packages_get_www_nav()) {
// $menu['Packages'] = $nav;
// }

/* Status */
$menu['Status'][gettext("Summary")] = array('index.php');
$menu['Status'][gettext("Ports")] = array('status_ports.php');
// $menu['Status'][gettext("Channels")]			= array('status_channels.php');
// $menu['Status'][gettext("Conferences")]			= array('status_conferences.php');
$menu['Status'][gettext("Network Traffic")] = array('status_graph_traffic.php');
$menu['Status'][gettext("CPU Load")] = array('status_graph_cpu.php');
$menu['Status'][gettext("Logs")] = array('status_logs.php?show=kernel');

/* Advanced */
$menu['Advanced'][gettext("RTP")] = array('advanced_rtp.php');
$menu['Advanced'][gettext("SIP")] = array('advanced_sip.php');
$menu['Advanced'][gettext("IAX")] = array('advanced_iax.php');
$menu['Advanced'][gettext("Analog")] = array('advanced_analog.php');
$menu['Advanced'][gettext("Manager")] = array('advanced_manager.php',
	'advanced_manager_edit.php');
$menu['Advanced'][gettext("GUI Options")] = array('advanced_guioptions.php');

?>