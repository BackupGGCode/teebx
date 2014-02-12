<?php
/*
	$Id$
	part of BoneOS build platform (http://www.teebx.com/)
	Copyright(C) 2011 - 2014 Giovanni Vallesi.
	All rights reserved.

	originally part of AskoziaPBX svn trunk revision 1514 (http://askozia.com/pbx)
	Copyright (C) 2007-2009 tecema (a.k.a IKT) <http://www.tecema.de>. All rights reserved.
	originally part of m0n0wall (http://m0n0.ch/wall)
	Copyright (C) 2003-2006 Manuel Kasper <mk@neon1.net>. All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
	AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
	AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
	SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
	INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
	CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
	POSSIBILITY OF SUCH DAMAGE.
*/

	/* debug settings */
	error_reporting(E_ALL);
	ini_set('display_errors', 0);
	ini_set('log_errors', 1);
	ini_set('error_log', '/tmp/php_error.log');

	require 'libs-php/navigation.class.php';

	$hostLabel = $config['system']['hostname'] . '.' . $config['system']['domain'];
	$nav = new navigation('include/ui.menuleftdef.inc', $hostLabel, _('Dashboard'));
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
	<title><?php echo $nav->title; ?></title>
	<link rel="shortcut icon" href="/img/favicon.ico" type="image/x-icon">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<script src="libs-js/jqueryui/js/jquery.js" type="text/javascript"></script>
	<script src="libs-js/jqueryui/js/ui.core.js" type="text/javascript"></script>
	<script src="libs-js/jqueryui/js/ui.widget.js" type="text/javascript"></script>
	<script src="libs-js/jqueryui/js/ui.mouse.js" type="text/javascript"></script>
	<?php
		if (defined('INCLUDE_DDROPFILES'))
		{
	?>
	<script src="libs-js/jqueryui/js/ui.draggable.js" type="text/javascript"></script>
	<script src="libs-js/jqueryui/js/ui.sortable.js" type="text/javascript"></script>
	<script src="libs-js/jqueryui/contribs/jquery.bsmselect.js" type="text/javascript"></script>
	<script src="libs-js/jqueryui/contribs/jquery.bsmselect.sortable.js" type="text/javascript"></script>
	<script src="libs-js/jqueryui/contribs/jquery.bsmselect.compatibility.js" type="text/javascript"></script>
	<link href="style/jquery.bsmselect.css" rel="stylesheet" type="text/css">
	<script src="libs-js/mediacoder.sorter.js" type="text/javascript"></script>
	<?php
		}
	?>
	<script src="libs-js/jqueryui/js/ui.tabs.js" type="text/javascript"></script>
	<script src="libs-js/jqueryui/contribs/jquery.selectboxes.js" type="text/javascript"></script>
	<script src="libs-js/jqueryui/contribs/jquery.preloadImage.js" type="text/javascript"></script>
	<script src="libs-js/jqueryui/contribs/jquery.progressbar.js" type="text/javascript"></script>
	<?php
		if (defined('INCLUDE_TABSFILES'))
		{
			if (INCLUDE_TABSFILES === 'jshelper')
			{
	?>
	<script src="libs-js/tab.toggledivs.js" type="text/javascript"></script>
	<?php
			}
	?>
	<link href="style/tabs.css" rel="stylesheet" type="text/css">
	<?php
		}
	?>
	<?php
		if (defined('INCLUDE_JSCRIPTS'))
		{
			$jsFiles = explode('|', INCLUDE_JSCRIPTS);
			$cFiles = count($jsFiles);
			for ($idx = 0; $idx < $cFiles; $idx++)
			{
				echo '<script src="libs-js/' . $jsFiles[$idx] . '" type="text/javascript"></script>';
			}
		}
	?>
	<? if (isset($needs_codemirror) AND $needs_codemirror)
	{ ?>
	<script src="libs-js/codemirror/js/codemirror.js" type="text/javascript"></script>
	<? } ?>
	<link href="style/gui.css" rel="stylesheet" type="text/css">
	<link href="style/devpan.css" rel="stylesheet" type="text/css">
	<link href="style/instruments.css" rel="stylesheet" type="text/css">
	<?php
		if (defined('INCLUDE_FORMSTYLE'))
		{
			echo '<link href="style/form.css" rel="stylesheet" type="text/css">';
		}
		if (defined('INCLUDE_TBLSTYLE'))
		{
			echo '<link href="style/table.css" rel="stylesheet" type="text/css">';
		}
	?>
	<!--[if gt IE 5]>
		<script src="libs-js/ie_cssfix.js" type="text/javascript"></script>
	<![endif]-->
	<link href="libs-js/jqueryui/css/ui.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<div id="allbody">
			<div id="user" align="center">
				<div id="ui_header">
					<div id="ui_header_left">
						<a href="index.php"><img src="img/logo.png" width="182" height="68" border="0" alt="<?php echo _("System Information"); ?>"></a>
					</div>
					<div id="ui_header_right">
						<div class="ui_header_info">[admin@<?php echo $hostLabel;?>]</div>
						<div class="page_info"><?php echo $nav->breadcrumbs; ?></div>
					</div>
				<div style="clear:both;"></div>
				</div>
				<div id="ui_body_left" class="ui_menu_left">
					<div id="setup_links">
					<?php
						echo $nav->menu;
					?>
					</div>
				</div>
				<div id="ui_body_right">
<?php
	if (!isset($input_errors))
		$input_errors = null;
	if ($input_errors)
		showErrBlock($input_errors);
	if (!isset($savemsg))
		$savemsg = false;
	if ($savemsg)
		showMsgBlock($savemsg);
	if (!isset($keepmsg))
		$keepmsg = false;
	if ($keepmsg)
		showMsgBlock($keepmsg, 'keep');

	$scripts = array();
?>
