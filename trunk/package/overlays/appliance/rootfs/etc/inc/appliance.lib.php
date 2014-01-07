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

// this will replace pbx.inc but at this time mostly is a bridge to it

require_once 'pbx.inc';

function startIpbx()
{
	exec('/usr/sbin/asterisk', $discard, $result);
	return $result;
}

function stopIpbx()
{
	exec('/usr/sbin/asterisk -rx \'core stop now\'', $discard, $result);
	sleep(1);
	return $result;
}

function configAsterisk()
{
	asterisk_conf_generate();
}

function configCdr()
{

}

function reloadCdr()
{

}

