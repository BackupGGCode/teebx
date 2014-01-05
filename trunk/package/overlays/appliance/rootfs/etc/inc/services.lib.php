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

function getSvcState(&$conf, $svcName)
{
	if (!isset($conf['system']['storage']['services']) || !isset($conf['system']['storage']['services'][$svcName]))
	{
		return false;
	}

	require 'services.def.inc';
	$retval = false;
	if ($conf['system']['storage']['services'][$svcName]['active'] == 1)
	{
		$mnt = $conf['system']['storage']['services'][$svcName]['fsmount'];
		if (isset($conf['system']['storage']['fsmounts'][$mnt]) && $conf['system']['storage']['fsmounts'][$mnt]['active'] == 1)
		{
			$scvTree = $services[$svcName]['dirtree'];
			$retval = "{$conf['system']['storage']['mountroot']}/$mnt/$scvTree";
		}
	}
	return $retval;
}

function initsvcAstmedia($basePath, $arrOpt = null)
{
	if (!is_dir("$basePath/moh"))
	{
		mkdir("$basePath/moh/custom", 0766, true);
		exec("cp -Rp /offload/asterisk/moh/* $basePath/moh/");
	}

	if (!is_dir("$basePath/sounds"))
	{
		mkdir("$basePath/sounds/custom", 0766, true);
		exec("cp -Rp /offload/asterisk/sounds/* $basePath/sounds/");
	}

	return true;
}

function initsvcAstdb($basePath, $arrOpt = null)
{
	if (!is_dir($basePath))
	{
		mkdir($basePath, 0766, true);
		if (is_file('/etc/asterisk/db/astdb'))
		{
			exec("cp -p /etc/asterisk/db/astdb $basePath/");
		}
		// asterisk 1.8+
		elseif (is_file('/etc/asterisk/db/astdb.sqlite3'))
		{
			exec("cp -p /etc/asterisk/db/astdb.sqlite3 $basePath/");
		}
	}
	return true;
}

function initsvcAstcdr($basePath, $arrOpt = null)
{
}

function initsvcAstlogs($basePath, $arrOpt = null)
{
}

function initsvcFax($basePath, $arrOpt = null)
{
}

function initsvcVoicemail($basePath, $arrOpt = null)
{
}

function initsvcSystemlogs($arrStorage, $service)
{
}
?>
