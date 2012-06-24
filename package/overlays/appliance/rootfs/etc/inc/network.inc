<?php
/*
	$Id$
	part of BoneOS build platform (http://www.teebx.com/)
	Copyright(C) 2011 - 2012 Giovanni Vallesi.
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

/* include all configuration functions */
require_once("functions.inc");

/**
* Configures the system's loopback device
*/
function network_loopback_configure()
{
	global $config, $g;

	mwexec("/sbin/ifconfig lo 127.0.0.1");

	return 0;
}

/**
* Configures the system's main LAN interface
*/
function network_lan_configure($linkWaitCount = 20)
{
	global $config, $g;

	$syscfg = $config['system'];
	$lancfg = $config['interfaces']['lan'];
	//
	$lanIf = escapeshellarg($lancfg['if']);

	/* bring interface down */
	mwexec('/sbin/ifconfig ' . $lanIf . ' down');

	/* mac spoofing? */
	if ($lancfg['spoofmac'])
	{
		putenv('ethaddr=' . $lancfg['spoofmac']);
	}

	/* mac environment override? */
	if (verify_is_macaddress(getenv('ethaddr')))
	{
		mwexec('/sbin/ifconfig ' . $lanIf .
			' hw ether ' . escapeshellarg(getenv('ethaddr')));
	}

	/* deconfigure */
	mwexec('/sbin/ifconfig ' . $lanIf . ' 0.0.0.0');
	mwexec('/sbin/route del default gw 0.0.0.0 dev ' . $lanIf);

	/* set address & default route */
	if (isset($lancfg['dhcp']))
	{
		mwexec('/sbin/ifconfig ' . $lanIf . ' up');
		// ask the linux kernel that eth port link is really up,
		// else wait then retry some times
		for ($i = 0; $i < $linkWaitCount; $i++)
		{
			if (netGetIfLinkState($lanIf))
			{
				/*
					udhcp tuning options:
					-t (n): Number of discovery requests to be sent before failing
					-T (n): Time in seconds between discovery requests
					-n: Exit with failure if a lease cannot be obtained
				*/
				mwexec('/sbin/udhcpc -S -q -T 3 -t 5 -n -i ' . $lanIf .
					' -h ' . escapeshellarg("{$syscfg['hostname']}.{$syscfg['domain']}"));
				break;
			}
			$msg = "Waiting for link up on $lanIf: try " . $i + 1;
			msgToSyslog($msg);
			usleep(150000);
		}
	}
	else
	{
		mwexec('/sbin/ifconfig ' . $lanIf . ' ' .
			escapeshellarg($lancfg['ipaddr']) . " " .
			'netmask ' . escapeshellarg(dottedMaskFromBits($lancfg['subnet'])));
		mwexec('/sbin/ifconfig ' . $lanIf . ' up');
		mwexec('/sbin/route add default gw ' . escapeshellarg($lancfg['gateway']) . ' ' .
			'dev ' . $lanIf);
	}

	if (!$g['booting'])
	{
		/* restart ez-ipupdate */
		services_dyndns_configure();

		/* force DNS update *//*
		services_dnsupdate_process();*/
	}

	return 0;
}

/**
* Returns the current public IP address.
*/
function network_get_current_public_ip_address()
{
	global $config, $g;

	$lancfg = $config['interfaces']['lan'];

	if ($lancfg['topology'] == "public")
	{
		return $lancfg['ipaddr'];
	}
	else if ($lancfg['topology'] == "natstatic")
	{
		return $lancfg['extipaddr'];
	}
	else
	{
		return exec("/usr/bin/wget -q -O - http://www.teebx.org/serviceapplet?do=ipdiscovery");
	}
}

function network_get_interface_names()
{
	exec("ls /proc/sys/net/ipv4/conf/ | grep eth", $names);
	return $names;
}

function network_get_interface($name)
{
	$interface = array();
	// get ifconfig's output for this interface
	exec("/sbin/ifconfig $name", $output);
	$output = implode(" ", $output);
	// grab the mac address
	preg_match("/HWaddr (\S+)/", $output, $matches);
	$interface['mac'] = $matches[1];
	// grab the ip address
	preg_match("/inet addr:(\S+)/", $output, $matches);
	$interface['ipaddr'] = $matches[1];
	// grab the subnet
	preg_match("/Mask:(\S+)/", $output, $matches);
	$interface['subnet'] = netmask_to_cidr_bits($matches[1]);
	// is the interface up
	preg_match("/\s+(UP)\s+/", $output, $matches);
	if ($matches[1] == "UP")
	{
		$interface['up'] = true;
	}
	else
	{
		$interface['up'] = false;
	}

	exec('/sbin/route | grep "$name" | grep "default" | cut -d " " -f 10', $gwout);
	if (verify_is_ipaddress($gwout[0]))
	{
		$interface['gateway'] = $gwout[0];
	}

	exec('cat /etc/resolv.conf | grep "nameserver" | cut -d " " -f 2', $dnsout);
	foreach ($dnsout as $line)
	{
		if (verify_is_ipaddress($line))
		{
			$interface['dns'][] = $line;
		}
	}

	return $interface;
}

/**
* Returns a list of interfaces with MAC addresses
*/
function network_get_interfaces()
{
	global $g;
	// get network interface names
	$i_names = network_get_interface_names();
	// interface information array
	$iflist = array();
	// go through each name and populate the interface information array
	foreach ($i_names as $i)
	{
		if (preg_match("/^(eth)\d/", $i))
		{
			$iflist[$i] = network_get_interface($i);
		}
	}
	return $iflist;
}

function netmask_to_cidr_bits($netmask)
{
	$int = ip2long($netmask);
	$int = $int - (($int >> 1) &0x55555555);
	$int = ($int &0x33333333) + (($int >> 2) &0x33333333);
	return (($int + ($int >> 4) &0xF0F0F0F) * 0x1010101) >> 24;
}

/**
* Returns the subnet address given a host address and a subnet bit count
*/
function gen_subnet($ipaddr, $bits)
{
	if (!verify_is_ipaddress($ipaddr) || !is_numeric($bits))
		return "";

	return long2ip(ip2long($ipaddr) &gen_subnet_mask_long($bits));
}

/**
* return the highest (broadcast) address in the subnet given a host address
* and a subnet bit count
*/
function gen_subnet_max($ipaddr, $bits)
{
	if (!verify_is_ipaddress($ipaddr) || !is_numeric($bits))
		return "";

	return long2ip(sprintf('%u', ip2long($ipaddr)) | ~gen_subnet_mask_long($bits));
}

/**
* Returns a subnet mask (long given a bit count)
*/
function gen_subnet_mask_long($bits)
{
	$sm = 0;
	for ($i = 0; $i < $bits; $i++)
	{
		$sm >>= 1;
		$sm |= 0x80000000;
	}
	return $sm;
	/*
	$binval = '';
	for($i=1;$i<=32;$i++) {
	    $binval .= $bits >= $i ? '1' : '0';
	}
	$bitsval = bindec($binval);
	return ip2long($bitsval);*/
}

/**
* Same as gen_subnet_mask_long but returns a string
*/
function gen_subnet_mask($bits)
{
	return long2ip(gen_subnet_mask_long($bits));
}

/**
* Find out whether two subnets overlap
*/
function check_subnets_overlap($subnet1, $bits1, $subnet2, $bits2)
{
	if (!is_numeric($bits1))
		$bits1 = 32;
	if (!is_numeric($bits2))
		$bits2 = 32;

	if ($bits1 < $bits2)
		$relbits = $bits1;
	else
		$relbits = $bits2;

	$sn1 = gen_subnet_mask_long($relbits) &ip2long($subnet1);
	$sn2 = gen_subnet_mask_long($relbits) &ip2long($subnet2);

	if ($sn1 == $sn2)
		return true;
	else
		return false;
}

/**
* Compares two IP addresses
*/
function ipcmp($a, $b)
{
	if (ip2long($a) < ip2long($b))
		return - 1;
	else if (ip2long($a) > ip2long($b))
		return 1;
	else
		return 0;
}

/**
* Returns true if $addr is in $subnet, false if not
*/
function ip_in_subnet($addr, $subnet)
{
	list($ip, $mask) = explode('/', $subnet);
	$mask = 0xffffffff << (32 - $mask);
	return ((ip2long($addr) &$mask) == (ip2long($ip) &$mask));
}

/**
* Obtains MAC address given an IP address by looking at the ARP table
*/
function arp_get_mac_by_ip($ip)
{
	exec("/usr/sbin/arp -n {$ip}", $arpoutput);

	if ($arpoutput[0])
	{
		$arpi = explode(" ", $arpoutput[0]);
		$macaddr = $arpi[3];
		if (verify_is_macaddress($macaddr))
			return $macaddr;
		else
			return false;
	}

	return false;
}

/**
* Returns MAC addresses in a variety of formats
*/
function mac_format($clientmac)
{
	$mac = explode(":", $clientmac);

	global $config;

	$mac_format = $config['captiveportal']['radmac_format'] ? $config['captiveportal']['radmac_format'] : false;

	switch ($mac_format)
	{
		case 'singledash':
			return "$mac[0]$mac[1]$mac[2]-$mac[3]$mac[4]$mac[5]";

		case 'ietf':
			return "$mac[0]-$mac[1]-$mac[2]-$mac[3]-$mac[4]-$mac[5]";

		case 'cisco':
			return "$mac[0]$mac[1].$mac[2]$mac[3].$mac[4]$mac[5]";

		case 'unformatted':
			return "$mac[0]$mac[1]$mac[2]$mac[3]$mac[4]$mac[5]";

		default:
			return $clientmac;
	}
}

/* gv 2011 */

function netFromAddrBits($addr, $bits)
{
	$g = explode(".", $addr);
	$b = '';
	$res = '';
	foreach ($g as $gv)
	{
		$b .= str_pad(decbin($gv), 8, '0', STR_PAD_LEFT);
	}
	$bm = str_split(str_pad(substr($b, 0, $bits), 32, '0', STR_PAD_RIGHT), 8);
	foreach($bm as &$bg)
	{
		$bg = bindec($bg);
	}
	return join('.', $bm);
}

function netFromCIDR($CIDRn)
{
	$a = explode("/", $CIDRn);
	return netFromAddrBits($a[0], $a[1]);
}

function dottedMaskFromBits($bits)
{
	$res = str_split(str_pad(str_pad('', $bits, '1'), 32, '0', STR_PAD_RIGHT), 8);
	foreach($res as &$bg)
	{
		$bg = bindec($bg);
	}
	return join('.', $res);
}

function netGetIfLinkState($ifName = 'eth0')
{
	$linkIsUp = false;
	$cmdResult = array();
	exec("cat /sys/class/net/{$ifName}/carrier", $cmdResult);
	if (isset($cmdResult[0]))
	{
		if ($cmdResult[0] == '1')
		{
			$linkIsUp = true;
		}
	}
	return $linkIsUp;
}

?>
