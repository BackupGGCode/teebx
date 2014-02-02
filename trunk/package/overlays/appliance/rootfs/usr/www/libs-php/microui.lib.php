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

function showMsgBlock($msg, $persist=null)
{
	if (is_null($persist))
	{?>
	<script type="text/javascript" charset="utf-8">
		jQuery(document).ready(function()
		{
			jQuery('#msgbox').animate({opacity: 1.0}, 7000).slideUp('slow');
		});
	</script>
	<?php
	}
	echo sprintf('<div id="msgbox" class="confirm"><div>%s</div></div>', $msg);
}

function showErrBlock(&$errMsg)
{
	$msg = '';
	if (is_null($errMsg))
	{
		return;
	}
	if (is_array($errMsg))
	{
		foreach(array_keys($errMsg) as $msgKey)
		{
			$msg .= sprintf('<li>%s</li>', htmlspecialchars($errMsg[$msgKey]));
		}
	}
	else
	{
		if ($errMsg != '')
		{
			$msg .= "<li>$errMsg</li>";
		}
	}
	//
	if ($msg != '')
	{
		$msg = _('The following input errors were detected:') . "<ul>$msg</ul>";
		echo sprintf('<div id="msgbox" class="error"><div>%s</div></div>', $msg);
	}
}

function showPbxRestartWarning($msg = null)
{
	if (is_null($msg))
	{
		$msg = '<span>' . _('Warning') . ':</span><br>';
		$msg .= _('after you click &quot;Save&quot;, all current calls will be dropped.') . '<br>';
		$msg .= _('Pbx will restart.');
	}
	echo '<div class="save_warning">' . $msg . '</div>';
}

function showSaveWarning(&$msg, $restartMsg = false)
{
	$appendStr = '';
	if ($restartMsg)
	{
		$appendStr .= '<br><strong>' . _('System will restart.') . '</strong>';
	}
	echo '<div class="save_warning"><span>' . _('Warning') . ':</span></div>';
	echo '<div class="hint">' . $msg . $appendStr . '</div>';
}

function getAnalogBar($value, $label = null)
{
	if (is_array($value))
	{
		$value = round(($value['used'] * 100) / $value['total'], 0);
	}

	if (is_null($label))
	{
		$label = $value;
	}

	$barHtml = '<div class="analogbar"><div style="width: %s%%;"><p>%s%%</p></div></div>';
	return sprintf($barHtml, $value, $label);
}

function getTabs($arrTabItems, $printOut = false)
{
	$retval = '';
	if (count($arrTabItems) > 0)
	{
		$retval = '<div id="tabs"><ul>';
		foreach(array_keys($arrTabItems) as $itemKey)
		{
			$flagActiveCls = '';
			if ($arrTabItems[$itemKey]['url'] === $_SERVER['REQUEST_URI'])
			{
				$flagActiveCls = ' class="active"';
			}
			$retval .= "<li$flagActiveCls>" .
				"<a href=\"{$arrTabItems[$itemKey]['url']}\"$flagActiveCls>" .
				$arrTabItems[$itemKey]['label'] .
				'</a></li>';
		}
		$retval .= '</ul></div>';
	}
	if ($printOut)
	{
		echo $retval;
	}
	else
	{
		return $retval;
	}
}

?>
