				</div>
				<div style="clear:both;"></div>
				<div id="ui_footer">
					<?=_("TeeBX&reg; &copy; 2010-2013 Giovanni Vallesi. All rights reserved.");?>&nbsp;[<a href="license.php" class="tblnk"><?=_("view license");?></a>]
				</div>
			</div>
		</div>
	</body>
</html>
<?php
	// debug info
	require('libs-php/debug.lib.php');
	logPushMsg($GLOBALS);
	//
	if (file_exists($d_passworddirty_path))
	{
		system_update_shell_password();
		unlink($d_passworddirty_path);
	}
?>
