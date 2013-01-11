<?php
/*
  $Id$
part of TeeBX(R) VoIP communication platform.
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

- TeeBX Source code is available via svn at [http://code.google.com/p/teebx/source/checkout].
- look at TeeBX website [http://www.teebx.com] to get details about license.
*/

// w3c reference: http://www.w3.org/TR/html401/struct/tables.html#h-11.2.3

class htmlTable extends baseTag
{
	private $tblStack = array();
	private $last = NULL;

	protected function appendElement($element)
	{
		if (is_null($this->last))
		{
			// child elements not allowed here, log an error then return
			return;
		}
		$curRow = &$this->tblStack[$this->last];
		$current = $curRow->get_innerElement();
		$innerObj = end($curRow->{$current});
		if (is_object($innerObj) and (get_class($innerObj) != get_class($element)))
		{
			$innerElement = $innerObj->get_innerElement();
			$innerObj->{$innerElement}[] = $element;
		}
		else
		{
			$curRow->{$current}[] = $element;
		}
	}

	public function caption($text, $attributes = NULL)
	{
		$caption = new tableCaption($text, $attributes);
		$this->tblStack[] = $caption;
		// set a reference pointing to the current parent element
		$this->last = count($this->tblStack) - 1;
	}

	public function colgroup($attributes = NULL)
	{
		$colgroup = new tableColgroup($attributes);
		$this->tblStack[] = $colgroup;
		$this->last = count($this->tblStack) - 1;
	}

	public function col($attributes = NULL)
	{
		$col = new tableCol($attributes);
		$this->appendElement($col);
	}

	public function thead($attributes = NULL)
	{
		$head = new tableHead($attributes);
		$this->tblStack[] = $head;
		$this->last = count($this->tblStack) - 1;
	}

	public function tfoot($attributes = NULL)
	{
		$foot = new tableFoot($attributes);
		$this->tblStack[] = $foot;
		$this->last = count($this->tblStack) - 1;
	}

	public function tbody($attributes = NULL)
	{
		$body = new tableBody($attributes);
		$this->tblStack[] = $body;
		$this->last = count($this->tblStack) - 1;
	}

	public function tr($rowAttributes = NULL)
	{
		$row = new tableRow($rowAttributes);
		$this->appendElement($row);
	}

	public function td($data = '', $cellAttributes = NULL)
	{
		$cell = new tableCell($data, $cellAttributes);
		$this->appendElement($cell);
	}

	public function th($data = '', $cellAttributes = NULL)
	{
		$cell = new tableHeadingCell($data, $cellAttributes);
		$this->appendElement($cell);
	}

	public function renderTable($returnHtml = false)
	{
		$tblHtml = '';
		foreach($this->tblStack as $element)
		{
			$tblHtml .= $element->getElementHtml();
		}
		//
		$tblHtml = '<table'. $this->get_attributes() . '>' . $tblHtml . '</table>';
		if ($returnHtml)
		{
			return $tblHtml;
		}
		echo $tblHtml;
	}
}

class tableCaption extends baseTag
{
	protected static $htmlTag = 'caption';
	protected static $innerElement = 'text';

	function __construct($text, $attributes)
	{
		parent::__construct($attributes);
		$this->{static::$innerElement} = $text;
	}
}

class tableBody extends baseTag
{
	protected static $htmlTag = 'tbody';
	protected static $innerElement = 'tr';

	function __construct($attributes)
	{
		parent::__construct($attributes);
		$this->{static::$innerElement} = array();
	}
}

class tableHead extends tableBody
{
	protected static $htmlTag = 'thead';
}

class tableFoot extends tableBody
{
	protected static $htmlTag = 'tfoot';
}

class tableRow extends baseTag
{
	protected static $htmlTag = 'tr';
	protected static $innerElement = 'cells';

	function __construct($rowAttributes)
	{
		parent::__construct($rowAttributes);
		$this->{static::$innerElement} = array();
	}
}

class tableCell extends baseTag
{
	protected static $htmlTag = 'td';
	protected static $innerElement = 'data';

	function __construct($data, $tagAttributes)
	{
		parent::__construct($tagAttributes);
		$this->{static::$innerElement} = $data;
	}
}

class tableHeadingCell extends tableCell
{
	protected static $htmlTag = 'th';
}

class tableColgroup extends baseTag
{
	protected static $htmlTag = 'colgroup';
	protected static $innerElement = 'cols';

	function __construct($attributes)
	{
		parent::__construct($attributes);
		$this->{static::$innerElement} = array();
	}
}

class tableCol extends baseTag
{
	protected static $htmlTag = 'col';
	protected static $closeTag = false;

	function __construct($tagAttributes)
	{
		parent::__construct($tagAttributes);
	}
}


abstract class baseTag
{
	protected static $innerElement = NULL;
	protected static $closeTag = true;
	private $attributes = array();

	public function __construct($tagAttributes)
	{
		if (!is_null($tagAttributes))
		{
			$this->set_attributes($tagAttributes);
		}
	}

	protected static function get_htmlTag()
	{
		return static::$htmlTag;
	}

	protected static function get_innerElement()
	{
		return static::$innerElement;
	}

	protected function parseAttributes($strAttribs)
	{
		$result = array();
		$listAttr = explode('|', $strAttribs);
		foreach ($listAttr as $attr)
		{
			$params = explode('=', $attr);
			if (!empty($params[1]))
			{
				$result[$params[0]] = $params[1];
			}
		}
		return $result;
	}

	protected function set_attributes($tagAttributes)
	{
		if (!is_array($tagAttributes))
			$tagAttributes = $this->parseAttributes($tagAttributes);
		if(!empty($tagAttributes))
		{
			if (empty($this->attributes))
				$this->attributes = $tagAttributes;
			else
				$this->attributes = array_merge($this->attributes, $tagAttributes);
		}
	}

	public function getElementHtml()
	{
		$elemHtml = '<' . static::$htmlTag . $this->get_attributes() . '>';
		if (!is_null(static::$innerElement))
		{
			if (is_array($this->{static::$innerElement}))
			{
				foreach($this->{static::$innerElement} as $innerTag)
				{
					$elemHtml .= $innerTag->getElementHtml();
				}
			}
			else
			{
				$elemHtml .= $this->{static::$innerElement};
			}
		}
		if (static::$closeTag)
		{
			$elemHtml .= '</' . static::$htmlTag . '>';
		}
		return $elemHtml;
	}

	protected function get_attributes()
	{
		$result = '';
		foreach($this->attributes as $attribute => $value)
		{
			$result .= " $attribute=\"$value\"";
		}
		return $result;
	}
}

?>