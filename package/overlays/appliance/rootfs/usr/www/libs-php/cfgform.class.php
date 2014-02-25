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

require 'quickform.class.php';
require 'uiutils.lib.php';

class cfgForm extends quickForm implements Serializable
{
	// public properties

	// protected
	protected $layout = array();
	protected $formTpl = null;
	protected $containerTpl = array();
	protected $tplFmtCount = 2;
	// private
	private $currLayoutPath = '';
	private $prevLayoutPath = '';
	private $parentTagId;
	private $errQueue;

	// public class costructor

	/**
	 * Initialize a cfgForm class instance, set the form tag attributes
	 *
	 * @param string $action
	 * @param string $attributes
	 */
	public function __construct($action, $attributes=null)
	{
		parent::__construct();
		$this->setForm($action, $attributes);
		$this->errQueue = array();
		$this->formTpl = '<form%s>%s</form>';
		// containers table
		// all format strings must have equal number of placeholders as defined by $tplFmtCount
		$this->containerTpl['row']['begin'] = '<div class="form_row" id="%s"%s>';
		$this->containerTpl['row']['end'] = '</div>';
		$this->containerTpl['right']['begin'] = '<div class="r_col" id="%s"%s>';
		$this->containerTpl['right']['end'] = '</div>';
		$this->containerTpl['hint']['begin'] = '<div class="hint" id="%s"%s>';
		$this->containerTpl['hint']['end'] = '</div>';
		$this->containerTpl['wrapper']['begin'] = '<div class="wrapper" id="%s"%s>';
		$this->containerTpl['wrapper']['end'] = '</div>';
		$this->containerTpl['cloneable']['begin'] = '<div id="%s"%s>';
		$this->containerTpl['cloneable']['end'] = '</div>';
		$this->containerTpl['controls']['begin'] = '<div class="controls" id="%s"%s>';
		$this->containerTpl['controls']['end'] = '</div>';
	}

	public function serialize()
	{
		return base64_encode(
			gzcompress(
				serialize(
					array(
						'c_fldBlock' => $this->layout,
						'p_tagsPool' => $this->tagsPool,
						'p_constraints' => $this->constraints,
						'p_breakAfterFields' => $this->breakAfterFields,
						'p_autoIdSep' => $this->autoIdSep
					)
				)
			)
		);
	}

	public function unserialize($sState)
	{
		$clsState = unserialize(gzuncompress(base64_decode($sState)));
		if (isset($clsState['c_fldBlock']))
			$this->layout = $clsState['c_fldBlock'];
		if (isset($clsState['p_tagsPool']))
			$this->tagsPool = $clsState['p_tagsPool'];
		if (isset($clsState['p_constraints']))
			$this->constraints = $clsState['p_constraints'];
		if (isset($clsState['p_breakAfterFields']))
			$this->breakAfterFields = $clsState['p_breakAfterFields'];
		if (isset($clsState['p_autoIdSep']))
			$this->autoIdSep = $clsState['p_autoIdSep'];
	}

	// private methods

	private function syncFormByPost()
	{
		if (!isset($_POST))
			return;
		$this->setTagOptUnselected();
		foreach (array_keys($_POST) as $pKey)
		{
			$value = &$_POST[$pKey];
			if (!isset($this->tagsPool[$pKey]))
			{
				$pKey = $this->getFieldIdByName($pKey);
			}
			if ($pKey != null)
			{
				switch ($this->tagsPool[$pKey]['elemtype'])
				{
					case 'text':
					case 'hidden':
					case 'password':
					case 'file':
						$this->tagsPool[$pKey]['attrlist']['value'] = $value;
						break;
					case 'textarea':
						$this->tagsPool[$pKey]['text'] = $value;
						break;
					case 'checkbox':
						// !TODO: checkbox elements may also return an array
					case 'radio':
						// update the current element
						$this->tagsPool[$pKey]['attrlist']['items'][$value]['checked'] = 1;
						break;
					case 'select':
						$this->setFieldOptionsState($pKey, $value);
						break;
					default:
						return "<b>Error: unrecognized tag (Type: {$this->tagsPool[$pKey]['elemtype']} Id: $pKey</b>";
				}
			}
		}
	}

	/**
	 * Register a form tag in the the layout block array
	 *
	 */
	private function registerFormElement()
	{
		$oldPath = $this->currLayoutPath;
		setArrValueByPath(
			$this->layout,
			$this->parentTagId,
			$this->currLayoutPath . '/' . '__AUTONUM__'
		);
		//
		$this->currLayoutPath = $oldPath;
	}

	private function registerLayoutElement($kind, $content=null, $attribs=null)
	{
		$tmpArr = array('__KIND__' => $kind);
		if (!is_null($content))
		{
			if (!empty($content))
			{
				$tmpArr['__HTML__'] = $content;
			}
		}
		if (!is_null($attribs))
		{
			if (!is_array($attribs))
			{
				$tuples = explode(',', $attribs);
				unset($attribs);
				foreach (array_keys($tuples) as $tKey)
				{
					$tuple = explode('=', $tuples[$tKey]);
					if (count($tuple) == 2)
						$attribs[$tuple[0]] = $tuple[1];
					//
				}
			}
			if (is_array($attribs))
			{
				$tmpArr['__ATTR__'] = $attribs;
			}
		}
		setArrValueByPath(
			$this->layout,
			$tmpArr,
			$this->currLayoutPath
		);
	}

	protected function setElementByPath($value)
	{
		setArrValueByPath($this->layout, $value, $this->currLayoutPath);
	}

	protected function get_containerTpl($tplName, $id, $end = false, $attr = null)
	{
		$result = '';
		$part = 'begin';
		if ($end)
			$part = 'end';
		if (isset($this->containerTpl[$tplName]))
		{
			if (!empty($this->containerTpl[$tplName][$part]))
			{
				if (!$end)
				{
					$fmtParams[] = $id;
					// add extra format arguments as needed
					if (!is_null($attr))
					{
						if (is_array($attr))
							$fmtParams = array_merge($fmtParams, $attr);
						else
							$fmtParams[] = $attr;
						//
					}
					// fill the arguments array to required number of format parameters
					$count = count($fmtParams);
					for ($count < $this->tplFmtCount; $count < $this->tplFmtCount; $count++)
					{
						$fmtParams[] = '';
					}
					// format the template string
					$result = vsprintf($this->containerTpl[$tplName][$part], $fmtParams);
				}
				else
					$result = $this->containerTpl[$tplName][$part];
			}
		}
		elseif ($tplName == 'fieldset')
		{
			if (!$end)
				$result = $this->htmlFieldset($id);
			else
				$result = $this->htmlFieldset(null);
		}
		return $result;
	}

	// basic parent methods wrappers and overrides

	/**
	 * Enter description here...
	 *
	 * @param string $id
	 * @param string $legend
	 * @param string $attributes
	 */
	public function startFieldset($id, $legend=NULL, $attributes=NULL)
	{
		$parentId = parent::setFieldset($id, $legend, $attributes);
		// update layout path to current logical container
		$this->currLayoutPath .= '/' . $id;
		$this->currLayoutPath = trim($this->currLayoutPath, '/');
		if (!is_null($parentId))
		{
			$this->parentTagId = $parentId;
			$this->registerLayoutElement('fieldset');
		}
	}

	/**
	 * Exit the current fielset
	 * Alias for exitLayout()
	 */
	public function exitFieldSet()
	{
		$this->exitLayout();
	}

	public function startWrapper($wrapperId, $wrapperTpl = 'wrapper', $attribs = null)
	{
		$this->currLayoutPath .= '/' . $wrapperId;
		$this->currLayoutPath = trim($this->currLayoutPath, '/');
		$this->registerLayoutElement($wrapperTpl, null, $attribs);
	}

	/**
	 * Exit the current wrapper
	 * Alias for exitLayout()
	 */
	public function exitWrapper()
	{
		$this->exitLayout();
	}

	public function clonePrevWrapper($realName, $fValues,  $newAttribs = 'class=clone')
	{
		// handle dynamically user cloned field collections at form submission time
		if ($this->submitted)
		{
			if (isset($_POST[$realName]))
			{
				if (is_array($_POST[$realName]))
				{
					// get submitted values and reindex the array to remove gaps
					$fValues = array_values($_POST[$realName]);
					$_POST[$realName] = $fValues;
				}
			}
			else
			{
				/* This field/fields collection was deleted by the user, nothing to clone
				but set up an hidden field to let the update method to consider
				this field later when values will be needed to update configuration
				*/
				if (!isset($this->tagsPool[$realName]))
					$this->setField($realName, 'hidden', 'disabled=disabled');
				//
				return;
			}
		}
		if (!is_array($fValues))
			return;
		$res = getArrValueByPath($this->layout, $this->prevLayoutPath);
		// check if values passed for fields is a multidimensional array
		$multi = (count($fValues) != count($fValues, COUNT_RECURSIVE));
		if ($res[0] != null)
		{
			// loop through values
			foreach (array_keys($fValues) as $vKey)
			{
				$new['__KIND__'] = $res[1]['__KIND__'];
				// set clone element new attributes, regardless the source attributes
				if (!is_null($newAttribs))
				{
					if (!is_array($newAttribs))
					{
						$tuples = explode(',', $newAttribs);
						unset($newAttribs);
						foreach (array_keys($tuples) as $tKey)
						{
							$tuple = explode('=', $tuples[$tKey]);
							if (count($tuple) == 2)
								$newAttribs[$tuple[0]] = $tuple[1];
							//
						}
					}
					if (is_array($newAttribs))
						$new['__ATTR__'] = $newAttribs;
					//
				}
				// loop through wrapper field collection
				foreach (array_keys($res[1]) as $currKey)
				{
					if (is_numeric($currKey))
					{
						// change flds ids
						$prefix = '';
						if (is_numeric($res[1][$currKey]))
							$prefix = $this->idPrefix;
						//
						$postfix = $this->autoIdSep . $vKey;
						$newFld = $prefix . $res[1][$currKey] . $postfix;
						$new[$currKey] = $newFld;
						$newName = $this->getFieldName($res[1][$currKey]);
						$newValue = $fValues[$vKey];
						if (!is_null($newName))
						{
							if ($multi)
							{
								$newValue = $fValues[$vKey][$newName];
								$newName = "{$realName}[$vKey][$newName]";
							}
							else
							{
								$newName = "{$newName}[]";
							}
						}
						$this->cloneElement($res[1][$currKey], $newFld, $newName, $postfix);
						$this->setFieldValue($newFld, $newValue);
					}
				}
				//
				$new = array($res[0] . $this->autoIdSep . $vKey => $new);
				setArrValueByPath(
					$this->layout,
					$new,
					$this->currLayoutPath
				);
				unset($new);
			}
		}
		//
	}

	public function setLabel($id, $text, $target=null, $attributes=null)
	{
		$this->parentTagId = parent::setLabel($id, $text, $target, $attributes);
		$this->registerFormElement();
	}

	public function setField($id, $type, $attributes=null, $breakAfter=false, $def=null)
	{
		$this->parentTagId = parent::setField($id, $type, $attributes, $breakAfter, $def);
		$this->registerFormElement();
	}

	public function setTextarea($id, $text=null, $attributes=null, $breakAfter=false, $def=null)
	{
		$this->parentTagId = parent::setTextarea($id, $text, $attributes, $breakAfter, $def);
		$this->registerFormElement();
	}

	// base public class methods
	public function get_formTpl()
	{
		return $this->formTpl;
	}

	public function set_formTpl($htmlMarkup)
	{
		$this->formTpl = $htmlMarkup;
	}

	public function getTextareaStrings($fldId, $split=true, $trim=true, $b64encode=true)
	{
		$text = parent::getTextareaContent($fldId);
		if (trim($text) == '')
			return array();
		//
		if(!$split)
		{
			if ($trim)
				$text = trim($text);
			if ($b64encode)
				$text = base64_encode($text);
			return $text;
		}
		//
		$arrLines = preg_split("/[\x0D\x0A]+/", $text, - 1, PREG_SPLIT_NO_EMPTY);
		foreach (array_keys($arrLines) as $rowKey)
		{
			$line = &$arrLines[$rowKey];
			if ($trim)
				$line = trim($line);
		}
		if ($b64encode)
			$arrLines = array_map('base64_encode', $arrLines);
		return $arrLines;
	}

	public function wake(&$sState)
	{
		$this->unserialize($sState);
		$this->syncFormByPost();
	}

	/**
	 * Start a layout block that will hold newly assigned fields
	 *
	 * @param string $id
	 * @param string $pos
	 */
	public function startBlock($id, $pos = '', $attributes=null)
	{
		$ssPath = '';
		$fsPath = '';
		$oldPath = $this->currLayoutPath;
		// quick & dirty test for call over already existing path
		// !TODO: log errors
		$path = explode('/', $oldPath);
		if (!empty($pos))
		{
			if (end($path) == "{$id}_{$pos}")
			{
				return;
			}
		}
		if (end($path) == $id)
		{
			array_pop($path);
			$oldPath = join('/', $path);
		}
		$fsPath = trim($oldPath . "/$id", '/');
		$ssPath = $fsPath . "/{$id}_{$pos}";
		// !TODO: process attributes to array
		if ($this->currLayoutPath != $fsPath)
		{
			$this->currLayoutPath = $fsPath;
			$this->registerLayoutElement('row', null, $attributes);
		}
		//
		if(!empty($pos))
		{
			$this->currLayoutPath = $ssPath;
			$this->registerLayoutElement($pos, null, $attributes);
		}
	}

	/**
	 * Exit the current layout element
	 *
	 */
	public function exitLayout($steps = 1)
	{
		$this->prevLayoutPath = $this->currLayoutPath;
		$path = explode('/', $this->currLayoutPath);
		array_splice($path, (0 - $steps), $steps);
		$this->currLayoutPath = join('/', $path);
	}

	/**
	 * Reset the layout block
	 * Alias for exitLayout()
	 */
	public function exitBlock()
	{
		$this->exitLayout(2);
	}

	public function setBlockHint($idTag, $hintText = '')
	{
		$oldPath = $this->currLayoutPath;
		$this->currLayoutPath .= "/$idTag";
		$this->registerLayoutElement('hint', $hintText);
		$this->currLayoutPath = $oldPath;
	}

	public function renderForm()
	{
		$result = '';
		$grp = array();
		//
		$breadcrumbTrail = array();
		$oldDepth = -1;
		$iteIter = new RecursiveIteratorIterator(
			new RecursiveArrayIterator($this->layout),
			RecursiveIteratorIterator::SELF_FIRST
		);
		foreach ($iteIter as $key => $val)
		{
			// check current elements nesting to close backward any block container
			$currDepth = $iteIter->getDepth();
			$oldDepth = count($breadcrumbTrail);
			for ($currDepth < $oldDepth; $currDepth < $oldDepth; $oldDepth--)
			{
				$trail = end($breadcrumbTrail);
				$result .= $this->get_containerTpl($trail, null, true);
				array_pop($breadcrumbTrail);
			}
			//
			if (is_array($val))
			{
				$currKind = null;
				$currAttr = null;
				switch ($key)
				{
					case '__KIND__':
					case '__ATTR__':
						break;
					default:
					{
						$currKind = $val['__KIND__'];
						if (isset($val['__ATTR__']))
						{
							$currAttr = '';
							foreach (array_keys($val['__ATTR__']) as $attKey)
							{
								$currAttr .= " $attKey=\"{$val['__ATTR__'][$attKey]}\"";
							}
						}
						if ($currDepth < $oldDepth)
						{
							$a = $currKind;
						}
						$breadcrumbTrail[] = $currKind;
						$result .= $this->get_containerTpl($currKind, $key, false, $currAttr);
					}
				}
			}
			else
			{
				if(is_int($key))
				{
					$result .= $this->htmlField($val);
				}
				elseif ($key == '__HTML__')
				{
					$result .= $val;
				}
			}
		}
		// ensure opened layout elements tags will close properly
		$remainderTrail = count($breadcrumbTrail);
		for ($remainderTrail > 0; $remainderTrail >= 1; $remainderTrail--)
		{
			$result .= $this->get_containerTpl($breadcrumbTrail[$remainderTrail - 1], null, true);
		}
		// prepare form markup
		$formAttribs = '';
		foreach ($this->formTag['attrlist'] as $attrName => $attrValue)
		{
			$formAttribs .= " $attrName=\"$attrValue\"";
		}
		echo sprintf($this->formTpl, $formAttribs, $result);
	}

	/*
	public class methods to set up predefined form blocks
	*/

	public function presetManAttrEd($lblCaption, &$content, $hint = null)
	{
		$this->startBlock('man_attributes');
		$this->setLabel(NULL, $lblCaption, 'manualattributes', 'class=labelcol');
		$this->startBlock('man_attributes', 'right');
		$fldTxt = '';
		if (is_array($content))
		{
			$n = count($content);
			$fldTxt .= base64_decode($content[0]);
			for ($i = 1; $i < $n; $i++)
			{
				$fldTxt .= "\n" . base64_decode($content[$i]);
			}
		}
		$this->setTextarea('manualattributes', $fldTxt, 'cols=40|rows=5');
		if (!is_null($hint))
		{
			$this->setBlockHint('hint-manualattributes', $hint);
		}
		$this->exitBlock();
	}

	public function presetGrpPort($lblText, $fldValue = '', $required = false)
	{
		$this->startBlock('port_block');
		$this->setLabel(null, $lblText, 'port', 'class=labelcol');
		$this->startBlock('port_block', 'right');
		$this->setField('port', 'text', "value=$fldValue");
	}

	public function presetFldsetExtBasic(&$cfgPtr, $techInfo)
	{
		$numFldHint = _('The number used to dial this phone.');
		if ($techInfo == 'sip' OR $tech == 'iax')
		{
			$numFldHint .= '<br>' . _('Use this number as your username.');
			$authLabel = _('Password');
			$authFldName = 'secret';
			$authFldLen = 16;
			$authFldMaxLen = 64;
			$authFldHint = _('This account\'s password.');
		}
		elseif ($techInfo == 'skinny')
		{
			$authLabel = _('Device');
			$authFldName = 'device';
			$authFldLen = 17;
			$authFldMaxLen = 17;
			$authFldHint = _('The MAC address of this phone.');
		}
		else
		{
			$authFldHint = _('The hardware port this phone is connected to.');
			if ($techInfo == 'isdn')
			{
			}
			elseif ($techInfo == 'analog')
			{
			}
		}
		// extension number, required
		$this->startFieldset('fset_baseexten', _('Base Settings'));
		$this->startBlock('rw_number');
		$this->setLabel(null, _('Number'), 'extension', 'class=labelcol');
		$this->startBlock('rw_number', 'right');
		$this->setField('extension', 'text', "size=12|maxlength=12|class=required");
		$this->setInputText('extension', $cfgPtr['extension']);
		$this->setBlockHint('hint-number', $numFldHint);
		$this->exitBlock();
		// caller name part used for CLI, required
		$this->startBlock('rw_cname');
		$this->setLabel(null, _('Caller Name'), 'callerid', 'class=labelcol');
		$this->startBlock('rw_cname', 'right');
		$this->setField('callerid', 'text', "size=40|maxlength=40|class=required");
		$this->setInputText('callerid', $cfgPtr['callerid']);
		$this->setBlockHint('hint-cname', _('Text to be displayed for Caller ID Name.'));
		$this->exitBlock();
		// auth details or phisical port, depending on used technology. Required.
		if ($techInfo == 'isdn' OR $techInfo == 'analog')
		{

		}
		else
		{
			$this->startBlock('rw_auth');
			$this->setLabel(null, $authLabel, $authFldName, 'class=labelcol');
			$this->startBlock('rw_auth', 'right');
			$this->setField($authFldName, 'text', "size=$authFldLen|maxlength=$authFldMaxLen|class=required");
			$this->setInputText($authFldName, $cfgPtr[$authFldName]);
			$this->setBlockHint('hint-auth', $authFldHint);
			$this->exitBlock();
		}
		//
		$this->exitFieldSet();
	}

	public function presetFldsetExtGeneral(&$cfgPtr, &$arrRingLen, &$arrLangs)
	{
		$this->startFieldset('fset_genexten', _('General Settings'));
		// ring len. selector
		$this->startBlock('rw_rlen');
		$this->setLabel(null, _('Ring Length'), 'ringlength', 'class=labelcol');
		$this->startBlock('rw_rlen', 'right');
		$this->setField('ringlength', 'select', "name=ringlength");
		$this->setSelectOptFill('ringlength', $arrRingLen);
		$this->setFieldOptionsState('ringlength', $cfgPtr['ringlength']);
		$this->setBlockHint('hint-rlen', _('The number of seconds this phone will ring before giving up or going to voicemail.'));
		$this->exitBlock();
		// channel language selector
		$this->startBlock('rw_clang');
		$this->setLabel(null, _('Language'), 'ringlength', 'class=labelcol');
		$this->startBlock('rw_clang', 'right');
		$this->setField('language', 'select', "name=language");
		$this->setSelectOptFill('language', $arrLangs);
		$this->setFieldOptionsState('language', $cfgPtr['language']);
		$this->setBlockHint('hint-clang', _('Audio prompts will be played back in the selected language for this account.'));
		$this->exitBlock();
		// free text description
		$this->startBlock('rw_descr');
		$this->setLabel(null, _('Description'), 'descr', 'class=labelcol');
		$this->startBlock('rw_descr', 'right');
		$this->setField('descr', 'text', "size=40|maxlength=40");
		$this->setInputText('descr', $cfgPtr['descr']);
		$this->setBlockHint('hint-descr', _('You may enter a description here for your reference (not parsed).'));
		$this->exitBlock();
		//
		$this->exitFieldSet();
	}

	public function presetFldsetExtSecurity(&$cfgPtr, $ShowAuthOpt = false, $showNetOpt = false)
	{
		$this->startFieldset('fset_security', _('Security'));
		// public access enable
			$this->startBlock('rw_pubaccs');
				$this->setLabel(null, _('Public Access'), null, 'class=labelcol');
				$this->startBlock('rw_pubaccs', 'right');
					$this->setField('publicaccess', 'checkbox');
					$this->setCbItems('publicaccess',
						'yes=' . _('allow this number to be reachable over the Internet'),
						true);
					$this->setCbStateByIsset('publicaccess', 'yes', $cfgPtr['publicaccess']);
				$this->exitBlock();
			// public acces alias field
			$this->startBlock('rw_publicname');
				$this->setLabel(null, _('alias'), 'publicname', 'class=labelcol');
				$this->startBlock('rw_publicname', 'right');
					$this->setField('publicname', 'text', "size=40|maxlength=40");
					$this->setInputText('publicname', $cfgPtr['publicname']);
					$this->setBlockHint('hint-publicname', _('Set a friendlier alias above if you would like to use a word or name instead of the extension number.'));
			$this->exitBlock();
			// auth method field
			if (array_key_exists('authentication', $cfgPtr))
			{
			}
			// allow/deny networks address fields
			if (array_key_exists('netallowed', $cfgPtr))
			{
			}
			//
		$this->exitFieldSet();
	}

	public function presetFldsetExtOgRoutes(&$cfgPtr, $ogRoutes)
	{
		if (count($ogRoutes) < 1)
		{
			return;
		}
		$this->startFieldset('fset_ogroutes', _('Outgoing calls restrictions'));
			$this->startBlock('rw_ogroutes');
				$this->setLabel(null, _('Block Providers'), null, 'class=labelcol');
				$this->startBlock('rw_ogroutes', 'right');
				//
				foreach ($ogRoutes as $route)
				{
					$r_uid = $route['uniqid'];
					$r_name = $route['name'];
					$this->setField($r_uid, 'checkbox');
					$this->setCbItems($r_uid, 'yes=' . $r_name, true);
					$this->setCbStateByIsset($r_uid, 'yes', $cfgPtr['provider']);
				}
				//
				$this->setBlockHint('hint-ogroute', _('Block access to the providers selected above.'));
			$this->exitBlock();
		$this->exitFieldset();
	}

	public function presetFldsetExtVoiceBox(&$cfgPtr)
	{

	}

	public function presetFldsetCodecs(&$cfgPtr, &$audioCodecs, &$videoCodecs)
	{

	}

	/*
	class form validation methods
	*/

	public function getValidationErrMsg($fieldName, $funcName = null)
	{
		$retval = _('Unknown error in field: ' . $fieldName);
		if (is_null($funcName))
		{
			$stack = debug_backtrace(false);
			$funcName = $stack[2]['function'];
			unset($stack);
		}
		if (isset($this->constraints[$fieldName]['validation'][$funcName]['errorMsg']))
			$retval = $this->constraints[$fieldName]['validation'][$funcName]['errorMsg'];
		return $retval;
	}

	public function get_errQueue()
	{
		return $this->errQueue;
	}

	public function validPostData()
	{
		// deny not allowed input chars
		$this->validChars($_POST);
		// check for required fields
		$reqFldMap = $this->getRequired();
		foreach(array_keys($reqFldMap['fNames']) as $fKey)
		{
			// ignore any disabled field
			if (isset($this->tagsPool[$reqFldMap['fNames'][$fKey]]['attrlist']['disabled']))
				continue;
			//
			$currValue = $this->getSubmitFieldValue($reqFldMap['fNames'][$fKey]);
			if (is_null($currValue) or ($currValue == ''))
			{
				$testPass = false;
				if (isset($this->constraints[$reqFldMap['fNames'][$fKey]]['requiredIf']))
				{
					foreach(array_keys($this->constraints[$reqFldMap['fNames'][$fKey]]['requiredIf']) as $testKey)
					{
						$testValue = $this->getSubmitFieldValue($testKey);
						switch ($this->constraints[$reqFldMap['fNames'][$fKey]]['requiredIf'][$testKey])
						{
							case '__ISNULL__':
								$testPass |= !is_null($testValue);
								break;
							case '__NONULL__':
								$testPass |= is_null($testValue);
								break;
							case '__EMPTY__':
								$testPass |= !empty($testValue);
								break;
							case '__BLANK__':
								$testPass |= !($testValue == '');
								break;
							default:
							{
								if (!is_null($testValue))
								{
									$testPass |= ($testValue != $this->constraints[$reqFldMap['fNames'][$fKey]]['requiredIf'][$testKey]);
								}
								elseif ($this->constraints[$reqFldMap['fNames'][$fKey]]['requiredIf'][$testKey] != null)
								{
									$testPass |= true;
								}
							}
						}
					}
				}
				if ($testPass)
					continue;
				//
				$this->errQueue[] = sprintf(_("The field '%s' is required."), _($reqFldMap['fCaptions'][$fKey]));
			}
		}
	}

	public function validChars(&$data)
	{
		foreach(array_keys($data) as $aKey)
		{
			if (is_array($data[$aKey]))
			{
				$str = count_chars(implode($data[$aKey]), 3);
			}
			else
			{
				$str = count_chars($data[$aKey], 3);
			}
			if (preg_match("/[\\x00-\\x08\\x0b\\x0c\\x0e-\\x1f]/", $str))
			{
				$this->errQueue[] = sprintf(_("The field '%s' contains invalid characters."), $aKey);
			}
		}
	}

	public function validAsInteger($fieldName)
	{
		$inString = &$_POST[$fieldName];
		if (!ctype_digit($inString))
		{
			$this->errQueue[] = $this->getValidationErrMsg($fieldName, 'validAsInteger');
			return 1;
		}
		return 0;
	}

	public function validPort($fieldName, &$value, $unprivileged = array('unprivileged' => true))
	{
		$lowerPort = 1024;
		$highPort = 65535;
		$portNumericString = $value;
		if (!ctype_digit($value))
		{
			$this->errQueue[] = _('A valid port must be specified.') . ' (' . _('Port value must be a number') . ')';
		}
		// set a lower start base if we allow privileged ports
		if (!$unprivileged['unprivileged'])
		{
			$lowerPort = 1;
		}
		if ($value == 0)
		{
			$this->errQueue[] = _('Port value must be a number') . '. ' . _('Valid range') . ': ' . "&gt; $lowerPort &lt; $highPort";
		}
		elseif ($value < $lowerPort)
		{
			$this->errQueue[] = _('Unprivileged port required, value must be greather than' . ' ' . $lowerPort - 1);
		}
		elseif ($value > $highPort)
		{
			$this->errQueue[] = _('Port value must be lower than') . ' ' . $highPort + 1;
		}
	}

	public function validGreaterOf($fieldName, $arrayParams)
	{
		if (isset($_POST[$arrayParams['greaterOf']]))
		{
			if ($_POST[$fieldName] <= $_POST[$arrayParams['greaterOf']])
			{
				$this->errQueue[] = $arrayParams['errorMsg'];
				return 1;
			}
			return 0;
		}
	}

	public function validIf($fieldId, &$value, $idx = null, $arrayParams = null)
	{
		$valid = false;
		if ($arrayParams === null)
		{
			$this->errQueue[] = $fieldId . ', debug info: missing validation arguments.';
			return 4;
		}
		if (!is_array($arrayParams))
		{
			$this->errQueue[] = $fieldId . ', debug info: missing validation arguments type.';
			return 3;
		}
		//
		$cmpToFld = $this->getSubmitFieldValue($arrayParams['validSet']['fld']);
		switch($arrayParams['validSet']['cond'])
		{
			case '<':
				$valid = ($value < $cmpToFld);
				break;
			case '<=':
				$valid = ($value <= $cmpToFld);
				break;
			case '==':
				$valid = ($value == $cmpToFld);
				break;
			case '===':
				$valid = ($value === $cmpToFld);
				break;
			case '>=':
				$valid = ($value >= $cmpToFld);
				break;
			case '>':
				$valid = ($value > $cmpToFld);
				break;
			case '!=':
				$valid = ($value != $cmpToFld);
				break;
			default: return 2;
		}
		if (!$valid)
		{
			$this->errQueue[] = $arrayParams['errorMsg'];
			return 1;
		}
		return 0;
	}

	public function validDirectiveSntx($fieldName)
	{
		$cfgData = &$_POST[$fieldName];
		if (is_array($cfgData))
		{
			$data = &$cfgData;
		}
		else
		{
			$data = explode(PHP_EOL, $cfgData);
		}
		foreach(array_keys($data) as $aKey)
		{
			// spaces allowed
			$cfgRow = trim($data[$aKey]);
			// skip empty lines
			if ($cfgRow == '' OR $cfgRow == PHP_EOL)
			{
				continue;
			}
			// comments allowed
			if ($cfgRow{0} == ';')
			{
				continue;
			}
			if (preg_match("/^.+=.+/", $cfgRow))
			{
				continue;
			}
			$ln = $aKey + 1;
			$this->errQueue[] = "Manual attribute line number $ln is invalid: " . $data[$aKey];
		}
	}

	public function validIpAddr($fieldId, &$data, $idx = null, $params = null)
	{
		if (!is_null($params) && is_array($params))
		{
			if (isset($params['except']))
			{
				// no validation because allowed to be empty
				if ($params['except'] === 'empty' && $data == '')
				{
					return;
				}
				// ...
			}
		}
		$ident = $this->getConstraintCaption($fieldId);
		if ($ident != null)
			$ident = "$ident: ";
		else
			$ident = '';
		//
		if ($idx !== null)
			$ident = $ident . "(#$idx) ";
		//
		if ($data === filter_var($data, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
		{
			return;
		}
		$this->errQueue[] = $ident . _('A valid IP address must be specified.');
	}

	// TODO: this is a mess and need a deep refactoring!
	public function validForm()
	{
		$fProcessed = array();
		// check required fields
		$this->validPostData();
		// get functions to validate form fields
		$validationFuncs = $this->getValidationFunc();
		// loop thru input constraints to validate fields.
		// to prevent a severe security hole only 'validator' class methods are callable
		foreach (array_keys($validationFuncs) as $fKey)
		{
			// skip fields disabled at design time
			if (isset($this->tagsPool[$fKey]['attrlist']['disabled']))
			{
				if ($this->tagsPool[$fKey]['attrlist']['disabled'] === 'disabled')
					continue;
				//
			}
			$fName = $this->getSubmitName($fKey);
			if (isset($_POST[$fName]))
			{
				$fValue = $this->getSubmitFieldValue($fKey);
				foreach (array_keys($validationFuncs[$fKey]) as $mKey)
				{
					$vfCount = count($validationFuncs[$fKey][$mKey]);
					if (!is_array($fValue))
					{
						if ($vfCount > 1)
						{
							$this->{$mKey}($fKey, $fValue, null, $validationFuncs[$fKey][$mKey]);
						}
						else
						{
							$this->{$mKey}($fKey, $fValue, $validationFuncs[$fKey][$mKey]);
						}
					}
					else
					{
						// do not attempt to validate any array submit result more than once
						if (in_array($fName, $fProcessed))
							continue;
						//
						$idx = 1;
						foreach (array_keys($fValue) as $pKey)
						{
							if ($vfCount > 1)
							{
								$this->{$mKey}($fKey, $fValue[$pKey], $idx, $validationFuncs[$fKey][$mKey]);
							}
							else
							{
								$this->{$mKey}($fKey, $fValue[$pKey], $idx);
							}
							$idx++;
						}
						// feed the processed field name array
						$fProcessed[] = $fName;
					}
				}
			}
		}
	}
}

?>
