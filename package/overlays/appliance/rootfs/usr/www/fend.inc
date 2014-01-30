				</div>
				<div style="clear:both;"></div>
				<div id="ui_footer"><?php
					echo 'TeeBX&reg; &copy;&nbsp;2010-2014 Giovanni Vallesi.&nbsp;',
						_('All rights reserved'),
						'.&nbsp;[<a href="license.php" class="tblnk">',
						_('view license information'),
						'</a>]';
				?></div>
			</div>
		</div>
	</body>
</html>
<?php
	if (file_exists($d_passworddirty_path))
	{
		system_update_shell_password();
		unlink($d_passworddirty_path);
	}
?>
