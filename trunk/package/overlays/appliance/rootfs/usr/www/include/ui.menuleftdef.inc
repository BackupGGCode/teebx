<?php
/*
  $Id$
  part of BoneOS build platform (http://www.teebx.com/)
*/

$left_items = array
(
	array
	(
		'label' => _('System'),
		'child' => array
		(
			array
			(
				'label' => _('General Setup'),
				'url' => 'sys_general.php'
			),
			array
			(
				'label' => _('Networking'),
				'url' => 'sys_network.php'
			),
			array
			(
				'label' => _('Storage'),
				'url' => 'sys_storage.php'
			),
			array
			(
				'label' => _('Logs'),
				'url' => 'sys_logs.php'
			),
			array
			(
				'label' => _('Notifications'),
				'url' => 'sys_notification.php'
			),
			array
			(
				'label' => _('Telephony Ports'),
				'child' => array
				(
					array
					(
						'label' => _('Analog'),
						'url' => 'sys_port_analog.php'
					),
					array
					(
						'label' => _('ISDN'),
						'url' => 'sys_port_isdn.php'
					)
				)
			)
		)
	),
	array
	(
		'label' => _('Trunks'),
		'child' => array
		(
			array
			(
				'label' => _('VoIP accounts'),
				'url' => 'tk_provider.php'
			),
			array
			(
				'label' => _('Landlines'),
				'url' => 'tk_landline.php'
			),
			array
			(
				'label' => _('Trunk groups'),
				'url' => 'tk_groups.php'
			)
		)
	),
	array
	(
		'label' => _('Dialplan'),
		'child' => array
		(
			array
			(
				'label' => _('Phones'),
				'url' => 'dp_extension.php'
			),
			array
			(
				'label' => _('Ring groups'),
				'url' => 'dp_callgroups.php'
			),
			array
			(
				'label' => _('Conferencing'),
				'url' => 'dp_conferencing.php'
			),
			array
			(
				'label' => _('Faxes'),
				'url' => 'dp_fax.php'
			),
			array
			(
				'label' => _('Applications'),
				'url' => 'dp_applications.php'
			),
			array
			(
				'label' => _('Feature codes'),
				'url' => 'dp_featurecode.php'
			)
		)
	),
	array
	(
		'label' => _('Services'),
		'child' => array
		(
			array
			(
				'label' => _('Voicemail'),
				'url' => 'svc_voicemail.php'
			),
			array
			(
				'label' => _('MOH'),
				'url' => 'svc_media_moh.php'
			)
		)
	),
	array
	(
		'label' => _('Status'),
		'child' => array
		(
			array
			(
				'label' => _('Logs'),
				'url' => 'status_logs.php'
			),
			array
			(
				'label' => _('Network Traffic'),
				'url' => 'status_netstat.php'
			),
			array
			(
				'label' => _('CPU Load'),
				'url' => 'status_cpustat.php'
			),
			array
			(
				'label' => _('Ports'),
				'url' => 'status_ports.php'
			)
		)
	),
	array
	(
		'label' => _('Maintenance'),
		'child' => array
		(
			array
			(
				'label' => _('Firmware'),
				'url' => 'maint_firmware.php'
			),
			array
			(
				'label' => _('Backup/Restore'),
				'url' => 'maint_backup.php'
			),
			array
			(
				'label' => _('Factory Defaults'),
				'url' => 'maint_defaults.php'
			),
			array
			(
				'label' => _('Reboot'),
				'url' => 'maint_reboot.php'
			)
		)
	),
	array
	(
		'label' => _('Advanced'),
		'child' => array
		(
			array
			(
				'label' => _('SIP'),
				'url' => 'adv_sip.php'
			),
			array
			(
				'label' => _('IAX'),
				'url' => 'adv_iax.php'
			),
			array
			(
				'label' => _('RTP'),
				'url' => 'adv_rtp.php'
			),
			array
			(
				'label' => _('Analog'),
				'url' => 'adv_analog.php'
			),
			array
			(
				'label' => _('Manager'),
				'url' => 'adv_manager.php'
			),
			array
			(
				'label' => _('UI Options'),
				'url' => 'adv_uioptions.php'
			)
		)
	)
);

?>