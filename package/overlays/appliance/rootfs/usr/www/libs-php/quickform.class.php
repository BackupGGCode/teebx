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

class quickForm
{
	// public properties
	public $submitted;
	// protected
	protected $htmlTagsPool;
	protected $htmlForm;
	protected $tagsPool;
	protected $constraints;
	protected $breakAfterFields;
	protected $autoIdSep;
	protected $formTag;
	protected $curFieldset;
	protected $idPrefix;
	// private
	private $htmlTagsPoolReady;
	private $errBuildQueue;
	private $formReady;

	public function __construct()
	{
		$this->htmlTagsPool = '';
		$this->htmlForm = '';
		$this->tagsPool = array();
		$this->constraints = array();
		$this->breakAfterFields = false;
		$this->autoIdSep = '_';
		$this->idPrefix = 'id-';
		$this->formTag = array();
		$this->curFieldset = null;
		$this->submitted = false;

		//
		$this->htmlTagsPoolReady = false;
		$this->errBuildQueue = array();
		$this->formReady = false;
	}

	private function checkTagById($id)
	{
		return isset($this->tagsPool[$id]);
	}

	private function checkTagAttrList($id)
	{
		return isset($this->tagsPool[$id]['attrlist']);
	}

	private function set_errBuildQueue($errCode)
	{
		$this->errBuildQueue[] = $errCode;
	}

	private function get_errBuildQueue()
	{
		if (count($this->errBuildQueue) === 0)
			return 0;
		return $this->errBuildQueue;
	}

	/**
	 * Return the fieldset ID if the field identified by ID belongs to a set of fields, else return NULL
	 *
	 * @param string $id
	 * @return string
	 */
	private function checkFieldset($id)
	{
		if (isset($this->tagsPool[$id]['fieldset']))
		{
			return $this->tagsPool[$id]['fieldset'];
		}
		return null;
	}

	private function setTagAttr($id, $attName, $attValue)
	{
		if (!$this->checkTagAttrList($id))
		{
			$this->tagsPool[$id]['attrlist'] = array();
		}
		$this->tagsPool[$id]['attrlist'][$attName] = $attValue;
	}

	private function setTagAttributes($id, $attributes)
	{
		$listAttr = explode('|', $attributes);
		foreach ($listAttr as $attr)
		{
			$params = explode('=', $attr);
			$this->setTagAttr($id, $params[0], $params[1]);
		}
	}

	private function getAttributes($id, $valueIndex = null, $idSuffix = null)
	{
		$tagAttrib = '';
		$localId = $id;
		if (isset($this->tagsPool[$id]['attrlist']))
		{
			if (is_array($this->tagsPool[$id]['attrlist']))
			{
				foreach ($this->tagsPool[$id]['attrlist'] as $key => $value)
				{
					switch ($key)
					{
						case 'items':
							continue;
							break;
						case 'options':
							continue;
							break;
						case 'value':
							if (!is_array($value))
							{
								$value = htmlspecialchars($value);
							}
							elseif (!is_null($valueIndex))
							{
								$value = $value[$valueIndex];
								$localId = $id . $this->autoIdSep . $valueIndex;
							}
						default:
							$tagAttrib .= sprintf(' %s="%s"', $key, $value);
					}
				}
			}
		}
		unset ($key, $value);
		// tag id can't start with a number, prepend a prefix
		if (is_numeric($id))
			$localId = $this->idPrefix . $localId;
		// append id suffix if set
		if (!is_null($idSuffix))
			$localId = $localId . $this->autoIdSep . $idSuffix;
		return " id=\"$localId\"$tagAttrib";
	}

	private function setTagOptSelected($id, $selectedOpts)
	{
		// target pointer
		$tagOptsPtr = &$this->tagsPool[$id]['attrlist']['options'];
		//
		if (!is_array($selectedOpts))
			$selectedOpts = array($selectedOpts);
		//
		foreach (array_keys($tagOptsPtr) as $opgKey)
		{
			foreach (array_keys($tagOptsPtr[$opgKey]) as $opKey)
			{
				if (in_array($opKey, $selectedOpts))
				{
					$tagOptsPtr[$opgKey][$opKey]['selected'] = 1;
				}
				else
				{
					$tagOptsPtr[$opgKey][$opKey]['selected'] = 0;
				}
			}
		}
	}

	private function setTagOptSelectedSorted($id, $topSortedValues)
	{
		if (!is_array($topSortedValues))
		{
			$topSortedValues = array($topSortedValues);
		}
		$selTagOpts = array();
		$topOptGrp = $this->tagsPool[$id]['keepvaltopsorted'];
		// target pointer
		$tagOptsPtr = &$this->tagsPool[$id]['attrlist']['options'];
		foreach (array_keys($topSortedValues) as $sKey)
		{
			foreach (array_keys($tagOptsPtr) as $gKey)
			{
				if (isset($tagOptsPtr[$gKey][$topSortedValues[$sKey]]))
				{
					$selTagOpts[$topOptGrp][$topSortedValues[$sKey]] = $tagOptsPtr[$gKey][$topSortedValues[$sKey]];
					$selTagOpts[$topOptGrp][$topSortedValues[$sKey]]['selected'] = 1;
					unset ($tagOptsPtr[$gKey][$topSortedValues[$sKey]]);
				}
			}
		}
		if (isset($tagOptsPtr[$topOptGrp]))
		{
			foreach (array_keys($tagOptsPtr[$topOptGrp]) as $opKey)
			{
				$tagOptsPtr[$topOptGrp][$opKey]['selected'] = 0;
				$tagOptsPtr[0][$opKey] = $tagOptsPtr[$topOptGrp][$opKey];
			}
			unset ($tagOptsPtr[$topOptGrp]);
		}
		// merge temp arrays replacing the previous one
		array_multisort($tagOptsPtr[0], SORT_ASC);
		$tagOptsPtr = array_merge($selTagOpts, $tagOptsPtr);
	}

	/**
	 * Set one or more items for a radio or checkbox group
	 *
	 * @param string $id
	 * @param string $items
	 * @return unknown
	 */
	private function setGroupItems(&$id, &$items, &$lblAfter)
	{
		$grpItems = explode('|', $items);
		foreach ($grpItems as $item)
		{
			$itemAttr = explode('=', $item);
			if (count($itemAttr) < 2)
			{
				return 30;
			}
			$currItemSlctd = 0;
			if ($lblAfter)
			{
				$this->tagsPool[$id]['lblafter'] = 1;
			}
			if (!isset($this->tagsPool[$id]['attrlist']['items']))
			{
				$this->tagsPool[$id]['attrlist']['items'] = array();
			}
			if (isset($itemAttr[2]))
			{
				if ($itemAttr[2] == 1)
				{
					$currItemSlctd = 1;
				}
			}
			if (!isset($this->tagsPool[$id]['attrlist']['items'][$itemAttr[0]]))
			{
				$this->tagsPool[$id]['attrlist']['items'][$itemAttr[0]] = array();
			}
			$this->tagsPool[$id]['attrlist']['items'][$itemAttr[0]]['label'] = $itemAttr[1];
			$this->tagsPool[$id]['attrlist']['items'][$itemAttr[0]]['checked'] = $currItemSlctd;
			if (isset($itemAttr[3]))
			{
				$this->tagsPool[$id]['attrlist']['items'][$itemAttr[0]]['disabled'] = 1;
			}
		}
		return 0;
	}

	private function get_rcGroup($idBase, $tplMarkup)
	{
		$tagGrp = '';
		$btnId = 0;
		$fldLbl = '';
		$brk = '';
		$htmlId = null;
		if ($this->breakAfterFields)
		{
			$brk .= '<br>';
		}
		$labelTpl = '<label for="%s">%s</label>';
		// keep id if only one element exists
		if (count($this->tagsPool[$idBase]['attrlist']['items']) === 1)
		{
			$htmlId = $idBase;
			$btnId = null;
		}
		foreach ($this->tagsPool[$idBase]['attrlist']['items'] as $setKey => $opSet)
		{
			$elemState = '';
			if (is_null($htmlId))
			{
				$htmlId = $idBase . $this->autoIdSep . $btnId;
			}
			foreach ($this->tagsPool[$idBase]['attrlist']['items'][$setKey] as $opKey => $opValue)
			{
				if ($opKey == 'label')
				{
					$fldLbl = sprintf($labelTpl, $htmlId, htmlspecialchars($opValue));
					continue;
				}
				if ($opValue == 1)
				{
					if ($opKey == 'checked')
					{
						$elemState .= ' checked="checked"';
						continue;
					}
					if ($opKey == 'disabled')
					{
						$elemState .= ' disabled="disabled"';
						continue;
					}
				}
			}
			$currSet = sprintf($tplMarkup . $brk,
				$setKey,
				$elemState,
				$this->getAttributes($idBase, null, $btnId));
			if (isset($this->tagsPool[$idBase]['lblafter']) and $this->tagsPool[$idBase]['lblafter'] == 1)
			{
				$tagGrp .= $currSet . $fldLbl;
			}
			else
			{
				$tagGrp .= $fldLbl . $currSet;
			}
			$fldLbl = '';
			$htmlId = null;
			$btnId++;
		}
		return $tagGrp;
	}


	protected function getFieldIdByName($fieldName)
	{
		$lastKey = null;
		$arrIterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($this->tagsPool),
			RecursiveIteratorIterator::SELF_FIRST);

		foreach ($arrIterator as $sKey => $sValue)
		{
			if ($arrIterator->getDepth() == 0)
				$lastKey = $sKey;
			// !TODO: should be otmimized to skip most non field elements but filtering the iterator needs php 5.4
			$subArray = $arrIterator->getSubIterator();
			if (isset($subArray['name']))
			{
				if ($subArray['name'] === $fieldName)
				{
					return $lastKey;
				}
			}
		}
		return null;
	}

	protected function getTextareaContent($fldId)
	{
		$text = '';
		if (isset($this->tagsPool[$fldId]['text']))
			$text = $this->tagsPool[$fldId]['text'];
		return $text;
	}

	protected function setTagOptUnselected()
	{
		// reset the checked/selected values for any checkbox/radio/select option element
		foreach (array_keys($this->tagsPool) as $tagKey)
		{
			$elemType = &$this->tagsPool[$tagKey]['elemtype'];
			if (($elemType == 'checkbox') or ($elemType == 'radio'))
			{
				foreach (array_keys($this->tagsPool[$tagKey]['attrlist']['items']) as $elmemKey)
				{
					$this->tagsPool[$tagKey]['attrlist']['items'][$elmemKey]['checked'] = 0;
				}
			}
			elseif ($elemType == 'select')
			{
				// internal array target pointers
				$tagOptsPtr = &$this->tagsPool[$tagKey]['attrlist']['options'];
				if (isset($this->tagsPool[$tagKey]['keepvaltopsorted']))
				{
					$topOptGrp = $this->tagsPool[$tagKey]['keepvaltopsorted'];
					foreach (array_keys($tagOptsPtr) as $opGrpgKey)
					{
						foreach (array_keys($tagOptsPtr[$opGrpgKey]) as $optionKey)
						{
							$tagOptsPtr[$opGrpgKey][$optionKey]['selected'] = 0;
						}
					}
				}
				if (isset($topOptGrp, $tagOptsPtr[$topOptGrp]))
				{
					foreach (array_keys($tagOptsPtr[$topOptGrp]) as $opKey)
					{
						$tagOptsPtr[0][$opKey] = $tagOptsPtr[$topOptGrp][$opKey];
					}
					unset ($tagOptsPtr[$topOptGrp]);
					array_multisort($tagOptsPtr[0], SORT_ASC);
				}
			}
		}
	}

	protected function cloneElement($idSource, $idTarget = null, $newName, $postfix = '')
	{
		if (!isset($this->tagsPool[$idSource]))
		{
			return null;
		}
		if (is_null($idTarget))
		{
			// find a new unique index
			$index = 0;
			$prefix = '';
			do
			{
				$postfix = $this->autoIdSep . $index;
				if (is_numeric($idSource))
					$prefix = $this->idPrefix;
				//
				$idTarget = $prefix . $idSource . $postfix;
				$index++;
			} while (isset($this->tagsPool[$idTarget]));
		}
		//
		$this->tagsPool[$idTarget] = $this->tagsPool[$idSource];
		// change new element attributes
		if (isset($this->tagsPool[$idTarget]['attrlist']['disabled']))
			unset($this->tagsPool[$idTarget]['attrlist']['disabled']);
		if (isset($this->tagsPool[$idTarget]['attrlist']['for']))
			$this->tagsPool[$idTarget]['attrlist']['for'] = $this->tagsPool[$idTarget]['attrlist']['for'] . $postfix;
		if ((!is_null($newName)) and ($this->tagsPool[$idTarget]['attrlist']['name']))
			$this->tagsPool[$idTarget]['attrlist']['name'] = $newName;
		// copy constraints from source field
		if (isset($this->constraints[$idSource]))
		{
			$this->constraints[$idTarget] = $this->constraints[$idSource];
			if (isset($this->constraints[$idTarget]['requiredIf']))
			{
				foreach (array_keys($this->constraints[$idTarget]['requiredIf']) as $cKey)
				{
					$this->constraints[$idTarget]['requiredIf'][$cKey . $postfix] = $this->constraints[$idTarget]['requiredIf'][$cKey];
					unset($this->constraints[$idTarget]['requiredIf'][$cKey]);
				}
			}
		}
		// return the new tag id
		return $idTarget;
	}

	public function getFieldName($id)
	{
		if (isset($this->tagsPool[$id]['attrlist']['name']))
		{
			return $this->tagsPool[$id]['attrlist']['name'];
		}
		else
			return null;
	}

	public function getSubmitName($id)
	{
		$fName = $this->tagsPool[$id]['attrlist']['name'];
		$openSquarePos = strpos($fName, '[');
		if ($openSquarePos !== false)
		{
			$nameLen = strlen($fName);
			$offset = $nameLen - ($nameLen - $openSquarePos);
			$fName = substr($fName, 0, $offset);
		}
		return $fName;
	}

	public function getSubmitFieldValue($fieldId)
	{
		$index = null;
		$sub = null;
		$fName = $this->tagsPool[$fieldId]['attrlist']['name'];
		$openSquarePos = strpos($fName, '['); //abc[0][def] ... abc[]
		if ($openSquarePos !== false)
		{
			$nameLen = strlen($fName);
			$offset = $nameLen - ($nameLen - $openSquarePos);
			$parts = substr($fName, $offset);
			$fName = substr($fName, 0, $offset);
			//
			if (preg_match_all('/\[([\w]*)\]/i', $parts, $results, PREG_PATTERN_ORDER))
			{
				$kCount = count($results[1]);
				if ($kCount >= 1)
				{
					if (strlen($results[1][0]) > 0)
					{
						$index = $results[1][0];
						if ($kCount == 2)
						{
							$sub = $results[1][1];
						}
					}
				}
			}
		}
		if (is_null($index))
		{
			if (isset($_POST[$fName]))
			{
				return $_POST[$fName];
			}
			else
				return null;
			//
		}
		else
		{
			if (is_null($sub))
			{
				return $_POST[$fName][$index];
			}
			else
			{
				return $_POST[$fName][$index][$sub];
			}
		}
	}

	public function getSubmitValues(&$results, $ignoreFlds = null, $ignoreEmptyFlds = null, $keepDefFlds = null)
	{
		$fldIgnore = array();
		$fldIgnoreEmpty = array();
		$fldKeepDefault = array();
		$fldProcessed = array();
		if ($ignoreFlds != null)
		{
			$fldIgnore = explode(',', $ignoreFlds);
		}
		if ($ignoreEmptyFlds != null)
		{
			$fldIgnoreEmpty = explode(',', $ignoreEmptyFlds);
		}
		if ($keepDefFlds != null)
		{
			$fldKeepDefault = explode(',', $keepDefFlds);
		}
		//
		foreach (array_keys($this->tagsPool) as $fId)
		{
			// ignore specific tags
			switch ($this->tagsPool[$fId]['elemtype'])
			{
				case 'label':
				case 'button':
				case 'fieldset':
				case 'submit':
					continue 2;
			}
			// find the real field name
			$fName = $this->tagsPool[$fId]['attrlist']['name'];
			$openSquarePos = strpos($fName, '[');
			if ($openSquarePos !== false)
			{
				$nameLen = strlen($fName);
				$offset = $nameLen - ($nameLen - $openSquarePos);
				$parts = substr($fName, $offset);
				$fName = substr($fName, 0, $offset);
			}
			// do not attempt to get again an already assigned field
			if (in_array($fName, $fldProcessed))
				continue;
			// obey the ignore field list
			if (in_array($fName, $fldIgnore))
				continue;
			// skip specific empty/blank results
			if (in_array($fName, $fldIgnoreEmpty))
				continue;
			// do not pollute the results array
			if (!array_key_exists($fName, $results))
				continue;
			//
			if (isset($_POST[$fName]))
			{
				$results[$fName] = $_POST[$fName];
			}
			else
			{
				if (in_array($fId, $fldKeepDefault))
				{
					if(isset($this->constraints[$fId]['default']))
					{
						$results[$fName] = $this->constraints[$fId]['default'];
					}
				}
				else
					$results[$fName] = null;
				//
			}
			$fldProcessed[] = $fName;
		}
	}

	public function checkTagAttrByName($tagId, $attName)
	{
		return isset($this->tagsPool[$tagId]['attrlist'][$attName]);
	}

	public function set_breakAfterFields($value=true)
	{
		$this->breakAfterFields = $value;
	}

	public function set_autoIdSep($value)
	{
		$this->autoIdSep = $value;
	}

	public function get_autoIdSep()
	{
		return $this->autoIdSep;
	}

	/**
	 * Set entry for a form field
	 *
	 * @param string $id
	 * @param string $type
	 * @param string $attributes
	 * @return string
	 */
	public function setField($id, $type, $attributes=null, $breakAfter=false, $def=null)
	{
		$this->tagsPool[$id]['elemtype'] = $type;
		if (!is_null($this->curFieldset))
		{
			$this->tagsPool[$id]['fieldset'] = $this->curFieldset;
		}
		if ($breakAfter)
		{
			$this->tagsPool[$id]['breakafter'] = 1;
		}
		// build attributes list
		if (!is_null($attributes))
		{
			$this->setTagAttributes($id, $attributes);
		}
		// set tag name equal to it's id if a name isn't already provided
		if (!isset($this->tagsPool[$id]['attrlist']['name']))
		{
			$this->tagsPool[$id]['attrlist']['name'] = $id;
		}
		// set the field default value
		if ($def !== null)
		{
			$this->constraints[$id]['default'] = $def;
		}
		return $id;
	}

	/**
	 * Set entry for a textarea field
	 *
	 * @param string $id
	 * @param string $text
	 * @param string $attributes
	 */
	public function setTextarea($id, $text=null, $attributes=null, $breakAfter=false, $def=null)
	{
		$this->tagsPool[$id]['elemtype'] = 'textarea';
		if (!is_null($this->curFieldset))
		{
			$this->tagsPool[$id]['fieldset'] = $this->curFieldset;
		}
		if ($breakAfter)
		{
			$this->tagsPool[$id]['breakafter'] = 1;
		}
		// build attributes list
		if (!is_null($attributes))
		{
			$this->setTagAttributes($id, $attributes);
		}
		// set tag name equal to it's id if a name isn't already provided
		if (!isset($this->tagsPool[$id]['attrlist']['name']))
		{
			$this->tagsPool[$id]['attrlist']['name'] = $id;
		}
		// set field text
		if ($text !== null)
		{
			$this->tagsPool[$id]['text'] = $text;
		}
		// set the field default value
		if ($def !== null)
		{
			$this->constraints[$id]['default'] = $def;
		}
		return $id;
	}

	/**
	 * Set one or more items for a radio group
	 *
	 * @param string $id
	 * @param string $items
	 * @return integer
	 */
	public function setRadioItems($id, $items, $lblAfter=false)
	{
		if (!isset($this->tagsPool[$id]))
		{
			return 10;
		}
		if (!$this->tagsPool[$id]['elemtype'] == 'radio')
		{
			return 20;
		}
		$this->setGroupItems($id, $items, $lblAfter);
	}

	public function setCbItems($id, $items, $lblAfter=false)
	{
		if (!isset($this->tagsPool[$id]))
		{
			return 10;
		}
		if (!$this->tagsPool[$id]['elemtype'] == 'checkbox')
		{
			return 20;
		}
		$this->setGroupItems($id, $items, $lblAfter);
	}

	public function setCbState($grpId, $elemValue, $state = 1)
	{
		if (!isset($this->tagsPool[$grpId]))
		{
			return 10;
		}
		if (($this->tagsPool[$grpId]['elemtype'] == 'radio') or ($this->tagsPool[$grpId]['elemtype'] == 'checkbox'))
		{
			if ($this->submitted)
			{
				$elemValue = $this->getSubmitFieldValue($grpId);
			}
			if (is_null($elemValue))
			{
				if (isset($this->constraints[$grpId]['default']))
				{
					$elemValue = $this->constraints[$grpId]['default'];
				}
			}

			if (!is_array($elemValue))
			{
				$elemValue = array($elemValue);
			}
			foreach (array_keys($elemValue) as $index)
			{
				if (!isset($this->tagsPool[$grpId]['attrlist']['items'][$elemValue[$index]]))
				{
					continue;
				}
				$this->tagsPool[$grpId]['attrlist']['items'][$elemValue[$index]]['checked'] = $state;
			}
			return 0;
		}
		else
			return 20;
	}

	function setCbStateByIsset($grpId, $elemValue, &$state)
	{
		$s = 0;
		if (isset($state))
		{
			$s = 1;
		}
		$this->setCbState($grpId, $elemValue, $s);
	}

	public function setInputText($fldId, &$value)
	{
		if (!isset($this->tagsPool[$fldId]))
		{
			return 10;
		}
		$textUsingDef = $value;
		if ($this->submitted)
		{
			$textUsingDef = $this->getSubmitFieldValue($fldId);
		}
		if ((!isset($textUsingDef) OR is_null($textUsingDef)) OR ($textUsingDef == ''))
		{
			// check if a default exists, where found use it to replace the value provided
			$textUsingDef = $this->getDefault($fldId);
			// if the above function returned NULL no default exist, set field as empty str.
			if (is_null($textUsingDef))
				$textUsingDef = '';
		}
		$this->tagsPool[$fldId]['attrlist']['value'] = $textUsingDef;
	}

	/**
	 * Shorthand to set options to be shown for an existing select html element.
	 *   The $items var sets one or more <option> tag using pipe separated list.
	 *   Each list element sets attributes for a specific option, separated by = sign:
	 *   value=display_value=selected_flag=option_group_label
	 *   the first two values are mandatory.
	 * Usage example:
	 *   $form->setSelectOpts('line', 'isdnbri=ISDN BRI=1=Technology|analog=POTS Line=0=Technology');
	 *
	 * @param string $id select tag unique identifier
	 * @param string $options pipe separated list of select options and attibutes
	 * @return integer (0: No errors, 10: id not found, 20: id not a select, 30: not enough attributes
	 */
	public function setSelectOpts($id, $options)
	{
		if (!isset($this->tagsPool[$id]))
		{
			return 10;
		}
		if (!$this->tagsPool[$id]['elemtype'] == 'select')
		{
			return 20;
		}
		$listOpts = explode('|', $options);
		foreach ($listOpts as $opt)
		{
			$optAttr = explode('=', $opt);
			if (count($optAttr) < 2)
			{
				return 30;
			}
			$currOptGrp = 0;
			$currOptSlctd = 0;
			if (isset($optAttr[3]))
			{
				$currOptGrp = $optAttr[3];
			}
			if (isset($optAttr[2]))
			{
				$currOptSlctd = $optAttr[2];
			}
			$this->tagsPool[$id]['attrlist']['options'][$currOptGrp][$optAttr[0]]['value'] = $optAttr[1];
			$this->tagsPool[$id]['attrlist']['options'][$currOptGrp][$optAttr[0]]['selected'] = $currOptSlctd;
		}
		return 0;
	}

	public function setSelectOptFill($id, $arrValLbl, $optGrp = 0)
	{
		if (!isset($this->tagsPool[$id]))
		{
			return 10;
		}
		if (!$this->tagsPool[$id]['elemtype'] == 'select')
		{
			return 20;
		}
		if (is_array($arrValLbl))
		{
			foreach (array_keys($arrValLbl) as $fKey)
			{
				$this->tagsPool[$id]['attrlist']['options'][$optGrp][$fKey]['value'] = $arrValLbl[$fKey];
				$this->tagsPool[$id]['attrlist']['options'][$optGrp][$fKey]['selected'] = 0;
			}
		}
	}

	public function setFieldKeepTopSorted($id, $topGrpOptionText = 'Saved values')
	{
		// !TODO: make this work both for select and/or checkbox
		if (!isset($this->tagsPool[$id]))
		{
			return 10;
		}
		if (!$this->tagsPool[$id]['elemtype'] == 'select')
		{
			return 20;
		}
		$this->tagsPool[$id]['keepvaltopsorted'] = $topGrpOptionText;
	}

	public function setFieldOptionsState($id, $selectedOpts, $default = null)
	{
		// !TODO: make this work both for select and/or checkbox
		if (!isset($this->tagsPool[$id]))
		{
			return 10;
		}
		if (!$this->tagsPool[$id]['elemtype'] == 'select')
		{
			return 20;
		}
		if (!is_null($default))
		{
			$this->setDefault($id, $default);
		}
		if ($this->submitted)
		{
			$selectedOpts = $this->getSubmitFieldValue($id);
		}
		elseif (is_null($selectedOpts) && isset($this->constraints[$id]['default']))
		{
			$selectedOpts = $this->constraints[$id]['default'];
		}
		if (isset($this->tagsPool[$id]['keepvaltopsorted']))
			$this->setTagOptSelectedSorted($id, $selectedOpts);
		else
			$this->setTagOptSelected($id, $selectedOpts);
	}

	public function setFieldValue($id, $value)
	{
		switch ($this->tagsPool[$id]['elemtype'])
		{
			case 'text':
			case 'hidden':
			case 'password':
				$this->tagsPool[$id]['attrlist']['value'] = $value;
				//$this->setInputText($id, $value);
				break;
			case 'textarea':
				$this->tagsPool[$id]['text'] = $value;
				break;
			case 'checkbox':
				// !TODO: checkbox elements may also return an array
			case 'radio':
				// update the current element
				$this->tagsPool[$id]['attrlist']['items'][$value]['checked'] = 1;
				break;
			case 'select':
				$this->setFieldOptionsState($id, $value);
				break;
		}
	}

	/**
	 * Set an html label
	 *
	 * @param string $id unique ID for this field (may be set to null)
	 * @param string $text label caption
	 * @param string $target trget field ID
	 * @param string $attributes label attributes
	 * @return integer
	 */
	public function setLabel($id, $text, $target=null, $attributes=null)
	{
		$tail = '';
		if (is_null($id))
		{
			$id = count($this->tagsPool, 0);
		}
		if (!$this->checkTagById($id))
		{
			$this->tagsPool[$id] = array();
		}
		$this->tagsPool[$id]['elemtype'] = 'label';
		if (!is_null($this->curFieldset))
		{
			$this->tagsPool[$id]['fieldset'] = $this->curFieldset;
		}
		$this->tagsPool[$id]['text'] = $text;
		if (!is_null($target))
		{
			if (!is_null($attributes))
			{
				$tail = "|$attributes";
			}
			$attributes = "for=$target$tail";
		}
		// build attributes list
		if (!is_null($attributes))
		{
			$this->setTagAttributes($id, $attributes);
		}
		return $id;
	}

	/**
	 * Set a fieldset to logically group form fields
	 *
	 * @param string $id unique id
	 * @param string $legend caption to display for that fieldgroup
	 * @param string $attributes html attributes
	 */
	public function setFieldset($id, $legend=NULL, $attributes=NULL)
	{
		if (!is_null($id))
		{
			if (!$this->checkTagById($id))
			{
				$this->tagsPool[$id] = array();
			}
			$this->tagsPool[$id]['elemtype'] = 'fieldset';
			if (!is_null($legend))
			{
				$this->tagsPool[$id]['legend'] = $legend;
			}
			if (!is_null($attributes))
			{
				$this->setTagAttributes($id, $attributes);
			}
		}
		$this->curFieldset = $id;
		return $id;
	}

	/**
	 * Return an array of fields actually set
	 *
	 * @return array
	 */
	public function get_tagsPool()
	{
		return $this->tagsPool;
	}

	/**
	 * Return the ID of the current fieldeset (NULL if none is set)
	 *
	 * @return string
	 */
	public function get_currFieldset()
	{
		return $this->curFieldset;
	}

	public function getFieldSelectedOpts($itemId)
	{
		$selValues = null;
		if (isset($this->tagsPool[$itemId]))
		{
			if ($this->tagsPool[$itemId]['elemtype'] == 'select')
			{
				// target pointer
				$tagOptsPtr = &$this->tagsPool[$itemId]['attrlist']['options'];
				foreach (array_keys($tagOptsPtr) as $gKey)
				{
					foreach (array_keys($tagOptsPtr[$gKey]) as $opKey)
					{
						if ($tagOptsPtr[$gKey][$opKey]['selected'] === 1)
						{
							if (isset($this->tagsPool[$itemId]['attrlist']['multiple']))
							{
								$selValues[] = $opKey;
							}
							else
							{
								return $opKey;
							}
						}
					}
				}
			}
		}
		return $selValues;
	}

	public function getCbFld($cbGroupId)
	{
		$retval = array();
		foreach(array_keys($this->tagsPool[$cbGroupId]['attrlist']['items']) as $iKey)
		{
			if ((bool) $this->tagsPool[$cbGroupId]['attrlist']['items'][$iKey]['checked'])
			{
				$retval[] = $iKey;
			}
		}
		// if we have only one choice then a string will be returned instead of an array
		if (count($this->tagsPool[$cbGroupId]['attrlist']['items'] == 1))
		{
			if (count($retval) == 1 )
			{
				return $retval[0];
			}
			$retval = '';
		}
		return $retval;
	}

	public function getRadioFld($radioGroupId)
	{
		$retval = array();
		foreach(array_keys($this->tagsPool[$radioGroupId]['attrlist']['items']) as $iKey)
		{
			if ((bool) $this->tagsPool[$radioGroupId]['attrlist']['items'][$iKey]['checked'])
			{
				return $iKey;
			}
		}
	}

	public function getCbState($cbGroupId, $cbValue, $returnType = true)
	{
		$retval = false;
		if (isset($this->tagsPool[$cbGroupId]['attrlist']['items'][$cbValue]['checked']))
		{
			$retval = $this->tagsPool[$cbGroupId]['attrlist']['items'][$cbValue]['checked'];
		}
		if ($returnType === true) return (bool) $retval;
		if ($returnType === 'int') return (int) $retval;
		return $retval;
	}

	public function getTextFld($fldId, $trim = true)
	{
		$retval = '';
		if (isset($this->tagsPool[$fldId]['attrlist']['value']))
		{
			$retval = $this->tagsPool[$fldId]['attrlist']['value'];
			if ($trim)
				$retval = trim($retval);
		}
		return $retval;
	}

	public function getFldValue($itemId)
	{
		$retval = null;
		if (!isset($this->tagsPool[$itemId]))
		{
			$itemId = $this->getFieldIdByName($itemId);
		}
		if ($itemId != null)
		{
			switch ($this->tagsPool[$itemId]['elemtype'])
			{
				case 'text':
				case 'password':
				case 'hidden':
					$retval = $this->getTextFld($itemId, false);
					if (is_array($retval))
					{
						$retval = array_filter($retval);
					}
					break;
				case 'radio':
					$retval = $this->getRadioFld($itemId);
					break;
				case 'checkbox':
					$retval = $this->getCbFld($itemId);
					break;
				case 'select':
					$retval = $this->getFieldSelectedOpts($itemId);
					break;
				case 'textarea':
					$retval = $this->getTextareaContent($itemId);
					break;
			}
		}
		return $retval;
	}

	public function getFields(&$arrFldIds, $ignoredIfEmpty = null, $remapFlds = null)
	{
		$fdlIgnoredEmpty = array();
		$remapFieldsList = array();
		$changedFields = array();
		if ($ignoredIfEmpty != null)
		{
			$fldIgnoredEmpty = explode('|', $ignoredIfEmpty);
		}
		if ($remapFlds != null)
		{
			// 'route=rtype,raddress,rgateway|field=f1,f2'
			$groups = explode('|', $remapFlds);
			foreach (array_keys($groups) as $mgKey)
			{
				$gMap = explode('=', $groups[$mgKey]);
				$fList = explode(',', $gMap[1]);
				foreach (array_keys($fList) as $mfKey)
				{
					$remapFieldsList[$gMap[0]][] = $fList[$mfKey];
				}
			}
		}
		foreach(array_keys($arrFldIds) as $fKey)
		{
			if (array_key_exists($fKey, $remapFieldsList))
			{
				foreach (array_keys($remapFieldsList[$fKey]) as $iKey)
				{
					$index = 0;
					$postfix = '';
					$mapFld = $remapFieldsList[$fKey][$iKey];
					$formFld = $mapFld;
					do
					{
						$run = false;
						$formFld = $formFld . $postfix;
						$catch = $this->getFldValue($formFld);
						if ($catch != null)
						{
							$tmp[$index][$mapFld] = $catch;
							$index++;
							$run = true;
						}
					}
					while ($run);
				}
			}
			else
			{
				$tmp = $this->getFldValue($fKey);
			}
			if (!is_null($tmp))
			{
				if (in_array($fKey, $fldIgnoredEmpty) and ($tmp == ''))
					continue;
				//
				if ($arrFldIds[$fKey] != $tmp)
					$changedFields[] = $fKey;
				//
				$arrFldIds[$fKey] = $tmp;
			}
		}
		return $changedFields;
	}

	/**
	 * Return the html label identified by ID
	 *
	 * @param string $id
	 * @return string
	 */
	public function htmlLabel($id)
	{
		return '<label' . $this->getAttributes($id) . '>' . $this->tagsPool[$id]['text'] . '</label>';
	}

	/**
	 * Return the html markup to start rendering a fieldset by it's ID.
	 * - return also the html legend if set for that group.
	 * - return the html markup to close the fieldset if NULL is passed as ID.
	 *
	 * @param string $id
	 * @return string
	 */
	public function htmlFieldset($id)
	{
		$result = '';
		if (!is_null($id))
		{
			$result .= '<fieldset' . $this->getAttributes($id) . '>';
			if (isset($this->tagsPool[$id]['legend']))
			{
				$result .= '<legend>' . $this->tagsPool[$id]['legend'] . '</legend>';
			}
			$this->curFieldset = $id;
			return $result;
		}
		else
		{
			if (!is_null($this->curFieldset))
			{
				$this->curFieldset = null;
				$result = '</fieldset>';
			}
			return $result;
		}
	}


	/**
	 * Return the html markup to render the input field by it's ID
	 *
	 * @param string $id
	 * @return string
	 */
	public function htmlInput($id, $type='text', $append = '')
	{
		$multiple = false;
		if (isset($this->tagsPool[$id]['attrlist']['value']))
		{
			if (is_array($this->tagsPool[$id]['attrlist']['value']))
			{
				// presents multiple fields/values using same name (fieldname[])
				$multiple = true;
			}
		}
		if (!$multiple)
		{
			return "<input type=\"$type\"" . $this->getAttributes($id) . '>' . $append;
		}
		else
		{
			$html = '';
			foreach (array_keys($this->tagsPool[$id]['attrlist']['value']) as $index)
			{
				$html .= "<input type=\"$type\"" . $this->getAttributes($id, $index) . '>' . $append;
			}
			return $html;
		}
	}

	/**
	 * Return the html markup to render the textarea field by it's ID
	 *
	 * @param string $id
	 * @return string
	 */
	public function htmlTextarea($fldId, $escape = false)
	{
		$text = $this->getTextareaContent($fldId);
		if ($escape)
			$text = htmlspecialchars($text);
		return '<textarea' . $this->getAttributes($fldId) . '>' . $text . '</textarea>';
	}

	/**
	 * Return the html markup to render a radio button or a group by it's ID
	 *
	 * @param string $idBase
	 */
	public function htmlRadio($idBase)
	{
		$rbtn = '<input type="radio" value="%s"%s%s>';
		return $this->get_rcGroup($idBase, $rbtn);
	}

	/**
	* Return the html markup to render a checkbox or a group by it's ID
	*
	* @param string $id
	*/
	public function htmlCheckbox($idBase)
	{
		$chkbx = '<input type="checkbox" value="%s"%s%s>';
		return $this->get_rcGroup($idBase, $chkbx);
	}

	/**
	 * Return the html markup to render a select field by it's ID
	 *
	 * @param string $id
	 * @return string
	 */
	public function htmlSelect($id)
	{
		$select = '';
		foreach ($this->tagsPool[$id]['attrlist']['options'] as $setKey => $opSet)
		{
			$currOptList = '';
			$currOptGrp = '';
			foreach ($this->tagsPool[$id]['attrlist']['options'][$setKey] as $opKey => $opValue)
			{
				$currIsSelected = '';
				if ($opValue['selected'] == 1)
				{
					$currIsSelected = ' selected="selected"';
				}
				$currOptList .= sprintf('<option value="%s"%s>%s</option>', $opKey, $currIsSelected, htmlspecialchars($opValue['value']));
			}
			if (!is_numeric($setKey))
			{
				$currOptList = sprintf('<optgroup label="%s">%s</optgroup>', $setKey, $currOptList);
			}
			$select .= $currOptList;
		}
		return '<select' . $this->getAttributes($id) .'>' . $select . '</select>';
	}

	public function htmlField($id, $trusted=false)
	{
		$field = '';
		$htmlBreak = '';
		if ($trusted OR $this->checkTagById($id))
		{
			if(isset($this->tagsPool[$id]['breakafter']))
			{
				if ($this->tagsPool[$id]['breakafter'] == 1)
				{
					$htmlBreak = '<br>';
				}
			}
			//
			switch ($this->tagsPool[$id]['elemtype'])
			{
				case 'text':
				case 'password':
				case 'hidden':
				case 'submit':
				case 'button':
				case 'reset':
				case 'image':
				case 'file':
					$field = $this->htmlInput($id, $this->tagsPool[$id]['elemtype'], $htmlBreak);
					$htmlBreak = '';
					break;
				case 'textarea':
					$field = $this->htmlTextarea($id);
					break;
				case 'checkbox':
					$field = $this->htmlCheckbox($id);
					break;
				case 'radio':
					$field = $this->htmlRadio($id);
					break;
				case 'select':
					$field = $this->htmlSelect($id);
					break;
				case 'label':
					$field = $this->htmlLabel($id);
					break;
				case 'fieldset':
					return $this->htmlFieldset($id);
				default:
					return "<b>Error: unrecognized tag (Type: {$this->tagsPool[$id]['elemtype']} Id: $id)</b>";
			}
			//
			return $field . $htmlBreak;
		}
	}

	public function get_htmlTagsPool($mode=NULL)
	{
		$this->curFieldset = null;
		$this->htmlTagsPool = '';
		foreach ($this->tagsPool as $tagId => $value)
		{
			if (!isset($this->tagsPool[$tagId]['fieldset']))
			{
				$this->htmlTagsPool .= $this->htmlFieldset(null);
			}
			$this->htmlTagsPool .= $this->htmlField($tagId, true);
			if (isset($value['breakafter']))
			{
				if ($value['breakafter'] == 1)
				{
					$this->htmlTagsPool .= '<br>';
				}
			}
		}
		$this->htmlTagsPool .= $this->htmlFieldset(null);
		if (!is_null($mode))
		{
			if ($mode == 'render')
			{
				echo $this->htmlTagsPool;
				return 0;
			}
			return $this->htmlTagsPool;
		}
		$this->htmlTagsPoolReady = true;
		return 0;
	}

	public function setForm($action, $attributes = null)
	{
		if (!isset($this->formTag['attrlist']))
		{
			$this->formTag['attrlist'] = array();
		}
		$this->formTag['attrlist']['action'] = $action;
		if (!is_null($attributes))
		{
			$listAttr = explode('|', $attributes);
			foreach ($listAttr as $attr)
			{
				$params = explode('=', $attr);
				$this->formTag['attrlist'][$params[0]] = $params[1];
			}
		}
		// add an auto generated hidden field to be used for internal checks
		// require that form name is set
		if (isset($this->formTag['attrlist']['name']))
		{
			$this->setField("form_{$this->formTag['attrlist']['name']}", 'hidden');
		}

		$this->submitted = isset($_POST["form_{$this->formTag['attrlist']['name']}"]);
	}

	public function htmlForm($mode=NULL)
	{
		$attribs = '';
		$formTpl = '<form%s>%s</form>';
		if (!$this->htmlTagsPoolReady)
		{
			$this->get_htmlTagsPool();
		}
		foreach ($this->formTag['attrlist'] as $attrName => $attrValue)
		{
			$attribs .= " $attrName=\"$attrValue\"";
		}
		if (!is_null($mode))
		{
			if ($mode == 'render')
			{
				echo sprintf($formTpl, $attribs, $this->htmlTagsPool);
				return 0;
			}
			return sprintf($formTpl, $attribs, $this->htmlTagsPool);
		}
		$this->htmlForm = sprintf($formTpl, $attribs, $this->htmlTagsPool);
		$this->formReady = true;
		return 0;
	}

	public function setRequired($fldId, $fldCaption = null, $requiredIf = null)
	{
		if (!$this->checkTagById($fldId))
		{
			// field do not exist
			return 10;
		}
		$this->constraints[$fldId]['reqd'] = 1;
		$fldConstraints = null;
		if (!is_null($requiredIf))
		{
			$tuples = explode('|', $requiredIf);
			foreach(array_keys($tuples) as $tKey)
			{
				$fldConstrain = null;
				$fldConstrain = explode('=', $tuples[$tKey], 2);
				if (is_array($fldConstrain))
				{
					if (isset($fldConstrain[1]))
					{
						$fldConstraints[$fldConstrain[0]] = $fldConstrain[1];
					}
				}
			}
			if (is_array($fldConstraints))
			{
				$this->constraints[$fldId]['requiredIf'] = $fldConstraints;
			}
		}
		if (!is_null($fldCaption))
		{
			$this->constraints[$fldId]['fCaption'] = $fldCaption;
		}
		return 0;
	}

	public function getConstraintCaption($fldId)
	{
		if (isset($this->constraints[$fldId]['fCaption']))
		{
			return $this->constraints[$fldId]['fCaption'];
		}
		// no caption, try the field name
		if (isset($this->tagsPool[$fldId]['attrlist']['name']))
		{
			return $this->tagsPool[$fldId]['attrlist']['name'];
		}
		return null;
	}

	public function getRequired()
	{
		$requiredFldMap['fNames'] = array();
		$requiredFldMap['fCaptions'] = array();
		foreach(array_keys($this->constraints) as $aKey)
		{
			if (isset($this->constraints[$aKey]['reqd']) and ($this->constraints[$aKey]['reqd'] === 1))
			{
				$requiredFldMap['fNames'][] = $aKey;
				//
				if (isset($this->constraints[$aKey]['fCaption']))
				{
					$requiredFldMap['fCaptions'][] = $this->constraints[$aKey]['fCaption'];
				}
				elseif (isset($this->tagsPool[$aKey]['attrlist']['name']))
				{
					$requiredFldMap['fCaptions'][] = $this->tagsPool[$aKey]['attrlist']['name'];
				}
				else
				{
					/*
					last resort, use the field id as field caption if a proper name was not set
					because the two arrays must be aligned every time.
					*/
					$requiredFldMap['fCaptions'][] = $aKey;
				}
			}
		}
		return $requiredFldMap;
	}

	public function setDefault($fldId, $defValue)
	{
		if (!$this->checkTagById($fldId))
		{
			// field do not exist
			return 10;
		}
		if (!isset($this->constraints[$fldId]))
		{
			$this->constraints[$fldId] = array();
		}
		$this->constraints[$fldId]['default'] = $defValue;
		return 0;
	}

	public function getDefault($fldId)
	{
		if (isset($this->constraints[$fldId]['default']))
			return $this->constraints[$fldId]['default'];
		//
		return null;
	}

	public function setValidationFunc($fldId, $func, $params = null)
	{
		if (!$this->checkTagById($fldId))
		{
			// field do not exist
			return 10;
		}
		if (!isset($this->constraints[$fldId]))
			$this->constraints[$fldId] = array();
		if (!isset($this->constraints[$fldId]['validation']))
			$this->constraints[$fldId]['validation'] = array();
		if (!isset($this->constraints[$fldId]['validation'][$func]))
			$this->constraints[$fldId]['validation'][$func] = array();
		if (!is_null($params))
		{
			if (is_array($params))
			{
				$this->constraints[$fldId]['validation'][$func] = $params;
			}
			else
			{
				$this->constraints[$fldId]['validation'][$func]['errorMsg'] = $params;
			}
		}
		return 0;
	}

	public function getValidationFunc($fldId = null)
	{
		if (is_null($fldId))
		{
			$validationFuncs = array();
			foreach(array_keys($this->constraints) as $vKey)
			{
				if (isset($this->constraints[$vKey]['validation']))
				{
					$validationFuncs = array_merge($validationFuncs, array($vKey => $this->constraints[$vKey]['validation']));
				}
			}
			return $validationFuncs;
		}
		return array($fldId => $this->constraints[$fldId]['validation']);
	}

	public function setValidationFuncErrMsg($fldId, $func, $errorMsg)
	{
		if (!$this->checkTagById($fldId))
		{
			// field do not exist
			return 10;
		}
		$this->constraints[$fldId]['validation'][$func]['errorMsg'] = $errorMsg;
		return 0;
	}

	public function getValidationFuncErrMsg($fldId, $func)
	{
		if (isset($this->constraints[$fldId]['validation'][$func]['errorMsg']))
			return array($this->constraints[$fldId]['validation'][$func]['errorMsg']);
		return null;
	}

	public function get_constraints()
	{
		return $this->constraints;
	}

}
?>
