<?php
/*
  $Id$
  part of TeeBX(R) VoIP communication platform. http://www.teebx.com
*/

	$base_ast_modules = array(
		'app_dial.so',
		'app_echo.so',
		'app_exec.so',
		'app_macro.so',
		'app_meetme.so',
		'app_milliwatt.so',
		'app_playback.so',
		'app_setcallerid.so',
		'app_stack.so',
		'app_system.so',
		'app_verbose.so',
		'app_voicemail.so',
		'app_wakeme.so',
		'app_while.so',
		'chan_dahdi.so',
		'chan_iax2.so',
		'chan_local.so',
		'chan_sip.so',
		'chan_skinny.so',
		'codec_a_mu.so',
		'codec_adpcm.so',
		'codec_alaw.so',
		'codec_dahdi.so',
		'codec_g722.so',
		'codec_gsm.so',
		'codec_ulaw.so',
		'format_gsm.so',
		'format_pcm.so',
		'format_sln.so',
		'format_wav.so',
		'format_wav_gsm.so',
		'func_callerid.so',
		'func_channel.so',
		'func_config.so',
		'func_cut.so',
		'func_db.so',
		'func_logic.so',
		'func_md5.so',
		'func_shell.so',
		'func_strings.so',
		'pbx_config.so',
		'pbx_loopback.so',
		'pbx_spool.so',
		'res_adsi.so',
		'res_agi.so',
		'res_crypto.so',
		'res_limit.so',
		'res_musiconhold.so',
		'res_smdi.so',
		'res_timing_timerfd.so'
	);

	$heading = <<<EOD
[modules]
autoload=no
;
; this is the minimum set of modules to support the
; functionality built into the TeeBX UI
;
EOD;

?>