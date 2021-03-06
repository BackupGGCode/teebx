<?php
/*
	$Id$
	part of TeeBX VoIP Communication platform (http://www.teebx.com/)
	Copyright(C) 2010 - 2013 Giovanni Vallesi.

	originally part of AskoziaPBX svn trunk revision 1514 (http://askozia.com/pbx)
	Copyright (C) 2007-2009 tecema (a.k.a IKT) <http://www.tecema.de>. All rights reserved.
	All rights reserved.

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

// set a default
$current_language = 'en_US';
// if any, set the configured language
if (isset($config['system']['webgui']['language']))
{
	if (!empty($config['system']['webgui']['language']))
	{
		$current_language = $config['system']['webgui']['language'];
	}
}
// override if just updated.
if (isset($_POST['lang']))
{
	if (!empty($_POST['lang']))
	{
		$current_language = $_POST['lang'];
	}
}

bindtextdomain('messages', $g['www_path'] . '/locale');
bind_textdomain_codeset('messages', 'UTF-8');
setlocale(LC_MESSAGES, $current_language, $current_language . '.UTF-8');
//putenv("LANG=$current_language");
textdomain('messages');

?>
