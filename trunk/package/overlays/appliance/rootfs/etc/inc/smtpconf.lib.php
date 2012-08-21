<?php
/*
  $Id$
part of BoneOS build platform (http://www.teebx.com/)
Copyright(C) 2010 - 2012 Giovanni Vallesi (http://www.teebx.com).
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

function getSmtpConf(&$cfgPointer)
{
	return $cfgPointer['config']['notifications']['email'];
}

function saveSysSmtpConf($newCfg, &$cfgPointer)
{
	$cfgPointer['config']['notifications']['email'] = $newCfg;
	write_config();
}

function writeSmtpConf(&$cfgPointer)
{
	$retval = 1;
	$smtpConf = getSmtpConf($cfgPointer);
	// just to be sure not to corrupt an existing configuration if we get a null or empty set
	if (is_array($smtpConf))
	{
		if(!empty($smtpConf))
		{
			$newConfig = array();
			//
			$newConfig['account'] = 'default';
			$newConfig['host'] = $smtpConf['host'];
			//
			if (isset($smtpConf['username']))
			{
				if (!empty($smtpConf['username']))
				{
					if (isset($smtpConf['authtype']))
					{
						$newConfig['auth'] = $smtpConf['authtype'];
					}
					$newConfig['user'] = $smtpConf['username'];
					$newConfig['password'] = $smtpConf['password'];
				}
			}
			//
			if (isset($smtpConf['enctype']))
			{
				switch ($smtpConf['enctype'])
				{
					case 'tls':
					{
						$newConfig['tls'] = 'on';
						$newConfig['tls_starttls'] = 'on';
						break;
					}
					case 'smtps':
					{
						$newConfig['tls'] = 'on';
						$newConfig['tls_starttls'] = 'off';
						break;
					}
				}
				if (isset($newConfig['tls']))
				{
					if (isset($smtpConf['disablecertcheck']))
					{
						$newConfig['tls_certcheck'] = 'off';
					}
					else
					{
						$newConfig['tls_certcheck'] = 'on';
						$newConfig['tls_trust_file'] = '/etc/ssl/certs/ca-certificates.crt';
					}
				}
			}
			//
			$newConfig['from'] = $smtpConf['address'];
			$addressParts = explode('@', $smtpConf['address']);
			$newConfig['maildomain'] = $addressParts[1];
			//
			if (isset($smtpConf['port']))
			{
				if (!empty($smtpConf['port']))
				{
					$newConfig['port'] = $smtpConf['port'];
				}
			}
			//
			$newConfig['syslog'] = 'LOG_LOCAL0';
		}
		// write setting to /etc/msmtp.conf
		$retval = cfgFileWrite('/etc/msmtp.conf', $newConfig);
	}
	return $retval;
}

?>
