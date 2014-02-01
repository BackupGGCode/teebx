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

	error_reporting(E_ALL);
	ini_set('display_errors', 0);
	ini_set('log_errors', 1);
	ini_set('error_log', '/tmp/php_error.log');

//ini_set(session.gc_maxlifetime, 100);
require 'guiconfig.inc';
require_once '/etc/inc/utils.lib.php';
require '/etc/inc/ddns.def.inc';
require 'include/network.def.inc';
require 'libs-php/cfgform.class.php';
// define some constants referenced in fbegin.inc
define('INCLUDE_FORMSTYLE', true);
define('INCLUDE_TABSFILES', 'jshelper');
define('INCLUDE_JSCRIPTS', 'sys_network.conditionalfields.js');
// page title
$pgtitle = array(_('System'), _('Networking'));
// set pointers to the actual configuration variables
// interface options
$cfgPtr['if'] = &$config['interfaces']['lan']['if'];
$cfgPtr['dhcp'] = &$config['interfaces']['lan']['dhcp'];
$cfgPtr['ipaddr'] = &$config['interfaces']['lan']['ipaddr'];
$cfgPtr['subnet'] = &$config['interfaces']['lan']['subnet'];
$cfgPtr['gateway'] = &$config['interfaces']['lan']['gateway'];
$cfgPtr['spoofmac'] = &$config['interfaces']['lan']['spoofmac'];
// dns options
$cfgPtr['dnsserver'] = &$config['system']['dnsserver'];
// network topology
$cfgPtr['topology'] = &$config['interfaces']['lan']['topology'];
$cfgPtr['extipaddr'] = &$config['interfaces']['lan']['extipaddr'];
$cfgPtr['exthostname'] = &$config['interfaces']['lan']['exthostname'];
// dynamic dns settings
$cfgPtr['hostnameupdatesrc'] = &$config['interfaces']['lan']['hostnameupdatesrc'];
$cfgPtr['dyndnsusername'] = &$config['dyndns']['username'];
$cfgPtr['dyndnspassword'] = &$config['dyndns']['password'];
$cfgPtr['dyndnstype'] = &$config['dyndns']['type'];
$cfgPtr['dyndnsenable'] = &$config['dyndns']['enable'];
$cfgPtr['dyndnswildcard'] = &$config['dyndns']['wildcard'];
// static routes
$cfgPtr['route'] = &$config['system']['staticroutes']['route'];
// get ethernet interfaces list
$netInterfaces = network_get_interfaces();
// temporary variable to check later if static ip address is changed
$oldIpaddr = $netInterfaces[$cfgPtr['if']]['ipaddr'];
// instantiate the config form object
$form = new cfgForm('sys_network.php', 'method=post|name=iform|id=iform');
// initialize form
$form->startWrapper('tab-1');
	$form->startFieldset('fset_lanport', _('Port') . " {$cfgPtr['if']} ({$netInterfaces[$cfgPtr['if']]['mac']})");
		// if address mode selector
		$form->startBlock('rw_usedhcp');
			$form->setLabel(null, _('Settings'), 'dhcp', 'class=labelcol');
			$form->startBlock('rw_usedhcp', 'right');
				$form->setField('dhcp', 'select', 'name=dhcp', false, 'yes');
				$selectOpts['yes'] = _('configured via DHCP client');
				$selectOpts['no'] = _('configured manually');
				$form->setSelectOptFill('dhcp', $selectOpts);
				$form->setFieldOptionsState('dhcp', $cfgPtr['dhcp']);
				unset($selectOpts);
			//
		$form->exitBlock();
		// static ip address and netmask
		$form->startBlock('rw_ipaddr');
			$form->setLabel(null, _('IP Address'), 'ipaddr', 'class=labelcol');
			$form->startBlock('rw_ipaddr', 'right');
				$form->setField('ipaddr', 'text', 'size=15|maxlength=15', false, '192.168.1.222');
				$form->setInputText('ipaddr', $cfgPtr['ipaddr']);
				$form->setValidationFunc('ipaddr', 'validIpAddr');
				// netmask selector
				$form->setLabel(null, _('Subnet') . ' /', 'subnet');
				$form->setField('subnet', 'select', 'name=subnet', false, '24');
				$form->setSelectOptFill('subnet', $netmask);
				$form->setFieldOptionsState('subnet', $cfgPtr['subnet']);
			//
		$form->exitBlock();
		// gateway
		$form->startBlock('rw_gateway');
			$form->setLabel(null, _('Gateway'), 'gateway', 'class=labelcol');
			$form->startBlock('rw_gateway', 'right');
				$form->setField('gateway', 'text', 'size=15|maxlength=15', false, '192.168.1.1');
				$form->setInputText('gateway', $cfgPtr['gateway']);
				$form->setValidationFunc('gateway', 'validIpAddr');
			//
		$form->exitBlock();
		// mac spoofing
		$form->startBlock('rw_spoofmac');
			$form->setLabel(null, _('MAC Address'), 'spoofmac', 'class=labelcol');
			$form->startBlock('rw_spoofmac', 'right');
				$form->setField('spoofmac', 'text', 'size=17|maxlength=17');
				$form->setInputText('spoofmac', $cfgPtr['spoofmac']);
				$form->setBlockHint('hint-spoofmac',
					_('This field can be used to modify (&quot;spoof&quot;) the MAC address of the network interface<br>Enter a MAC address in the following format: xx:xx:xx:xx:xx:xx or leave blank')
				);
			//
		$form->exitBlock();
	$form->exitFieldSet();
	$form->startFieldset('fset_dns', _('DNS Servers'));
		// dns servers
		$form->startBlock('rw_dnsserver');
			$form->setLabel(null, _('IP Addresses'), 'dnsserver', 'class=labelcol');
			$form->startBlock('rw_dnsserver', 'right');
			$form->startWrapper('rw_dnsopts', 'controls');
				$form->startWrapper('dnstpl', 'cloneable', 'class=cloneable');
					$form->setField('dnsremove', 'button', 'class=dnsremove removeitem|value=', false);
					$form->setField('dnsserver', 'text', 'disabled=disabled|size=15|maxlength=15', false);
					$form->setValidationFunc('dnsserver', 'validIpAddr');
				$form->exitWrapper();
				$form->clonePrevWrapper('dnsserver', $cfgPtr['dnsserver'], 'class=dnsclone');
			$form->exitWrapper();
			$form->startWrapper('rw_adddnsbtn', 'controls');
				$form->setField('adddns', 'button', 'class=additem|value=');
			$form->exitWrapper();
			//
		$form->exitBlock();
	$form->exitFieldSet();
$form->exitWrapper();
$form->startWrapper('tab-2');
	$form->startFieldset('fset_topo', _('Topology'));
		// network topology
		$form->startBlock('rw_topology');
			$form->setLabel(null, _('Topology'), 'topology', 'class=labelcol');
			$form->startBlock('rw_topology', 'right');
				$form->setField('topology', 'select', 'name=topology');
				$selectOpts['public'] = _('Public IP address');
				$selectOpts['natstatic'] = _('NAT + static public IP');
				$selectOpts['natdynamichost'] = _('NAT + dynamic public IP');
				$form->setSelectOptFill('topology', $selectOpts);
				$form->setFieldOptionsState('topology', $cfgPtr['topology']);
				unset($selectOpts);
				$form->setBlockHint('hint-topology',
					'<ul>' .
					'<li>' . _('Public IP Address: this PBX has a routable IP address (entered above)') . '</li>'.
					'<li>' . _('NAT + Static Public IP: this PBX is behind a NAT which has a static public IP. Enter this IP below.') . '</li>' .
					'<li>' . _('NAT + Dynamic Public IP: this PBX is behind a NAT which has a dynamic public IP. A hostname, constantly updated to point to this network is required. Enter this information below.') . '</li>' .
					'</ul>'
				);
			//
		$form->exitBlock();
		// public ip address
		$form->startBlock('rw_extipaddr');
			$form->setLabel(null, _('Static Public IP'), 'extipaddr', 'class=labelcol');
			$form->startBlock('rw_extipaddr', 'right');
				$form->setField('extipaddr', 'text', 'size=15|maxlength=15');
				$form->setInputText('extipaddr', $cfgPtr['extipaddr']);
			//
		$form->exitBlock();
		// public host name
		$form->startBlock('rw_exthostname');
			$form->setLabel(null, _('Public Hostname'), 'exthostname', 'class=labelcol');
			$form->startBlock('rw_exthostname', 'right');
				$form->setField('exthostname', 'text', 'size=32', true);
				$form->setInputText('exthostname', $cfgPtr['exthostname']);
				$form->setLabel(null, _('This information should be updated by:'), null, null, true);
				$form->setField('hostnameupdatesrc', 'radio', null, false, 'router');
				$form->setRadioItems('hostnameupdatesrc',
					'router=' . _('My Router') . '|' .
					'local=' . _('This system'),
					true
				);
				$form->setCbState('hostnameupdatesrc', $cfgPtr['hostnameupdatesrc']);
			//
		$form->exitBlock();
	$form->exitFieldSet();
	$form->startFieldset('fset_dyndns', _('Dynamic DNS Client'));
		// dynamic dns
		$form->startBlock('rw_service');
			$form->setLabel(null, _('Service Type'), 'dyndnstype', 'class=labelcol');
			$form->startBlock('rw_service', 'right');
				$form->setField('dyndnstype', 'select', 'name=dyndnstype');
				$form->setSelectOptFill('dyndnstype', $ddnsProviders);
				$form->setFieldOptionsState('dyndnstype', $cfgPtr['dyndnstype']);
			//
		$form->exitBlock();
		// ddns username
		$form->startBlock('rw_dyndnsusername');
			$form->setLabel(null, _('Username'), 'dyndnsusername', 'class=labelcol');
			$form->startBlock('rw_dyndnsusername', 'right');
				$form->setField('dyndnsusername', 'text', 'size=32');
				$form->setInputText('dyndnsusername', $cfgPtr['dyndnsusername']);
			//
		$form->exitBlock();
		// ddns password
		$form->startBlock('rw_dyndnspassword');
			$form->setLabel(null, _('Password'), 'dyndnspassword', 'class=labelcol');
			$form->startBlock('rw_dyndnspassword', 'right');
				$form->setField('dyndnspassword', 'text', 'size=32');
				$form->setInputText('dyndnspassword', $cfgPtr['dyndnspassword']);
			//
		$form->exitBlock();
		// ddns domain wildcards
		$form->startBlock('rw_dyndnswildcard');
			$form->setLabel(null, _('Wildcards'), 'dyndnswildcard', 'class=labelcol');
			$form->startBlock('rw_dyndnswildcard', 'right');
				$form->setField('dyndnswildcard', 'checkbox');
				$form->setCbItems('dyndnswildcard', 'yes=' . _('Yes, alias "*.hostname.domain" to hostname specified above.'), true);
				$form->setCbState('dyndnswildcard', $cfgPtr['dyndnswildcard']);
			//
		$form->exitBlock();
	$form->exitFieldSet();
$form->exitWrapper();
$form->startWrapper('tab-3');
	$form->startFieldset('fset_routes', _('Static routes'));
	// static routes
		$form->startBlock('rw_route');
			$form->setLabel(null, '#1', 'routeaddress', 'class=labelcol');
			$form->startBlock('rw_route', 'right');
			$form->startWrapper('rw_sets', 'controls');
				//
				$form->startWrapper('routetpl', 'cloneable', 'class=cloneable');
					$form->setField('remove', 'button', 'class=remove removeitem|value=', false);
					$form->setLabel(null, _('Address'), 'raddress');
					$form->setField('raddress', 'text', 'disabled=disabled|size=17', false);
					$form->setValidationFunc('raddress', 'validIpAddr');
					$form->setLabel(null, _('Subnet') . ' /', 'rsubnet');
					$form->setField('rsubnet', 'select', 'disabled=disabled', true, '24');
					$form->setSelectOptFill('rsubnet', $netmask);
					$form->setLabel(null, _('Gateway'), 'rgateway');
					$form->setField('rgateway', 'text', 'disabled=disabled|size=17', false);
					$form->setLabel(null, _('Interface'), 'rdev');
					$form->setField('rdev', 'select', 'disabled=disabled', false);
					$form->setSelectOptFill('rdev', network_get_interface_names());
					$form->setValidationFunc('rgateway', 'validIpAddr');
					$form->setRequired('rgateway', _('Gateway'), 'rdev=__NONULL__');
					$form->setRequired('raddress', _('Address'), 'rdev=__NONULL__');
				$form->exitWrapper();
				$form->clonePrevWrapper('route', $cfgPtr['route']);
				//
				unset($selectOpts);
				//
			$form->exitWrapper();
			$form->startWrapper('rw_addroutebtn', 'controls');
				$form->setField('addrow', 'button', 'class=additem|value=');
			$form->exitWrapper();
		$form->exitBlock();
	$form->exitFieldSet();
$form->exitWrapper();
$form->setField('submit', 'submit', 'value=' . _('Save'));
// set required fields
$form->setRequired('ipaddr', _('IP Address'), 'dhcp=no');
$form->setRequired('subnet', _('Subnet'), 'dhcp=no');
$form->setRequired('gateway', _('Gateway'), 'dhcp=no');
$form->setRequired('extipaddr', _('Static Public IP'), 'topology=natstatic');
$form->setRequired('exthostname', _('Public Hostname'), 'topology=natdynamichost');
$form->setRequired('dyndnsusername', _('Dynamic DNS Client') . ': ' . _('Username'), 'hostnameupdatesrc=local');
$form->setRequired('dyndnspassword', _('Dynamic DNS Client') . ': ' . _('Password'), 'hostnameupdatesrc=local');

//
if ($_POST)
{
	// validate user input
	$form->validForm($form);
	// check no errors collected...
	$input_errors = $form->get_errQueue();
	//
	if (count($input_errors) == 0)
	{
		$form->getSubmitValues($cfgPtr, null, null, 'ipaddr,subnet,gateway');
		$retval = 0;
		write_config();

		if (!file_exists($d_sysrebootreqd_path))
		{
			config_lock();
			// update network interface settings
			services_dyndns_reset();
			$retval = system_resolvconf_generate();
			// if user changed the appliance ip address try to redirect it to the new one
			if ($retval == 0)
			{
				if ($config['interfaces']['lan']['dhcp'] == 'no')
				{
					if ($config['interfaces']['lan']['ipaddr'] != $oldIpaddr)
					{
						define('REDIRECT_REQ', "http://{$config['interfaces']['lan']['ipaddr']}{$_SERVER['REQUEST_URI']}");
						define('REDIRECT_DLY', 1);
						define('CONTENT_TOP', '<a href="' . REDIRECT_REQ . '">' .
							_('Click here to') .' ' .
							_('access the web UI using the new IP address') .
							'</a>');
						define('FILL_FORCE_FLUSH', true);
						include('include/blankpagetpl.php');
					}
				}
			}
			$retval |= network_lan_configure();
			$retval |= services_dyndns_configure();
			// update applications that depends on network settings

			//
			config_unlock();
		}
		//
		$savemsg = get_std_save_message($retval);
	}
}
// stop here if redirected
if (defined('REDIRECT_REQ'))
{
	exit;
}
// render the page content
require('fbegin.inc');
// prepare logical groups to show tabs
$arrTabs[] = array('url' => '#tab-1', 'label' => _('Interface') . '/' . _('Dns'));
$arrTabs[] = array('url' => '#tab-2', 'label' => _('Topology') . '/' . _('Dynamic DNS'));
$arrTabs[] = array('url' => '#tab-3', 'label' => _('Routing'));
getTabs($arrTabs, true);
// wrap the form to make tabs working
$form->set_formTpl('<div id="sets">' . $form->get_formTpl() . '</div>');
// render form
$form->renderForm();
$msg = _('After you click &quot;Save&quot;, all current calls will be dropped. You may also have to do one or more of the following steps before you can access your PBX again:') .
	'<ul><li>' . _('restart system'). '</li>' .
	'<li>' . _('change the IP address of your computer') . '</li>' .
	'<li>' . _('access the web UI using the new IP address') . '</li></ul>';
showSaveWarning($msg);
require('fend.inc');
?>
