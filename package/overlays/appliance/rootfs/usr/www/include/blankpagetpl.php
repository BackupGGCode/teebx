<?php
/*
  $Id$
part of BoneOS build platform (http://www.teebx.com/)
Copyright(C) 2012 - 2013 Giovanni Vallesi (http://www.teebx.com).
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
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
	<title></title>
	<link rel="shortcut icon" href="/img/favicon.ico" type="image/x-icon">
	<?php
		if (!defined('REDIRECT_REQ'))
		{
			define('REDIRECT_REQ', "http://{$_SERVER['HTTP_HOST']}/");
		}
		if (!defined('REDIRECT_DLY'))
		{
			define('REDIRECT_DLY', 3000);
		}
		if (!defined('CONTENT_TOP'))
		{
			define('CONTENT_TOP', '<a href="' . REDIRECT_REQ . '">' .
				'<b>' . _('Direct access not allowed!') . '<b><br>' .
				_('Click here to') .' ' .
				_('access the web UI.') .
				'</a>'
			);
		}
		if (!defined('CONTENT_MIDDLE'))
		{
			define('CONTENT_MIDDLE', '');
		}
		if (!defined('CONTENT_BOTTOM'))
		{
			define('CONTENT_BOTTOM', '');
		}
		//
		$redirDly = REDIRECT_DLY;
		$redirOpt = " onLoad=\"setTimeout('dlyRedir()', $redirDly)\"";
	?>
	<script type="text/javascript">
	function dlyRedir()
	{
		window.location = "<?php echo REDIRECT_REQ;?>"
	}
	</script>
	</head>
	<body<?php echo $redirOpt;?>>
		<div>
		<?php
			echo '<div>', CONTENT_TOP, '</div>',
			'<div>', CONTENT_MIDDLE, '</div>',
			'<div>', CONTENT_BOTTOM, '</div>';
			// trick to force buffer to flush as soon as possible
			if (defined('FILL_FORCE_FLUSH'))
			{
				for($i=0; $i<10; $i++)
				{
					echo '<!-- ', str_repeat('.', 65536), ' -->';
					flush();
				}
			}
		?>
		</div>
	</body>
</html>

