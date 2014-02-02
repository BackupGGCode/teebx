<?php
/*
  $Id: .$
part of BoneOS build platform (http://www.teebx.com/)
Copyright(C) 2014 Giovanni Vallesi (http://www.teebx.com).
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

require 'guiconfig.inc';
require 'libs-php/htmltable.class.php';
define('INCLUDE_TBLSTYLE', true);

require 'fbegin.inc';
?>
	<p>
		<strong>TeeBX&reg; communication platform.
		<a href="https://www.gnu.org/licenses/agpl.html" target="_blank"><img src="img/agplv3.png" class="imgfloatrgt" alt="GNU Affero General Public License v 3"></a><br>
		Copyright &copy; 2010 - 2014 Giovanni Vallesi (<a href="http://www.teebx.org/">TeeBX.org</a>).<br>
		<?php echo _('All rights reserved under the terms of AGPLv3'), '.&nbsp;<span id="toggleldetails" class="clickhere">', _('Click here to'), '&nbsp;', _('show/hide details'),'</span>';?>
		</strong>
	</p>
	<div id="ldetails" style="display:none;">
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
	</div>
	<hr size="1">
	<p>TeeBX&reg;&nbsp;<?php echo _('is based upon/includes various software packages, listed below. A big thanks to the authors for all of their hard work!'); ?></p>
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
	<script type="text/javascript">
		jQuery('#toggleldetails').click(function()
		{
			if (jQuery('#ldetails').is(':hidden'))
			{
				jQuery('#ldetails').slideDown('fast');
			}
			else
			{
				jQuery('#ldetails').hide();
			}
		});
	</script>
<?php include("fend.inc"); ?>
