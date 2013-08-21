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

function cfgFileWrite($fileName, &$fileLines, $keySep = ' ', $chgMode = '0600')
{
	// open the target file in write mode
	$fHandle = fopen($fileName, 'w');
	if (!$fHandle)
	{
		return 10;
	}
	/* if $fileLines is an array, assume that each element is a line
		 of key ($keySep) value.
	*/
	if (is_array($fileLines))
	{
		foreach (array_keys($fileLines) as $cfgKey)
		{
			fwrite($fHandle, $cfgKey . $keySep . $fileLines[$cfgKey] . PHP_EOL);
		}
	}
	else
	{
		fwrite($fHandle, $fileLines);
	}
	//
	fclose($fHandle);
	chmod($fileName, $chgMode);
	return 0;
}

function cfgFileRead($fileName, $keySep = ' ', $trimTokens = false)
{
	if(!file_exists($fileName))
	{
		return false;
	}
	//
	$result = array();
	$fileLines = file($fileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	if (is_array($fileLines))
	{
		foreach (array_keys($fileLines) as $lineNum)
		{
			$tokens = explode($keySep, $fileLines[$lineNum]);
			if(count($tokens) == 2)
			{
				if ($trimTokens)
				{
					$tokens[0] = trim($tokens[0]);
					$tokens[1] = trim($tokens[1]);
				}
				$result[$tokens[0]] = $tokens[1];
			}
		}
	}
	return $result;
}

?>
