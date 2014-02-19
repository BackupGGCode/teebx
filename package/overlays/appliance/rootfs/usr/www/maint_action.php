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

session_start();

require_once '/etc/inc/utils.lib.php';
require_once '/etc/inc/appliancebone.lib.php';
require_once 'libs-php/uiutils.lib.php';

if (!isset($_SESSION['maint-halt']['token']) || !isset($_POST['stk'])
	|| $_SESSION['maint-halt']['token'] != $_POST['stk'] || !isset($_POST['action']))
{
	include('include/blankpagetpl.php');
	exit();
}

$stopOpts = array();
if (file_exists('/etc/inc/appliance.lib.php'))
{
	include_once '/etc/inc/appliance.lib.php';
	if (is_callable('getStopApplianceOptions'))
	{
		$stopOpts = getStopApplianceOptions();
	}
}

$data = array();
$data['retval'] = null;
$data['msg'] = _('Invalid task or general error.');
$data['action'] = $_POST['action'];

if ($data['action'] === 'reboot' || $data['action'] === 'poweroff')
{
	$data['msg'] = _('The system is rebooting now. Please wait.');
	if ($data['action'] === 'poweroff')
	{
		$data['msg'] = _('The system is shutting down and will be powered off if your platform support it. Please wait.');
	}
	sleep(1);
	doSystemStop($data['action']);
}
elseif ($data['action'] === 'prepare')
{
	foreach (array_keys($stopOpts) as $opKey)
	{
		if (!isset($_POST[$opKey]))
		{
			continue;
		}
		if (is_callable($stopOpts[$opKey]['function']))
		{
			$data['retval'] = call_user_func($stopOpts[$opKey]['function']);
			$data['msg'] = "<input type=\"hidden\" id =\"retval{$opKey}\" value=\"{$data['retval']}\">";
			if ($data['retval'] == $stopOpts[$opKey]['expect'])
			{
				$data['msg'] .= $stopOpts[$opKey]['feedback_done'];
			}
			elseif($stopOpts[$opKey]['mode'] == 'poll')
			{
				$data['msg'] .= $data['retval'] . ' ' . $stopOpts[$opKey]['feedback_wait'];
			}
			// only one task at a time
			break;
		}
	}
}

//$data['msg'] = escapeStr($data['msg']);
exit(json_encode($data));
?>
