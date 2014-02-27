<?php
/*
  $Id$
  part of BoneOS build platform (http://www.teebx.com/)
*/

	$services = array(
		'systemlog' => array(
			'form_field' => '',
			'fld_label_se' => _('System logs'),
			'fld_desc_se' => _('store system logs on disk'),
			'application' => 'syslogd',
			'dirtree' => 'log',
			'ignorecoldstart' => false,
			'handler' => 'initsvcSystemlogs',
			'rules' => array(
				'stop' => 'storageupd_stopSyslog',
				'start' => 'storageupd_startSyslog'
			),
			'fld_label_bck' => _('Logs'),
			'fld_desc_bck' => _('System logs'),
			'includedon_bck' => 0
		),
		'astmedia' => array(
			'form_field' => '',
			'fld_label_se' => _('Media'),
			'fld_desc_se' => _('store pbx voice prompts and music-on-hold on disk'),
			'application' => 'asterisk',
			'dirtree' => 'asterisk',
			'ignorecoldstart' => true,
			'handler' => 'initsvcAstmedia',
			'rules' => array(
				'reconf' => 'configAsterisk',
				'stop' => 'stopIpbx',
				'start' => 'startIpbx',
			),
			'fld_label_bck' => _('Media'),
			'fld_desc_bck' => _('Pbx voice prompts and music-on-hold'),
			'includedon_bck' => 1
		),
		'astdb' => array(
			'form_field' => '',
			'fld_label_se' => _('Persistence'),
			'fld_desc_se' => _("store Asterisk's database on disk"),
			'application' => 'asterisk',
			'dirtree' => 'asterisk/db',
			'ignorecoldstart' => true,
			'handler' => 'initsvcAstdb',
			'rules' => array(
				'reconf' => 'configAsterisk',
				'stop' => 'stopIpbx',
				'start' => 'startIpbx',
			),
			'fld_label_bck' => _('Persistence'),
			'fld_desc_bck' => _("Asterisk's database"),
			'includedon_bck' => 0
		),
		'astcdr' => array(
			'form_field' => '',
			'fld_label_se' => _('Call Report'),
			'fld_desc_se' => _('store pbx call detail records on disk'),
			'application' => 'asterisk',
			'dirtree' => 'log/asterisk/cdr',
			'ignorecoldstart' => true,
			'handler' => 'initsvcAstcdr',
			'rules' => array(
				'reconf' => 'configCdr',
				'reload' => 'reloadCdr'
			),
			'fld_label_bck' => _('Call Report'),
			'fld_desc_bck' => _('Pbx call detail records'),
			'includedon_bck' => 1
		)
	);
?>