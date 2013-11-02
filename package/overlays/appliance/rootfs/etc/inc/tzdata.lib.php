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

	function getTzData($tzCompositeId = false)
	{
		// define our tz index array
		$tzData = array(
			'10' => array(
				'group' => _('Asia'),
				'000' => array(_('Jakarta'), 'WIB-7', 'Asia/Jakarta'),
				'500' => array(_('Singapore'), 'SGT-8', 'Asia/Singapore'),
				'900' => array(_('Ulaanbaatar, Mongolia'), 'ULAT-8ULAST,M3.5.0/2,M9.5.0/2', 'Asia/Ulaanbaatar')
			),
			'20' => array(
				'group' => _('Australia'),
				'000' => array(_('Adelaide'), 'CST-9:30CDT-10:30,M10.5.0/02:00:00,M3.5.0/03:00:00', 'Australia/Adelaide'),
				'100' => array(_('Brisbane'), 'EST-10', 'Australia/Brisbane'),
				'200' => array(_('Canberra'), 'EST-10EDT-11,M10.5.0/02:00:00,M3.5.0/03:00:00', 'Australia/Canberra'),
				'300' => array(_('Darwin'), 'CST-9:30', 'Australia/Darwin'),
				'400' => array(_('Hobart'), 'EST-10EDT-11,M10.1.0/02:00:00,M3.5.0/03:00:00', 'Australia/Hobart'),
				'500' => array(_('Melbourne'), 'EST-10EDT-11,M10.5.0/02:00:00,M3.5.0/03:00:00', 'Australia/Melbourne'),
				'600' => array(_('Perth'), 'WST-8', 'Australia/Perth'),
				'700' => array(_('Sydney'), 'EST-10EDT-11,M10.5.0/02:00:00,M3.5.0/03:00:00', 'Australia/Sydney')
			),
			'30' => array(
				'group' => _('Central and South America'),
				'000' => array(_('Argentina'), 'UTC+3', 'America/Argentina/Buenos_Aires'),
				'500' => array(_('Brazil, São Paulo'), 'BRST+3BRDT+2,M10.3.0,M2.3.0', 'America/Sao_Paulo'),
				'800' => array(_('Central America'), 'CST+6', 'CST6CDT')
			),
			'40' => array(
				'group' => _('Europe'),
				'000' => array(_('Amsterdam, Netherlands'), 'CET-1CEST-2,M3.5.0/02:00:00,M10.5.0/03:00:00', 'Europe/Amsterdam'),
				'010' => array(_('Athens, Greece'), 'EET-2EEST-3,M3.5.0/03:00:00,M10.5.0/04:00:00', 'Europe/Athens'),
				'020' => array(_('Barcelona, Spain'), 'CET-1CEST-2,M3.5.0/02:00:00,M10.5.0/03:00:00', 'Europe/Berlin'),
				'030' => array(_('Berlin, Germany'), 'CET-1CEST-2,M3.5.0/02:00:00,M10.5.0/03:00:00', 'Europe/Berlin'),
				'040' => array(_('Brussels, Belgium'), 'CET-1CEST-2,M3.5.0/02:00:00,M10.5.0/03:00:00', 'Europe/Brussels'),
				'050' => array(_('Budapest, Hungary'), 'CET-1CEST-2,M3.5.0/02:00:00,M10.5.0/03:00:00', 'Europe/Budapest'),
				'060' => array(_('Copenhagen, Denmark'), 'CET-1CEST-2,M3.5.0/02:00:00,M10.5.0/03:00:00', 'Europe/Copenhagen'),
				'070' => array(_('Dublin, Ireland'), 'GMT+0IST-1,M3.5.0/01:00:00,M10.5.0/02:00:00', 'Europe/Dublin'),
				'080' => array(_('Geneva, Switzerland'), 'CET-1CEST-2,M3.5.0/02:00:00,M10.5.0/03:00:00', 'Europe/Copenhagen'),
				'090' => array(_('Helsinki, Finland'), 'EET-2EEST-3,M3.5.0/03:00:00,M10.5.0/04:00:00', 'Europe/Helsinki'),
				'100' => array(_('Kiev, Ukraine'), 'EET-2EEST,M3.5.0/3,M10.5.0/4', 'Europe/Kiev'),
				'110' => array(_('Lisbon, Portugal'), 'WET-0WEST-1,M3.5.0/01:00:00,M10.5.0/02:00:00', 'Europe/Lisbon'),
				'120' => array(_('London, Great Britain'), 'GMT+0BST-1,M3.5.0/01:00:00,M10.5.0/02:00:00', 'Europe/London'),
				'130' => array(_('Madrid, Spain'), 'CET-1CEST-2,M3.5.0/02:00:00,M10.5.0/03:00:00', 'Europe/Madrid'),
				'140' => array(_('Oslo, Norway'), 'CET-1CEST-2,M3.5.0/02:00:00,M10.5.0/03:00:00', 'Europe/Oslo'),
				'150' => array(_('Paris, France'), 'CET-1CEST-2,M3.5.0/02:00:00,M10.5.0/03:00:00', 'Europe/Paris'),
				'160' => array(_('Prague, Czech Republic'), 'CET-1CEST-2,M3.5.0/02:00:00,M10.5.0/03:00:00', 'Europe/Prague'),
				'170' => array(_('Rome, Italy'), 'CET-1CEST-2,M3.5.0/02:00:00,M10.5.0/03:00:00', 'Europe/Rome'),
				'180' => array(_('Moscow, Russia'), 'MSK-3MSD,M3.5.0/2,M10.5.0/3', 'Europe/Moscow'),
				'190' => array(_('Stockholm, Sweden'), 'CET-1CEST-2,M3.5.0/02:00:00,M10.5.0/03:00:00', 'Europe/Stockholm'),
				'200' => array(_('Tallinn, Estonia'), 'EET-2EEST-3,M3.5.0/03:00:00,M10.5.0/04:00:00', 'Europe/Tallinn')
			),
			'50' => array(
				'group' => _('New Zealand'),
				'000' => array(_('Auckland'), 'NZST-12NZDT-13,M10.1.0/02:00:00,M3.3.0/03:00:00', 'Pacific/Auckland'),
				'500' => array(_('Wellington'), 'NZST-12NZDT-13,M10.1.0/02:00:00,M3.3.0/03:00:00', 'Pacific/Auckland')
			),
			'60' => array(
				'group' => _('USA & Canada'),
				'000' => array(_('Alaska Time'), 'AKST9AKDT', 'US/Alaska'),
				'200' => array(_('Atlantic Time'), 'AST4ADT', 'Canada/Atlantic'),
				'300' => array(_('Central Time'), 'CST6CDT', 'US/Central'),
				'400' => array(_('Eastern Time'), 'EST5EDT', 'US/Eastern'),
				'500' => array(_('Hawaii Time'), 'HAW10', 'US/Hawaii'),
				'600' => array(_('Mountain Time'), 'MST7MDT', 'US/Mountain'),
				'700' => array(_('Mountain Time (Arizona, no DST)'), 'MST7', 'US/Arizona'),
				'800' => array(_('Newfoundland Time'), 'NST+3:30NDT+2:30,M4.1.0/00:01:00,M10.5.0/00:01:00', 'Canada/Newfoundland')
			),
			'70' => array(
				'group' => _('Pacific Time'),
				'010' => array(_('Pacific Time'), 'PST8PDT', 'US/Pacific')
			),
			'99' => array(
				'group' => _('Default'),
				'000' => array(_('Coordinated Universal Time (UTC)'), 'UTC', 'UTC')
			)
		);
		//
		if ($tzCompositeId === false)
		{
			return $tzData;
		}
		//
		$grpKey = substr($tzCompositeId, 0, 2);
		$tzKey = substr($tzCompositeId, 2, 3);
		if (!isset($tzData[$grpKey][$tzKey]))
		{
			$grpKey = '99';
			$tzKey = '000';
		}
		return array('label' => $tzData[$grpKey][$tzKey][0],
			'rule' => $tzData[$grpKey][$tzKey][1],
			'name' => $tzData[$grpKey][$tzKey][2]
		);
	}

?>
