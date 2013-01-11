<?php
/*
	$Id$
	part of TeeBX VoIP Communication platform (http://www.teebx.com/)
	Copyright(C) 2010 - 2011 Giovanni Vallesi.

	originally part of AskoziaPBX (http://askozia.com/pbx)
	Copyright (C) 2007-2011 tecema (a.k.a IKT) <http://www.tecema.de>.
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

	3. Redistribution in any form at a charge, that in whole or in part
	   contains or is derived from the software, including but not limited to
	   value added products, is prohibited without prior written consent of
	   tecema.

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

/* include all configuration functions */
require_once("functions.inc");

/**
* Generates all Asterisk configuration files and (re)starts the Asterisk process
*/
function pbx_configure()
{
	global $config, $g;

	if (!$g['booting'])
	{
		pbx_stop();
	}

	$res = 0;
	if ($g['booting']) echo "   |- generating Asterisk process configuration...";
	$res |= asterisk_conf_generate();
	if ($g['booting']) echo "done\n";

	if ($g['booting']) echo "   |- generating modules configuration...";
	$res |= modules_conf_generate();
	if ($g['booting']) echo "done\n";

	if ($g['booting']) echo "   |- generating PHP AGI configuration...";
	$res |= phpagi_conf_generate();
	if ($g['booting']) echo "done\n";

	if ($g['booting']) echo "   |- generating indications configuration...";
	$res |= indications_conf_generate();
	if ($g['booting']) echo "done\n";

	if ($g['booting']) echo "   |- generating manager configuration...";
	$res |= manager_conf_generate();
	if ($g['booting']) echo "done\n";

	if ($g['booting']) echo "   |- generating rtp configuration...";
	$res |= rtp_conf_generate();
	if ($g['booting']) echo "done\n";

	if ($g['booting']) echo "   |- generating sip configuration...";
	$res |= sip_conf_generate();
	if ($g['booting']) echo "done\n";

	if ($g['booting']) echo "   |- generating iax configuration...";
	$res |= iax_conf_generate();
	if ($g['booting']) echo "done\n";

	if ($g['booting']) echo "   |- generating skinny configuration...";
	$res |= skinny_conf_generate();
	if ($g['booting']) echo "done\n";

	if ($g['booting']) echo "   |- generating conferencing configuration...";
	$res |= meetme_conf_generate();
	if ($g['booting']) echo "done\n";

	if ($g['booting']) echo "   |- generating music-on-hold configuration...";
	$res |= musiconhold_conf_generate();
	if ($g['booting']) echo "done\n";

	if ($g['booting']) echo "   |- generating dialplan...";
	$res |= extensions_conf_generate();
	$res |= features_conf_generate();
	if ($g['booting']) echo "done\n";

	if ($g['booting']) echo "   |- generating voicemail configuration...";
	$res |= voicemail_conf_generate();
	$res |= notifications_msmtp_conf_generate();
	if ($g['booting']) echo "done\n";

	if ($g['booting']) echo "   |- generating DAHDI configuration...";
	$res |= dahdi_configure();
	if ($g['booting']) echo "done\n";

	if ($g['booting']) echo "   |- generating manual configuration...";
	$res |= adsi_conf_generate();
	$res |= adtranvofr_conf_generate();
	$res |= agents_conf_generate();
	$res |= ais_conf_generate();
	$res |= alarmreceiver_conf_generate();
	$res |= alsa_conf_generate();
	$res |= cdr_adaptive_odbc_conf_generate();
	$res |= cdr_manager_conf_generate();
	$res |= cdr_odbc_conf_generate();
	$res |= cdr_pgsql_conf_generate();
	$res |= cdr_sqlite3_custom_conf_generate();
	$res |= cdr_tds_conf_generate();
	$res |= cli_conf_generate();
	$res |= cli_aliases_conf_generate();
	$res |= cli_permissions_conf_generate();
	$res |= codecs_conf_generate();
	$res |= console_conf_generate();
	$res |= dbsep_conf_generate();
	$res |= dnsmgr_conf_generate();
	$res |= dsp_conf_generate();
	$res |= dundi_conf_generate();
	$res |= extconfig_conf_generate();
	$res |= extensions_minivm_conf_generate();
	$res |= festival_conf_generate();
	$res |= followme_conf_generate();
	$res |= func_odbc_conf_generate();
	$res |= gtalk_conf_generate();
	$res |= h323_conf_generate();
	$res |= http_conf_generate();
	$res |= iaxprov_conf_generate();
	$res |= jabber_conf_generate();
	$res |= jingle_conf_generate();
	$res |= mgcp_conf_generate();
	$res |= minivm_conf_generate();
	$res |= misdn_conf_generate();
	$res |= muted_conf_generate();
	$res |= chan_ooh323_conf_generate();
	$res |= osp_conf_generate();
	$res |= oss_conf_generate();
	$res |= phone_conf_generate();
	$res |= phoneprov_conf_generate();
	$res |= queuerules_conf_generate();
	$res |= queues_conf_generate();
	$res |= res_config_sqlite_conf_generate();
	$res |= res_ldap_conf_generate();
	$res |= res_odbc_conf_generate();
	$res |= res_pgsql_conf_generate();
	$res |= res_snmp_conf_generate();
	$res |= sla_conf_generate();
	$res |= unistim_conf_generate();
	$res |= usbradio_conf_generate();
	$res |= users_conf_generate();
	$res |= vpb_conf_generate();
	if ($g['booting']) echo "done\n";
	if ($g['booting']) echo "   |- executing Asterisk...";
	$res |= pbx_start();
	if ($g['booting']) echo "done\n";

	return $res;
}

function pbx_start()
{
	return mwexec_bg("/usr/sbin/asterisk");
}

function pbx_stop()
{
	killbyname("astcanary");
	killbyname("asterisk");
}

function pbx_restart()
{
	$res = 0;
	$res |= pbx_stop();
	$res |= pbx_start();
	return $res;
}

/**
* Executes a command using the AMI
*/
function pbx_exec($cmd, $output = null)
{
	global $savemsg;
	$token = md5(uniqid(rand()));
	$errno = 0;
	$errstr = 0;
	$fp = @fsockopen("localhost", 5038, &$errno, &$errstr, 20);
	if (!$fp)
	{
		return 1;
	}

	fputs($fp, "Action: login\r\n");
	fputs($fp, "Username: admin\r\n");
	fputs($fp, "Secret: teebx\r\n");
	fputs($fp, "Events: off\r\n\r\n");
	usleep(500);

	fputs($fp, "Action: COMMAND\r\n");
	fputs($fp, "command: $cmd\r\n");
	fputs($fp, "ActionID: $token\r\n\r\n");
	usleep(500);

	$out = fread($fp, 38000);
	while (strpos($out, "--END COMMAND--") == 0)
	{
		$out .= fread($fp, 38000);
	}
	fclose ($fp);

	$out = substr($out, strpos($out, "ActionID"));
	$out = substr($out, strpos($out, "\n") + 1);
	$out = substr($out, 0, strpos($out, "--END COMMAND--") - 1);
	// XXX : better command failure checking needed
	$output = $out;

	return 0; // XXX : this needs to return a value for SUCCESS/FAILURE
}

/**
* Determines the validity of a given internal extension
*/
function pbx_is_valid_extension($extension)
{
	if (!verify_is_numericint($extension) || (strlen($extension) < 1))
		return false;

	return true;
}

/**
* Determines the validity of a given Caller ID
* XXX : unimplemented
*/
function pbx_is_valid_callerid($id)
{
	if (preg_match("/^[a-z0-9]+(\s{1}[a-z0-9]|[a-z0-9])+$/i", $id))
	{
		return true;
	}
	else
	{
		return false;
	}
}

/**
* Determines the validity of a given Caller ID string
* XXX : unimplemented
*/
function pbx_is_valid_callerid_string($string)
{
	if (preg_match("/^[a-z0-9]+(\s{1}[a-z0-9]|[a-z0-9])+\s{1}<{1}([0-9]+(\s{1}[0-9]|[0-9])+)+>$/i", $string))
	{
		return true;
	}
	else
	{
		return false;
	}
}

/**
* Determines the validity of a given secret
* XXX : unimplemented
*/
function pbx_is_valid_secret($secret)
{
	return true;
}

/**
* Determines the validity of a given username
* XXX : unimplemented
*/
function pbx_is_valid_username($username)
{
	return true;
}

/**
* Checks is the supplied dialpattern already exists.
*/
function pbx_dialpattern_exists($dialpattern, &$return_provider_name, $sending_provider_id = false)
{
	$allpatterns = pbx_get_dialpatterns();

	if (is_array($allpatterns))
	{
		foreach ($allpatterns as $provider_id => $patterns)
		{
			if ($provider_id == $sending_provider_id)
			{
				continue;
			}
			if (is_array($patterns) && in_array($dialpattern, $patterns))
			{
				$return_provider_name = pbx_uniqid_to_name($provider_id);
				return true;
			}
		}
	}

	return false;
}

/**
* Determines the validity of a given dialpattern
*/
function pbx_is_valid_dialpattern($dialpattern, &$error, $incoming = false)
{
	$incoming_regex = "/^[XNZ0-9\-\[\]\.\!]+$/";
	$outgoing_regex = "/^[XNZ0-9\-\[\]\.\!\|\+\*\#]+$/";

	$invalid_regex = $incoming ? $incoming_regex : $outgoing_regex;
	// invalid character
	if (!preg_match($invalid_regex, $dialpattern))
	{
		$error = gettext("It contains an invalid character");
		return false;
	}
	// doubled character
	if (substr_count($dialpattern, "|") > 1)
	{
		$error = gettext("It contains an doubled \"|\" character, only one is allowed.");
		return false;
	}
	if (substr_count($dialpattern, "+") > 1)
	{
		$error = gettext("It contains an doubled \"+\" character, only one is allowed.");
		return false;
	}
	if (substr_count($dialpattern, "-") > 1)
	{
		$error = gettext("It contains an doubled \"-\" character, only one is allowed.");
		return false;
	}
	// bracketing
	$len = strlen($dialpattern);
	$open = false;
	$open_index = 0;
	for($i = 0; $i < $len; $i++)
	{
		if ($dialpattern[$i] == "[")
		{
			if ($open)
			{
				$error = gettext("Nested square brackets are not allowed.");
				return false;
			}
			else
			{
				$open = true;
				$open_index = $i;
			}
		}
		else if ($dialpattern[$i] == "]")
		{
			if (!$open)
			{
				$error = gettext("Unmatched square bracket.");
				return false;
			}
			else if (($i - $open_index) < 3)
			{
				$error = gettext("Brackets are not needed for less than 2 characters.");
				return false;
			}
			else
			{
				$open = false;
			}
		}
	}
	// invalid characters in brackets
	/* XXX : not working...
	if (preg_match("/\[[XNZ\.\|\+]+\]/", $dialpattern)) {
		$error = "An invalid character is contained within square brackets, only digits and hyphens are allowed.";
		return false;
	}
	*/
	// invalid appended prefix
	// XXX : incorrectly identifies "1|0+X!" as an invalid pattern
	if (($plus_offset = strpos($dialpattern, "+")) !== false)
	{
		if (!preg_match("/^[0-9\*\#]+\+/", $dialpattern))
		{
			$error = gettext("Appended prefixes may only contain digits, '*' and '#'.");
			return false;
		}
		$dialpattern = substr($dialpattern, $plus_offset + 1);
	}
	// invalid chopped prefix
	if (($pipe_offset = strpos($dialpattern, "|")) !== false)
	{
		if (strpos(substr($dialpattern, 0, $pipe_offset), ".") !== false)
		{
			$error = gettext("The wildcard character \".\" is not allowed before a prefix which is to be removed.");
			return false;
		}
		if (strpos(substr($dialpattern, 0, $pipe_offset), "!") !== false)
		{
			$error = gettext("The wildcard character \"!\" is not allowed before a prefix which is to be removed.");
			return false;
		}
	}

	return true;
}

/**
* Merges all *get_extensions() into a single array
* XXX : should be reimplemented to have an optional "technology" parameter
* so each of these sub _get_ functions can be generally implemented here
*/
function pbx_get_extensions()
{
	global $config;

	return array_merge(
		sip_get_extensions(),
		iax_get_extensions(),
		isdn_get_extensions(),
		analog_get_extensions(),
		skinny_get_extensions(),
		conferencing_get_extensions(),
		external_get_extensions(),
		callgroups_get_extensions(),
		applications_get_extensions(),
		fax_get_extensions()
		);
}

function pbx_get_next_default_phone_extension()
{
	global $defaults;

	$all_extensions = pbx_get_extensions();
	$new_extension = $defaults['accounts']['phones']['default-extension-start'];
	while (in_array($new_extension, $all_extensions))
	{
		$new_extension++;
	}

	return $new_extension;
}

/**
* Merges all *get_providers() into a single array
* XXX : should be reimplemented to have an optional "technology" parameter
* so each of these sub _get_ functions can be generally implemented here
*/
function pbx_get_providers()
{
	global $config;

	return pbx_sort_providers(
		array_merge(
			sip_get_providers(),
			iax_get_providers(),
			isdn_get_providers(),
			analog_get_providers()
			));
}

/**
* Returns a provider based on its uniqid
*/
function pbx_get_provider($uniqid)
{
	if (strstr($uniqid, "SIP-PROVIDER"))
	{
		return sip_get_provider($uniqid);
	}
	else if (strstr($uniqid, "IAX-PROVIDER"))
	{
		return iax_get_provider($uniqid);
	}
	else if (strstr($uniqid, "ISDN-PROVIDER"))
	{
		return isdn_get_provider($uniqid);
	}
	else if (strstr($uniqid, "ANALOG-PROVIDER"))
	{
		return analog_get_provider($uniqid);
	}

	return;
}

/**
* Deletes a provider from the configuration array.
* XXX : as opposed to the pbx_get_* functions, this is implemented in pbx.inc itself
* this seems more logical as there is nothing technology specific going on here.
* The other functions should be refactored to use this method if no argument arises
* against it.
*/
function pbx_delete_provider($uniqid)
{
	global $g, $uniqid_map;

	$pieces = explode("-", $uniqid);
	$provider_type = strtolower($pieces[0]);

	if ($pieces[1] == "PROVIDER" &&
		in_array($provider_type, $g['provider_types']) &&
			is_array($GLOBALS['config'][$provider_type]['provider'][$uniqid_map[$uniqid]]))
	{
		unset($GLOBALS['config'][$provider_type]['provider'][$uniqid_map[$uniqid]]);
		dialplan_remove_provider_reference_from_phones($uniqid);
		return 0;
	}
	else
	{
		return "ERROR: invalid uniqid ($uniqid) provided in pbx_delete_provider!";
	}
}

/**
* Marks a provider disabled
*/
function pbx_disable_provider($uniqid)
{
	global $g, $uniqid_map;

	$pieces = explode("-", $uniqid);
	$provider_type = strtolower($pieces[0]);

	if ($pieces[1] == "PROVIDER" &&
		in_array($provider_type, $g['provider_types']) &&
			is_array($GLOBALS['config'][$provider_type]['provider'][$uniqid_map[$uniqid]]))
	{
		$GLOBALS['config'][$provider_type]['provider'][$uniqid_map[$uniqid]]['disabled'] = true;
		return 0;
	}
	else
	{
		return "ERROR: invalid uniqid ($uniqid) provided in pbx_disable_provider!";
	}
}

/**
* Marks a provider enabled
*/
function pbx_enable_provider($uniqid)
{
	global $g, $uniqid_map;

	$pieces = explode("-", $uniqid);
	$provider_type = strtolower($pieces[0]);

	if ($pieces[1] == "PROVIDER" &&
		in_array($provider_type, $g['provider_types']) &&
			is_array($GLOBALS['config'][$provider_type]['provider'][$uniqid_map[$uniqid]]))
	{
		unset($GLOBALS['config'][$provider_type]['provider'][$uniqid_map[$uniqid]]['disabled']);
		return 0;
	}
	else
	{
		return "ERROR: invalid uniqid ($uniqid) provided in pbx_enable_provider!";
	}
}

function pbx_provider_is_active($uniqid)
{
	global $g, $uniqid_map;

	$pieces = explode("-", $uniqid);
	$provider_type = strtolower($pieces[0]);

	if ($pieces[1] == "PROVIDER" &&
		in_array($provider_type, $g['provider_types']) &&
			is_array($GLOBALS['config'][$provider_type]['provider'][$uniqid_map[$uniqid]]))
	{
		return !isset($GLOBALS['config'][$provider_type]['provider'][$uniqid_map[$uniqid]]['disabled']);
	}
	else
	{
		return "ERROR: invalid uniqid ($uniqid) provided in pbx_provider_is_active!";
	}
}

/**
* Sorts providers by name
*/
function pbx_sort_providers($providers)
{
	usort($providers, "pbx_sort_by_name");

	return $providers;
}

/**
* Merges all *get_phones() into a single array
* XXX : should be reimplemented to have an optional "technology" parameter
* so each of these sub _get_ functions can be generally implemented here
*/
function pbx_get_phones()
{
	global $config;

	return pbx_sort_phones(
		array_merge(
			sip_get_phones(),
			iax_get_phones(),
			isdn_get_phones(),
			skinny_get_phones(),
			analog_get_phones()
			));
}

/**
* Returns a phone based on its uniqid
*/
function pbx_get_phone($uniqid)
{
	if (strstr($uniqid, "SIP-PHONE"))
	{
		return sip_get_phone($uniqid);
	}
	else if (strstr($uniqid, "IAX-PHONE"))
	{
		return iax_get_phone($uniqid);
	}
	else if (strstr($uniqid, "ISDN-PHONE"))
	{
		return isdn_get_phone($uniqid);
	}
	else if (strstr($uniqid, "ANALOG-PHONE"))
	{
		return analog_get_phone($uniqid);
	}
	else if (strstr($uniqid, "SKINNY-PHONE"))
	{
		return skinny_get_phone($uniqid);
	}
	else if (strstr($uniqid, "EXTERNAL-PHONE"))
	{
		return external_get_phone($uniqid);
	}
	else
	{
		return "ERROR: unmatched uniqid in pbx_get_phone!";
	}
}

/**
* Deletes a phone from the configuration array.
* XXX : as opposed to the pbx_get_* functions, this is implemented in pbx.inc itself
* this seems more logical as there is nothing technology specific going on here.
* The other functions should be refactored to use this method if no argument arises
* against it.
*/
function pbx_delete_phone($uniqid)
{
	global $g, $uniqid_map;

	$pieces = explode("-", $uniqid);
	$phone_type = strtolower($pieces[0]);

	if ($pieces[1] == "PHONE" &&
		in_array($phone_type, $g['phone_types']) &&
			is_array($GLOBALS['config'][$phone_type]['phone'][$uniqid_map[$uniqid]]))
	{
		unset($GLOBALS['config'][$phone_type]['phone'][$uniqid_map[$uniqid]]);
		dialplan_remove_incomingextensionmap_reference_from_providers($uniqid);
		dialplan_remove_member_reference_from_callgroups($uniqid);
		return 0;
	}
	else
	{
		return "ERROR: invalid uniqid ($uniqid) provided in pbx_delete_phone!";
	}
}

/**
* Marks a phone disabled
*/
function pbx_disable_phone($uniqid)
{
	global $g, $uniqid_map;

	$pieces = explode("-", $uniqid);
	$phone_type = strtolower($pieces[0]);

	if ($pieces[1] == "PHONE" &&
		in_array($phone_type, $g['phone_types']) &&
			is_array($GLOBALS['config'][$phone_type]['phone'][$uniqid_map[$uniqid]]))
	{
		$GLOBALS['config'][$phone_type]['phone'][$uniqid_map[$uniqid]]['disabled'] = true;
		return 0;
	}
	else
	{
		return "ERROR: invalid uniqid ($uniqid) provided in pbx_disable_phone!";
	}
}

/**
* Marks a phone enabled
*/
function pbx_enable_phone($uniqid)
{
	global $g, $uniqid_map;

	$pieces = explode("-", $uniqid);
	$phone_type = strtolower($pieces[0]);

	if ($pieces[1] == "PHONE" &&
		in_array($phone_type, $g['phone_types']) &&
			is_array($GLOBALS['config'][$phone_type]['phone'][$uniqid_map[$uniqid]]))
	{
		unset($GLOBALS['config'][$phone_type]['phone'][$uniqid_map[$uniqid]]['disabled']);
		return 0;
	}
	else
	{
		return "ERROR: invalid uniqid ($uniqid) provided in pbx_enable_phone!";
	}
}

/**
* Sorts phones by Caller ID
* XXX : this sort differs in behavior from the individual _sort_phones() functions
*/
function pbx_sort_phones($phones)
{
	usort($phones, "_a_sortphones");

	return $phones;
}

/* XXX : replace with "sort_by_callerid_field" */
function _a_sortphones($a, $b)
{
	return strcmp($a['callerid'], $b['callerid']);
}

function pbx_delete_fax($uniqid)
{
	global $g, $uniqid_map;

	$pieces = explode("-", $uniqid);
	$fax_type = strtolower($pieces[0]);

	if ($pieces[1] == "FAX" &&
		in_array($fax_type, $g['fax_types']) &&
			is_array($GLOBALS['config'][$fax_type]['fax'][$uniqid_map[$uniqid]]))
	{
		unset($GLOBALS['config'][$fax_type]['fax'][$uniqid_map[$uniqid]]);
		dialplan_remove_defaultfax_reference_from_providers($uniqid);
		return 0;
	}
	else
	{
		return "ERROR: invalid uniqid ($uniqid) provided in pbx_delete_fax!";
	}
}

function pbx_disable_fax($uniqid)
{
	global $g, $uniqid_map;

	$pieces = explode("-", $uniqid);
	$fax_type = strtolower($pieces[0]);

	if ($pieces[1] == "FAX" &&
		in_array($fax_type, $g['fax_types']) &&
			is_array($GLOBALS['config'][$fax_type]['fax'][$uniqid_map[$uniqid]]))
	{
		$GLOBALS['config'][$fax_type]['fax'][$uniqid_map[$uniqid]]['disabled'] = true;
		return 0;
	}
	else
	{
		return "ERROR: invalid uniqid ($uniqid) provided in pbx_disable_fax!";
	}
}

function pbx_enable_fax($uniqid)
{
	global $g, $uniqid_map;

	$pieces = explode("-", $uniqid);
	$fax_type = strtolower($pieces[0]);

	if ($pieces[1] == "FAX" &&
		in_array($fax_type, $g['fax_types']) &&
			is_array($GLOBALS['config'][$fax_type]['fax'][$uniqid_map[$uniqid]]))
	{
		unset($GLOBALS['config'][$fax_type]['fax'][$uniqid_map[$uniqid]]['disabled']);
		return 0;
	}
	else
	{
		return "ERROR: invalid uniqid ($uniqid) provided in pbx_enable_fax!";
	}
}

/**
* Merges all *get_dialpatterns() into a single array
* XXX : should be reimplemented to have an optional "technology" parameter
* so each of these sub _get_ functions can be generally implemented here
*/
function pbx_get_dialpatterns()
{
	global $config;

	return array_merge(
		sip_get_dialpatterns(),
		iax_get_dialpatterns(),
		isdn_get_dialpatterns(),
		analog_get_dialpatterns()
		);
}

/**
* usort callback for sorting an array alphabetically by name field
*/
function pbx_sort_by_name($a, $b)
{
	return strcmp($a['name'], $b['name']);
}

/**
* usort callback for sorting an array numerically by extension field
*/
function pbx_sort_by_extension($a, $b)
{
	if ($a['extension'] == $b['extension'])
	{
		return 0;
	}
	return ($a['extension'] < $b['extension']) ? - 1 : 1;
}

/**
* usort callback for sorting an array numerically by unit field
*/
function pbx_sort_by_unit($a, $b)
{
	if ($a['unit'] == $b['unit'])
	{
		return 0;
	}
	return ($a['unit'] < $b['unit']) ? - 1 : 1;
}

/**
* usort callback for sorting an array numerically by number field
*/
function pbx_sort_by_number($a, $b)
{
	if ($a['number'] == $b['number'])
	{
		return 0;
	}
	return ($a['number'] < $b['number']) ? - 1 : 1;
}

/**
* Parses "core show channels" output to return a channel list
*/
function pbx_get_active_channels(&$channel_list)
{
	pbx_exec("core show channels", &$output);
	$lines = explode("\n", $output);

	$n = count($lines) - 4;
	for ($i = 1; $i <= $n; $i++)
	{
		$channel_list[] = $lines[$i];
	}

	return 0;
}

function pbx_get_peer_statuses()
{
	/*
	65 OK (199 ms)
	SIP-PROVIDER-10758720864848013d90e3b OK (101 ms)
	SIP-PROVIDER-342863348484e7bf9a9d4f OK (119 ms)
*/

	$statuses = array();

	/* sip */
	pbx_exec("sip show statuses", &$raw);
	$lines = explode("\n", $raw);
	foreach ($lines as $line)
	{
		$line = preg_split("/\s+/", $line);
		$statuses[$line[0]] = $line[1];
	}
	unset($raw);

	/* iax */
	pbx_exec("iax2 show statuses", &$raw);
	$lines = explode("\n", $raw);
	foreach ($lines as $line)
	{
		$line = preg_split("/\s+/", $line);
		$statuses[$line[0]] = $line[1];
	}
	unset($raw);

	/* isdn */

	/* analog */

	return $statuses;
}

/**
* Replace a string's contents with the name equivalents of the uniqids contained within.
*/
function pbx_replace_uniqids_with_names($string)
{
	$uniqid_regex = "/(SIP|IAX|ISDN|ANALOG|SKINNY|CONFERENCE|CALLGROUP|EXTERNAL){1}-(PROVIDER|PHONE|ROOM|PARALLEL){1}-[0-9a-zA-Z]+/";

	preg_match_all($uniqid_regex, $string, $uniqids);
	if (!is_array($uniqids))
	{
		return;
	}

	$uniqids = array_keys(array_flip($uniqids[0]));

	$names = array();
	$n = count($uniqids);
	for ($i = 0; $i < $n; $i++)
	{
		$names[$i] = pbx_uniqid_to_name($uniqids[$i]);
	}

	return str_replace($uniqids, $names, $string);
}

/**
* Returns an appropriate "name" for the supplied uniqid or array of uniqids
*/
function pbx_uniqid_to_name($uniqid)
{
	if (is_array($uniqid))
	{
		$n = count($uniqid);
		for ($i = 0; $i < $n; $i++)
		{
			$uniqid[$i] = pbx_uniqid_to_name_sub($uniqid[$i]);
		}
		return $uniqid;
	}
	else
	{
		return pbx_uniqid_to_name_sub($uniqid);
	}
}

function pbx_uniqid_to_name_sub($uniqid)
{
	global $config, $uniqid_map;

	$pair = explode("-", substr(strtolower($uniqid), 0 , strrpos($uniqid, "-")));

	if ($pair[1] == "phone" && $pair[0] == "external")
	{
		return $config[$pair[0]][$pair[1]][$uniqid_map[$uniqid]]['callerid'];
	}
	else if ($pair[1] == "phone")
	{
		return $config[$pair[0]][$pair[1]][$uniqid_map[$uniqid]]['callerid'];
	}
	if ($pair[1] == "provider")
	{
		return $config[$pair[0]][$pair[1]][$uniqid_map[$uniqid]]['name'];
	}
	if ($pair[1] == "room")
	{
		return $config[$pair[0]][$pair[1]][$uniqid_map[$uniqid]]['name'];
	}
	// possible bug here, double check needed!! should actually by $pair[0];
	if ($pair[1] == "callgroup")
	{
		return $config[$pair[0]][$pair[1]][$uniqid_map[$uniqid]]['name'];
	}

	if ($pair[0] == "dahdiport")
	{
		return $config['interfaces']['dahdi-port'][$uniqid_map[$uniqid]]['name'];
	}
	if ($pair[0] == "dahdiportgroup")
	{
		return $config['interfaces']['dahdi-portgroup'][$uniqid_map[$uniqid]]['name'];
	}

	return "ERROR:UNIQID_TO_NAME_FAILED($uniqid)";
}

/**
* Returns the channel technology associated with the supplied uniqid.
*/
function pbx_uniqid_to_technology($uniqid)
{
	$parts = explode("-", $uniqid);
	switch ($parts[0])
	{
		case "SIP":
			return "SIP";
		case "IAX":
			return "IAX2";
		case "ISDN":
			return "DAHDI";
		case "ANALOG":
			return "DAHDI";
		case "SKINNY":
			return "SKINNY";
	}

	return "ERROR:UNIQID_TO_TECHNOLOGY_FAILED($uniqid)";
}

function modules_conf_generate($return = false)
{
	$conf = <<<EOD
[modules]
autoload=no
;
; this is the minimum set of modules to support the
; functionality built into the TeeBX GUI
;
load => app_dial.so
load => app_echo.so
load => app_exec.so
load => app_macro.so
load => app_meetme.so
load => app_milliwatt.so
load => app_playback.so
load => app_setcallerid.so
load => app_stack.so
load => app_system.so
load => app_verbose.so
load => app_voicemail.so
load => app_wakeme.so
load => app_while.so
load => chan_dahdi.so
load => chan_iax2.so
load => chan_local.so
load => chan_sip.so
load => chan_skinny.so
load => codec_a_mu.so
load => codec_adpcm.so
load => codec_alaw.so
load => codec_dahdi.so
load => codec_g722.so
load => codec_gsm.so
load => codec_ulaw.so
load => format_gsm.so
load => format_pcm.so
load => format_sln.so
load => format_wav.so
load => format_wav_gsm.so
load => func_callerid.so
load => func_channel.so
load => func_config.so
load => func_cut.so
load => func_db.so
load => func_logic.so
load => func_md5.so
load => func_shell.so
load => func_strings.so
load => pbx_config.so
load => pbx_loopback.so
load => pbx_spool.so
load => res_adsi.so
load => res_agi.so
load => res_crypto.so
load => res_limit.so
load => res_musiconhold.so
load => res_qstat.so
load => res_smdi.so

EOD;

	return ($return) ? $conf : util_file_write("/etc/asterisk/modules.conf", $conf);
}

function phpagi_conf_generate($return = false)
{
	$conf = <<<EOD
[phpagi]
debug=false							; enable debuging
error_handler=true					; use internal error handler
;admin=errors@mydomain.com			; mail errors to
;hostname=sip.mydomain.com			; host name of this server
tempdir=/var/asterisk/spool/tmp/	; temporary directory for storing temporary output

[asmanager]
server=localhost					; server to connect to
port=5038							; default manager port
username=admin						; username for login
secret=teebx						; password for login

[fastagi]
setuid=true							; drop privileges to owner of script
basedir=/etc/asterisk/agi-bin/		; path to script folder

[festival]							; text to speech engine
text2wave=/usr/bin/flite_cmu_us_kal	; path to text2wave functionality

[cepstral]							; alternate text to speech engine
swift=/opt/swift/bin/swift			; path to switft binary
voice=David							; default voice

EOD;

	return ($return) ? $conf : util_file_write("/etc/asterisk/phpagi.conf", $conf);
}

function asterisk_conf_generate($return = false)
{
	$datapath = "/offload/asterisk";
	if ($disk = storage_service_is_active("media"))
	{
		$datapath = $disk['mountpoint'] . "/teebx/media";
	}

	$dbpath = "/etc/asterisk/db";
	if ($disk = storage_service_is_active("persistence"))
	{
		$dbpath = $disk['mountpoint'] . "/teebx/persistence";
	}

	$conf = <<<EOD
[directories]
astetcdir => /etc/asterisk
astagidir => /etc/asterisk/agi-bin
astdbdir => $dbpath
astkeydir => /etc/asterisk
astspooldir => /var/asterisk/spool
astrundir => /var/asterisk/run
astlogdir => /var/asterisk/log
astmoddir => /offload/asterisk/modules
astvarlibdir => $datapath
astdatadir => $datapath

[options]
verbose = 3
debug = 0
dumpcore = no
internal_timing = yes
highpriority = yes
nocolor=yes


EOD;

	return ($return) ? $conf : util_file_write("/etc/asterisk/asterisk.conf", $conf);
}

function musiconhold_conf_generate($return = false)
{
	global $config;
	$mohpath = "/offload/asterisk/moh";
	if ($disk = storage_service_is_active("media"))
	{
		if (isset($config['media']['custom-moh']))
		{
			$mohpath = $disk['mountpoint'] . "/teebx/media/moh-custom";
		}
		else
		{
			$mohpath = $disk['mountpoint'] . "/teebx/media/moh";
		}
	}

	$conf = <<<EOD
[default]
mode=files
directory=$mohpath


EOD;

	return ($return) ? $conf : util_file_write("/etc/asterisk/musiconhold.conf", $conf);
}

function logger_conf_generate($return = false)
{
	$astlogopt = '';
	if ($disk = storage_service_is_active("astlogs"))
	{
		$astlogopt = 'full => verbose,notice,warning,error,debug';
	}
	$conf = <<<EOD
[general]
queue_log = no
event_log = no

[logfiles]
syslog.local0 => verbose,notice,warning,error,debug
$astlogopt


EOD;

	return ($return) ? $conf : util_file_write("/etc/asterisk/logger.conf", $conf);
}

function udptl_conf_generate($return = false)
{
	$conf = <<<EOD
[general]
udptlstart=4500
udptlend=4599
udptlfecentries=3
udptlfecspan=3
use_even_ports=no


EOD;

	return ($return) ? $conf : util_file_write("/etc/asterisk/udptl.conf", $conf);
}

function cdr_conf_generate($return = false)
{
	$conf = <<<EOD
[general]
enable=yes

[csv]
usegmtime=no
loguniqueid=no
loguserfield=no


EOD;

	return ($return) ? $conf : util_file_write("/etc/asterisk/cdr.conf", $conf);
}

function cdr_custom_conf_generate($return = false)
{
	$conf = <<<EOD
[mappings]
Master.csv => "\${CDR(clid)}","\${CDR(src)}","\${CDR(dst)}","\${CDR(dcontext)}","\${CDR(channel)}","\${CDR(dstchannel)}","\${CDR(lastapp)}","\${CDR(lastdata)}","\${CDR(start)}","\${CDR(answer)}","\${CDR(end)}","\${CDR(duration)}","\${CDR(billsec)}","\${CDR(disposition)}","\${CDR(amaflags)}","\${CDR(accountcode)}","\${CDR(uniqueid)}","\${CDR(userfield)}"


EOD;

	return ($return) ? $conf : util_file_write("/etc/asterisk/cdr_custom.conf", $conf);
}

function amd_conf_generate($return = false)
{
	$conf = <<<EOD
[general]
initial_silence = 2500
greeting = 1500
after_greeting_silence = 800
total_analysis_time = 5000
min_word_length = 100
between_words_silence = 50
maximum_number_of_words = 3
silence_threshold = 256


EOD;

	return ($return) ? $conf : util_file_write("/etc/asterisk/amd.conf", $conf);
}

function enum_conf_generate($return = false)
{
	$conf = <<<EOD
[general]


EOD;

	return ($return) ? $conf : util_file_write("/etc/asterisk/enum.conf", $conf);
}

function say_conf_generate($return = false)
{
	$conf = <<<EOD
[general]
mode=old


EOD;

	return ($return) ? $conf : util_file_write("/etc/asterisk/say.conf", $conf);
}

function smdi_conf_generate($return = false)
{
	$conf = <<<EOD
[interfaces]


EOD;

	return ($return) ? $conf : util_file_write("/etc/asterisk/smdi.conf", $conf);
}

function sip_notify_conf_generate($return = false)
{
	$conf = <<<EOD
[aastra-check-cfg]
Event=>check-sync

[aastra-xml]
Event=>aastra-xml

[linksys-cold-restart]
Event=>reboot_now

[linksys-warm-restart]
Event=>restart_now

[polycom-check-cfg]
Event=>check-sync

[sipura-check-cfg]
Event=>resync

[sipura-get-report]
Event=>report

[snom-check-cfg]
Event=>check-sync\;reboot=false

[snom-reboot]
Event=>reboot

[cisco-check-cfg]
Event=>check-sync


EOD;

	return ($return) ? $conf : util_file_write("/etc/asterisk/sip_notify.conf", $conf);
}

/* boilerplate functions */
function adsi_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/adsi.conf", $conf);
}
function adtranvofr_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/adtranvofr.conf", $conf);
}
function agents_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/agents.conf", $conf);
}
function ais_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/ais.conf", $conf);
}
function alarmreceiver_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/alarmreceiver.conf", $conf);
}
function alsa_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/alsa.conf", $conf);
}
function cdr_adaptive_odbc_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/cdr_adaptive_odbc.conf", $conf);
}
function cdr_manager_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/cdr_manager.conf", $conf);
}
function cdr_odbc_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/cdr_odbc.conf", $conf);
}
function cdr_pgsql_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/cdr_pgsql.conf", $conf);
}
function cdr_sqlite3_custom_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/cdr_sqlite3_custom.conf", $conf);
}
function cdr_tds_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/cdr_tds.conf", $conf);
}
function chan_ooh323_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/chan_ooh323.conf", $conf);
}
function cli_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/cli.conf", $conf);
}
function cli_aliases_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/cli_aliases.conf", $conf);
}
function cli_permissions_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/cli_permissions.conf", $conf);
}
function codecs_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/codecs.conf", $conf);
}
function console_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/console.conf", $conf);
}
function dbsep_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/dbsep.conf", $conf);
}
function dnsmgr_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/dnsmgr.conf", $conf);
}
function dsp_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/dsp.conf", $conf);
}
function dundi_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/dundi.conf", $conf);
}
function extconfig_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/extconfig.conf", $conf);
}
function extensions_minivm_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/extensions_minivm.conf", $conf);
}
function festival_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/festival.conf", $conf);
}
function followme_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/followme.conf", $conf);
}
function func_odbc_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/func_odbc.conf", $conf);
}
function gtalk_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/gtalk.conf", $conf);
}
function h323_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/h323.conf", $conf);
}
function http_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/http.conf", $conf);
}
function iaxprov_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/iaxprov.conf", $conf);
}
function jabber_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/jabber.conf", $conf);
}
function jingle_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/jingle.conf", $conf);
}
function mgcp_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/mgcp.conf", $conf);
}
function minivm_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/minivm.conf", $conf);
}
function misdn_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/misdn.conf", $conf);
}
function muted_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/muted.conf", $conf);
}
function osp_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/osp.conf", $conf);
}
function oss_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/oss.conf", $conf);
}
function phone_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/phone.conf", $conf);
}
function phoneprov_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/phoneprov.conf", $conf);
}
function queuerules_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/queuerules.conf", $conf);
}
function queues_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/queues.conf", $conf);
}
function res_config_sqlite_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/res_config_sqlite.conf", $conf);
}
function res_ldap_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/res_ldap.conf", $conf);
}
function res_odbc_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/res_odbc.conf", $conf);
}
function res_pgsql_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/res_pgsql.conf", $conf);
}
function res_snmp_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/res_snmp.conf", $conf);
}
function sla_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/sla.conf", $conf);
}
function unistim_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/unistim.conf", $conf);
}
function usbradio_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/usbradio.conf", $conf);
}
function users_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/users.conf", $conf);
}
function vpb_conf_generate($return = false)
{
	$conf = "";

	return ($return) ? $conf : util_file_write("/etc/asterisk/vpb.conf", $conf);
}

?>