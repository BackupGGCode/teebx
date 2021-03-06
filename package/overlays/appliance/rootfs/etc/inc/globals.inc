<?php
/*
	$Id$
	part of TeeBX VoIP Communication platform (http://www.teebx.com/)
	Copyright(C) 2010 - 2014 Giovanni Vallesi.
	All rights reserved.

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

$g = array(
	'varrun_path' => '/var/run',
	'varetc_path' => '/var/etc',
	'vardb_path' => '/var/db',
	'varlog_path' => '/var/log',
	'etc_path' => '/etc',
	'tmp_path' => '/tmp',
	'conf_path' => '/conf',
	'conf_default_path' => '/conf.default',
	'cf_path' => '/cf',
	'cf_conf_path' => '/cf/conf',
	'www_path' => '/usr/www',
	'xml_rootobj' => 'teebx',
	'debug' => false,
	'latest_config' => '0.1',
	'package_system' => '1.0',
	'technologies' => array('sip', 'iax', 'isdn', 'analog', 'skinny'),
	'phone_types' => array('sip', 'iax', 'isdn', 'analog', 'skinny', 'external'),
	'fax_types' => array('analog'),
	'provider_types' => array('sip', 'iax', 'isdn', 'analog'),

	'storage_services' => array('media', 'persistence'),

	'dahdi_dirty_path' => '/var/run/dahdi.dirty',
	'analog_dirty_path' => '/var/run/analog.dirty',
	'sip_dirty_path' => '/var/run/sip.dirty',
	'iax_dirty_path' => '/var/run/iax.dirty',
	'isdn_dirty_path' => '/var/run/isdn.dirty',
	'skinny_dirty_path' => '/var/run/skinny.dirty',
	'storage_dirty_path' => '/var/run/storage.dirty',
	'dialplan_dirty_path' => '/var/run/dialplan.dirty',
	'moh_dirty_path' => '/var/run/moh.dirty',
	'vmgreeting_dirty_path' => '/var/run/vmgreeting.dirty',
	'external_dirty_path' => '/var/run/external.dirty',
	//
	'booting' => false
	);

$cfgAppliance = array(
	'pbx' => array(
		'config' => 'config.sdb',
		'fld_label_bck' => _('Pbx'),
		'fld_desc_bck' => _('Pbx configuration'),
	)
);

$defaults = array();

$defaults['dahdi']['echo-taps'] = 128;
$defaults['dahdi']['rxgain'] = 0.0;
$defaults['dahdi']['txgain'] = 0.0;
$defaults['dahdi']['analog']['startsignaling'] = 'ks';
$defaults['dahdi']['bri']['lbo'] = 0;
$defaults['dahdi']['bri']['framing'] = 'ccs';
$defaults['dahdi']['bri']['coding'] = 'ami';

$defaults['dahdi']['loadzones'] = array('us');

$defaults['accounts']['language'] = 'en-us';
$defaults['accounts']['codec'] = array('alaw', 'gsm', 'ulaw');
$defaults['accounts']['qualify'] = 2;

$defaults['accounts']['phones']['ringlength'] = 'indefinitely';
$defaults['accounts']['phones']['default-extension-start'] = 601;
$defaults['accounts']['phones']['callerid'] = 'Default Extension';
$defaults['accounts']['phones']['dialprovider'] = 'sipuri';

$defaults['accounts']['providers']['calleridsource'] = 'phones';

$defaults['sip']['dtmfmode'] = 'auto';
$defaults['sip']['natmode'] = 'yes';

$defaults['iax']['authentication'] = 'md5';

$defaults['storage']['system-partition-offset-megabytes'] = 64;
$defaults['storage']['system-media-minimum-size'] = 96;

$defaults['system'] = array();
$defaults['system']['username'] = 'admin';
$defaults['system']['timeservers'] = 'pool.ntp.org';
$defaults['system']['timeupdateinterval'] = '4-hours';
// regional settings defaults
$defaults['system']['regional']['analog']['fxo']['daa'] = 'FCC';

class sysPreset
{
	const PATH_VARRUN = '/var/run';
	const PATH_VARETC = '/var/etc';
	const PATH_VARDB = '/var/db';
	const PATH_VARLOG = '/var/log';
	const PATH_ETC = '/etc';
	const PATH_TMP = '/tmp';
	const PATH_CONF = '/conf';
	const PATH_CONF_DEFAULT = '/conf.default';
	const PATH_CF = '/cf';
	const PATH_CF_CONF = '/cf/conf';
	const PATH_WWW = '/usr/www';
	const XML_ROOTOBJ = 'teebx';
	const LATEST_CONFIG = '0.1';
	const PACKAGE_SYSTEM = '1.0';

	public static $debug = false;
	public static $booting = false;

	public static $cfgAppliance = null;
	public static function cfgAppliance()
	{
		if (self::$cfgAppliance === null)
		{
			self::$cfgAppliance = array(
			'pbx' => array(
				'config' => self::PATH_CF_CONF . '/config.sdb',
				'fld_label_bck' => _('Pbx'),
				'fld_desc_bck' => _('Pbx configuration'),
				)
			);
		}
		return self::$cfgAppliance;
	}

}

?>
