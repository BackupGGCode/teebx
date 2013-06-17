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
function getMenu(&$mItems, $nodeCls = 'tree_node')
{
	$list = '<ul>';
	$itemCount = count($mItems);
	for ($i = 0; $i < $itemCount; $i++)
	{
		$li = '<li>';
		$hasChilds = false;
		if (is_array($mItems[$i]))
		{
			$hasChilds = isset($mItems[$i]['child']);
			if (isset($mItems[$i]['label']))
			{
				// if the current element has childs, apply the node class to the current li tag
				if ($hasChilds)
				{
					$li = '<li class="' . $nodeCls . '">';
				}
				if (!empty($mItems[$i]['url']))
				{
					$list .= $li . '<a href="' . $mItems[$i]['url'] . '">' . $mItems[$i]['label'] . '</a>';
				}
				else
				{
					$list .= $li . $mItems[$i]['label'];
				}
				// now do a recursive call to get child node items if any
				if ($hasChilds)
				{
					$list .= getMenu($mItems[$i]['child']);
				}
				$list .= '</li>';
			}
		}
	}
	$list .= '</ul>';
	return $list;
}
?>