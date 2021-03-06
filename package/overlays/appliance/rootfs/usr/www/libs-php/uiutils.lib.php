<?php
/*
  $Id$
part of TeeBX(R) VoIP communication platform.
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

- TeeBX Source code is available via svn at [http://code.google.com/p/teebx/source/checkout].
- look at TeeBX website [http://www.teebx.com] to get details about license.
*/

	function escapeStr($in_str, $mode = 'js')
	{
		$out_str = '';
		$len_in_str = strlen($in_str);
		for($i = 0; $i < $len_in_str; $i++)
		{
			$dec = ord(substr($in_str, $i, 1));
			$out_str .= '\\x' . dechex($dec);
		}
		return $out_str;
	}

	function getArrValueByPath($target, $path)
	{
		$result = array(0 => null);
		$path = explode('/', $path);
		$pathCount = count($path);
		for ($x=0; ($x < $pathCount); $x++)
		{
			$key = $path[$x];
			if (isset($target[$key]))
			{
				$target = $target[$key];
				$result[0] = $key;
			}
		}
		$result[1] = $target;
		return $result;
	}

	function setArrValueByPath(&$target, $value, $path = '__AUTONUM__')
	{
		$path = trim($path, '/');
		$path = array_reverse(explode('/', $path));
		$pathCount = count($path);
		for ($x=0; ($x < $pathCount); $x++)
		{
			$key = $path[$x];
			if (!isset($tmpArr))
			{
				if ($key == '__AUTONUM__')
				{
					$tmpArr[] = $value;
				}
				else
				{
					$tmpArr[$key] = $value;
				}
				continue;
			}
			$tmpArr = array($key => $tmpArr);
		}
		$target = array_merge_recursive($target, $tmpArr);
	}

	function pushKeyAfterPos($arrSrc, $srcPos, $insKey, $insVal)
	{
		foreach (array_keys($arrSrc) as $currKey)
		{
			$retval[$currKey] = $arrSrc[$currKey];
			if ($currKey === $srcPos)
			{
				$retval[$insKey] = $insVal;
			}
		}
		return $retval;
	}

	function getNewMixedIndex($baseStr, &$searchArray, $padDigits = 2, $separator = '_', $padChar = '0')
	{
		$index = 1;
		do
		{
			$current = $baseStr . $separator . str_pad((string)$index, $padDigits, $padChar, STR_PAD_LEFT);
			if (!isset($searchArray))
				break;
			//
			$index++;
		}
		while (array_key_exists($current, $searchArray));
		return $current;
	}

?>
