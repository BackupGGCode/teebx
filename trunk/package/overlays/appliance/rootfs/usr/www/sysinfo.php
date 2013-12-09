<?php
/*
  $Id$
part of BoneOS build platform (http://www.teebx.com/)
Copyright(C) 2010 - 2013 Giovanni Vallesi (http://www.teebx.com).
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

include('libs-php/sysinfo.class.php');
$data = array();
$data['retval'] = 1;
// errors array
$data['errors'] = array();
//
if (!isset($_POST['job']))
{
	$data['errors'][] = _('Missing post data!');
	exit(json_encode($data));
}

$sys = new sysInfo();

if (is_array($_POST['job']))
{
	foreach (array_keys($_POST['job']) as $job)
	{
		if ($_POST['job'][$job] == 'net')
		{
			if (isset($_POST['if']))
			{
				if (is_array($_POST['if']))
				{
					foreach (array_keys($_POST['if']) as $iface)
					{
						$sys->getNetIfTraffic($_POST['if'][$iface]);
					}
				}
			}
			continue;
		}
		if ($_POST['job'][$job] == 'netall')
		{
			$sys->getNetStats();
			continue;
		}
	}
}

exit(json_encode($sys->data));
?>
