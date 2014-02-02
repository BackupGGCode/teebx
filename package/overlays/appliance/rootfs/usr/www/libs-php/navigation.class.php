<?php
/*
  $Id$
part of BoneOS build platform (http://www.teebx.com/)
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

- BoneOS source code is available via svn at [http://svn.code.sf.net/p/boneos/code/].
- look at TeeBX website [http://www.teebx.com] to get details about license.
*/

class navigation
{
	public $menu;
	public $menunodeCls;
	public $menucurrCls;
	public $breadcrumbSeparator;
	public $breadcrumbs;
	public $crumbs;
	public $title;

	protected $pathway;
	private $pathLevel;

	public function __construct($navIncludeFile, $titlePrefix = null, $defTitle = null)
	{
		$this->pathway = array();
		$this->menu = '';
		$this->menunodeCls = 'tree_node';
		$this->menucurrCls = 'link_current';
		$this->crumbs = array();
		$this->pathLevel = 1;
		$this->breadcrumbSeparator = '&nbsp;&rsaquo;&rsaquo;&nbsp;';
		$this->breadcrumbs = '';

		require $navIncludeFile;
		$this->pathway = $left_items;
		unset($left_items);

		$this->menu = $this->getMenu($this->pathway);
		if (!isset($this->crumbs['current']))
		{
			$this->crumbs = array();
			if (!empty($defTitle))
			{
				$this->crumbs[] = $defTitle;
			}
		}
		$this->breadcrumbs = join($this->breadcrumbSeparator, $this->crumbs);
		$this->title = $this->breadcrumbs;
		if ($titlePrefix != null)
		{
			$this->title = $titlePrefix . ' - ' . $this->breadcrumbs;
		}
	}

	private function getMenu(&$mItems)
	{
		$list = '<ul>';
		$itemCount = count($mItems);
		for ($i = 0; $i < $itemCount; $i++)
		{
			$matchCurrent = '';
			$hasChilds = false;
			if (is_array($mItems[$i]))
			{
				$hasChilds = isset($mItems[$i]['child']);
				if (isset($mItems[$i]['label']))
				{
					if (isset($mItems[$i]['url']) && (basename($_SERVER['SCRIPT_FILENAME']) == $mItems[$i]['url']))
					{
						$matchCurrent = " class=\"{$this->menucurrCls}\"";
						if (count($this->crumbs) >= $this->pathLevel)
						{
							$this->crumbs = array_slice($this->crumbs, 0, $this->pathLevel-1);
						}
						$this->crumbs['current'] = $mItems[$i]['label'];
					}
					elseif (!isset($this->crumbs['current']))
					{
						$this->crumbs[$this->pathLevel] = $mItems[$i]['label'];
					}
					//
					$li = '<li>';
					// if the current element has childs, apply the node class to the current li tag
					if ($hasChilds)
					{
						$li = '<li class="' . $this->menunodeCls . '">';
					}
					if (!empty($mItems[$i]['url']))
					{
						$list .= $li . "<a{$matchCurrent} href=\"{$mItems[$i]['url']}\">{$mItems[$i]['label']}</a>";
					}
					else
					{
						$list .= $li . $mItems[$i]['label'];
					}
					// now do a recursive call to get child node items if any
					if ($hasChilds)
					{
						$this->pathLevel++;
						$list .= $this->getMenu($mItems[$i]['child']);
						$this->pathLevel--;
					}
					$list .= '</li>';
				}
			}
		}
		$list .= '</ul>';
		return $list;
	}
}


?>
