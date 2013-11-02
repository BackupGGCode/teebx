<?php
/*
	$Id$
	part of m0n0wall (http://m0n0.ch/wall)

	Copyright (C) 2003-2006 Manuel Kasper <mk@neon1.net>.
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

require('guiconfig.inc');
require('libs-php/htmltable.class.php');
define('INCLUDE_TBLSTYLE', true);

$pgtitle = array('License');
include('fbegin.inc');
?>
	<p><strong>The TeeBX&reg; communication platform.<br>
	Copyright &copy; 2010 - 2013 Giovanni Vallesi (<a href="http://teebx.org/">TeeBX.org</a>).<br>
	All rights reserved.</strong></p>
	<pre>  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU Affero General Public License as
  published by the Free Software Foundation, either version 3 of the
  License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU Affero General Public License for more details.

  You should have received a copy of the GNU Affero General Public License
  along with this program.  If not, see [http://www.gnu.org/licenses/].

  TeeBX Source code is available via svn at [http://code.google.com/p/teebx/source/checkout].</pre>
  <p>Because  TeeBX includes many pieces of software the above license apply only to the code written for TeeBX itself, any other software boundled inluding the one forked apply for a proper license.</p>
  <hr size="1">
	<p>TeeBX&reg is derived from Askozia&reg;PBX (Linux based 2.0 branch, svn revision 1948) which is currently released under a more restrictive license.<br>
	Askozia&reg;PBX is Copyright &copy; 2007-2011 tecema gmbh a.k.a. <a href="http://www.tecema.de" target="_blank">IKT</a>.</br>
	Please read carefully their license at <a href="http://www.askozia.com/pbx-license" target="_blank">Askozia&reg; website</a>.<br>
	Many thanks to Michael Iedema, the whole tecema staff and all of the AskoziaPBX contributors.<br>
	<hr size="1">
	<p>TeeBX&reg is also based upon/includes various software packages, listed below. A big thanks to the authors for all of their hard work!</p>
	<div id="software_list" class="scrollable">
	<?php
		$dataTbl = new csvRenderer('id=table_software_list|class=report');
		$dataTbl->loadData('docs/software-information.csv',
			array(_('Name'),
				_('Version'),
				_('Description'),
				_('License'),
				_('Website')
			)
		);
		$dataTbl->renderTable();
	?>
	</div>
	<div style="clear: both;"></div>
<?php include("fend.inc"); ?>
