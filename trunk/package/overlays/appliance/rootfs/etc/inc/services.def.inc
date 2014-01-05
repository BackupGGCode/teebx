<?php
/*
  $Id$
  part of BoneOS build platform (http://www.teebx.com/)
*/

	$services = array(
		'systemlog' => array(
			'form_field' => '',
			'fld_label' => _('System logs'),
			'fld_desc' => _("store system logs on disk"),
			'application' => 'syslogd',
			'dirtree' => 'log',
			'ignorecoldstart' => false,
			'handler' => 'initsvcSystemlogs',
			'rules' => array(
				'reconf' => 'configSyslog',
				'stop' => 'stopSyslog',
				'start' => 'startSyslog',
			)
		),
		'astmedia' => array(
			'form_field' => '',
			'fld_label' => _('Media'),
			'fld_desc' => _('store voice prompts and music-on-hold on disk'),
			'application' => 'asterisk',
			'dirtree' => 'asterisk',
			'ignorecoldstart' => true,
			'handler' => 'initsvcAstmedia',
			'rules' => array(
				'reconf' => 'configAsterisk',
				'stop' => 'stopIpbx',
				'start' => 'startIpbx',
			)
		),
		'astdb' => array(
			'form_field' => '',
			'fld_label' => _('Persistence'),
			'fld_desc' => _("store Asterisk's database on disk"),
			'application' => 'asterisk',
			'dirtree' => 'asterisk/db',
			'ignorecoldstart' => true,
			'handler' => 'initsvcAstdb',
			'rules' => array(
				'reconf' => 'configAsterisk',
				'stop' => 'stopIpbx',
				'start' => 'startIpbx',
			)
		),
		'astcdr' => array(
			'form_field' => '',
			'fld_label' => _('Call Report'),
			'fld_desc' => _("store Asterisk's call detail records on disk"),
			'application' => 'asterisk',
			'dirtree' => 'log/asterisk/cdr',
			'ignorecoldstart' => true,
			'handler' => 'initsvcAstcdr',
			'rules' => array(
				'reconf' => 'configCdr',
				'reload' => 'reloadCdr'
			)
		)
	);
?>