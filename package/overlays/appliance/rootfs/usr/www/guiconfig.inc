<?php
/*
  $Id$
	The TeeBX(R) communication platform.
	Copyright(C) 2010 - 2013 Giovanni Vallesi (TeeBX.com)

	originally part of AskoziaPBX svn trunk revision 1514 (http://askozia.com/pbx)
	Copyright (C) 2007-2009 tecema (a.k.a IKT) <http://www.tecema.de>. All rights reserved.
	originally part of m0n0wall (http://m0n0.ch/wall)
	Copyright (C) 2003-2006 Manuel Kasper <mk@neon1.net>. All rights reserved.

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

/* make sure nothing is cached */
	/* debug settings */
	error_reporting(E_ALL);
	ini_set('display_errors', 0);
	ini_set('log_errors', 1);
	ini_set('error_log', '/tmp/php_error.log');

if (!isset($omit_nocacheheaders))
	$omit_nocacheheaders = false;
if (!$omit_nocacheheaders)
{
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
}

/* parse the configuration and include all configuration functions */
require_once("config.inc");
// TODO: why on arm eglibc targets setting locale crashes the php interpreter?? po files endianess??
require_once("i18n.inc");
require_once("functions.inc");
require_once('libs-php/microui.lib.php');

$d_staticroutesdirty_path = $g['varrun_path'] . "/staticroutes.dirty";
$d_extensionsconfdirty_path = $g['varrun_path'] . "/extensionsconf.dirty";
$d_featuresconfdirty_path = $g['varrun_path'] . "/featuresconf.dirty";
$d_conferencingconfdirty_path = $g['varrun_path'] . "/conferencingconf.dirty";
$d_conferencing_kicked_path = $g['varrun_path'] . "/conferencing.kicked";
$d_managerconfdirty_path = $g['varrun_path'] . "/managerconf.dirty";
$d_rtpconfdirty_path = $g['varrun_path'] . "/rtpconf.dirty";
$d_packageconfdirty_path = $g['varrun_path'] . "/packageconf.dirty";
$d_ultmpmounted_path = $g['varrun_path'] . "/ultmp.mounted";
$d_sysrebootreqd_path = $g['varrun_path'] . "/sysreboot.reqd";
$d_passworddirty_path = $g['varrun_path'] . "/password.dirty";

$applications = array(
	"Echo" => _("Echos audio and video back to the caller as soon as it is received. Used to test connection delay."),
	"Milliwatt" => _("Generates a constant 1000Hz tone. Used to test connection quality."),
	"WakeMe" => _("Wake-Up call manager. Call to set and cancel wake-up calls on the current extension.")
	);

$vm_email_languages = array(
	"en-informal" => _("English"),
	"en-formal" => _("English (formal)"),
	"bg-informal" => _("Bulgarian"),
	"bg-formal" => _("Bulgarian (formal)"),
	"cs-informal" => _("Czech"),
	"cs-formal" => _("Czech (formal)"),
	"da-informal" => _("Danish"),
	"da-formal" => _("Danish (formal)"),
	"nl-informal" => _("Dutch"),
	"nl-formal" => _("Dutch (formal)"),
	"fi-informal" => _("Finnish"),
	"fi-formal" => _("Finnish (formal)"),
	"fr-informal" => _("French"),
	"fr-formal" => _("French (formal)"),
	"de-informal" => _("German"),
	"de-formal" => _("German (formal)"),
	"gr-informal" => _("Greek"),
	"gr-formal" => _("Greek (formal)"),
	"it-informal" => _("Italian"),
	"it-formal" => _("Italian (formal)"),
	"ja-formal" => _("Japanese (formal)"),
	"pl-informal" => _("Polish"),
	"pl-formal" => _("Polish (formal)"),
	"pt-br-informal" => _("Portuguese, Brazil"),
	"pt-br-formal" => _("Portuguese, Brazil (formal)"),
	"ru-informal" => _("Russian"),
	"ru-formal" => _("Russian (formal)"),
	"es-formal" => _("Spanish (formal)"),
	"se-informal" => _("Swedish"),
	"tr-informal" => _("Turkish"),
	"tr-formal" => _("Turkish (formal)")
	);

$isdn_dchannel_modes = array(0 => "",
	"DRVR_DSS1_TE" => _("point-to-multipoint, terminal equipment"),
	"DRVR_DSS1_NT" => _("multipoint-to-point, network termination"),
	"DRVR_DSS1_P2P_TE" => _("point-to-point, terminal equipment"),
	"DRVR_DSS1_P2P_NT" => _("point-to-point, network termination")
	// "DRVR_D_CHANNEL" => "default D-channel driver",
	// "DRVR_CAPI_TE" => "",
	// "DRVR_DIEHL_TE" => "",
	// "DRVR_TINA_DD_TE" => "",
	// "DRVR_AMV_B1_TE" => "",
	// "DRVR_D64S" => "This driver can be used on single B-channel leased lines.",
	// "DRVR_DUMMY" => "",
	);

// !TODO: duplicated in advanced_analog.php
$system_tonezones = array(
	"us" => _("United States / North America"),
	"au" => _("Australia"),
	"at" => _("Austria"),
	"be" => _("Belgium"),
	"br" => _("Brazil"),
	"bg" => _("Bulgaria"),
	"cl" => _("Chile"),
	"cn" => _("China"),
	"cz" => _("Czech Republic"),
	"dk" => _("Denmark"),
	"ee" => _("Estonia"),
	"fi" => _("Finland"),
	"fr" => _("France"),
	"de" => _("Germany"),
	"gr" => _("Greece"),
	"hu" => _("Hungary"),
	"in" => _("India"),
	"il" => _("Israel"),
	"it" => _("Italy"),
	"ja" => _("Japan"),
	"lt" => _("Lithuania"),
	"my" => _("Malaysia"),
	"mx" => _("Mexico"),
	"nl" => _("Netherlands"),
	"nz" => _("New Zealand"),
	"no" => _("Norway"),
	"ph" => _("Philippines"),
	"pl" => _("Poland"),
	"pt" => _("Portugal"),
	"ru" => _("Russia / ex Soviet Union"),
	"sg" => _("Singapore"),
	"za" => _("South Africa"),
	"es" => _("Spain"),
	"se" => _("Sweden"),
	"ch" => _("Switzerland"),
	"tw" => _("Taiwan"),
	"th" => _("Thailand"),
	"uk" => _("United Kingdom"),
	"us-old" => _("United States Circa 1950/ North America"),
	"ve" => _("Venezuela / South America")
	);

$manager_permissions = array(
	"agent",
	"call",
	"command",
	"config",
	"log",
	"user",
	"verbose",
	"system"
	);

function exec_rc_script($scriptname)
{
	global $d_sysrebootreqd_path;

	if (file_exists($d_sysrebootreqd_path))
		return 0;

	exec($scriptname . " >/dev/null 2>&1", $execoutput, $retval);
	return $retval;
}

function exec_rc_script_async($scriptname)
{
	global $d_sysrebootreqd_path;

	if (file_exists($d_sysrebootreqd_path))
		return 0;

	exec("nohup " . $scriptname . " >/dev/null 2>&1 &", $execoutput, $retval);
	return $retval;
}

function verify_gzip_file($fname)
{
	$returnvar = mwexec("/bin/gunzip -t " . escapeshellarg($fname));
	if ($returnvar != 0)
		return 0;
	else
		return 1;
}

function get_std_save_message($ok)
{
	global $d_sysrebootreqd_path;

	if ($ok == 0)
	{
		if (file_exists($d_sysrebootreqd_path))
			return _("The changes have been saved. You must <a href=\"/system_reboot.php\">reboot</a> your pbx for the changes to take effect.");
		else
			return _("The changes have been applied successfully.");
	}
	else
	{
		return sprintf(_("ERROR: the changes could not be applied (error code %s)."), $ok);
	}
}

function dynamic_tab_menu(&$tabs)
{
	global $allowed, $config;
	// ...disable this check since individual user/group permissions and logins
	// are not implemented yet...
	// if ($_SERVER['REMOTE_USER'] == $config['system']['username']) {
	$authorized = &$tabs;
	// } else {
	// $authorized = &$allowed;
	// }
	$curr_request = basename($_SERVER['SCRIPT_FILENAME']);
	$curr_querystr = $_SERVER['QUERY_STRING'];
	if (strlen($curr_querystr) > 0)
	{
		$curr_request = $curr_request . '?' . $curr_querystr;
	}
	foreach ($tabs as $desc => $link)
	{
		if (in_array($link, $authorized))
		{
			// TODO: better implementation needed, this works only if the static part is at beginning in the query string
			print_r('<!-- ' . "strpos($curr_request, $link) =>" . strpos($curr_request, $link) . ' -->');
			if (strpos($curr_request, $link) === false)
			{
				echo '<li class="tabinact"><a href="' . $link . '">' . $desc . '</a></li>';
			}
			else
			{
				echo '<li class="tabact">' . $desc . '</li>';
			}
		}
	}
}

function print_r_html($data, $return_data = true)
{
	$data = print_r($data, true);
	$data = str_replace(" ", "&nbsp;", $data);
	$data = str_replace("\r\n", "<br>\r\n", $data);
	$data = str_replace("\r", "<br>\r", $data);
	$data = str_replace("\n", "<br>\n", $data);

	if (!$return_data)
		echo $data;
	else
		return $data;
}

function split_and_clean_lines($s)
{
	$lines = preg_split("/[\x0D\x0A]+/", $s, - 1, PREG_SPLIT_NO_EMPTY);
	$n = count($lines);
	for ($i = 0; $i < $n; $i++)
	{
		$lines[$i] = trim($lines[$i], " ");
	}
	return $lines;
}

function gather_incomingextensionmaps($post, $multi = false)
{
	$maps = array();
	$keys = array_keys($post);

	if ($multi)
	{
		// XXX : the entire multiple vs single display mechanism needs to be redesigned...this will get / is horrible
	}
	else
	{
		foreach ($keys as $key)
		{
			if ((strpos($key, "destinationid_") !== false && $post[$key]) ||
					(strpos($key, "incomingpattern_") !== false && strlen($post[$key])))
			{
				$match = explode("_", $key);
				$maps[$match[1]][$match[0]] = $post[$key];
			}
		}
	}

	return $maps;
}

?>
