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
				'label' => _('Notifications'),
				'url' => 'notifications_email.php'
			),
			array
			(
				'label' => _('Telephony Ports'),
				'child' => array
				(
					array
					(
						'label' => _('Analog'),
						'url' => 'ports_analog.php'
					),
					array
					(
						'label' => _('ISDN'),
						'url' => 'ports_isdn.php'
					)
				)
			)
		)
	),
	array
	(
		'label' => _('Accounts'),
		'child' => array
		(
			array
			(
				'label' => _('Providers'),
				'url' => 'accounts_providers.php'
			),
			array
			(
				'label' => _('Groups'),
				'url' => 'accounts_phones.php'
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
				'url' => 'accounts_phones.php'
			),
			array
			(
				'label' => _('Ring groups'),
				'url' => 'dialplan_callgroups.php'
			),
			array
			(
				'label' => _('Conferencing'),
				'url' => 'services_conferencing.php'
			),
			array
			(
				'label' => _('Faxes'),
				'url' => 'accounts_faxes.php'
			),
			array
			(
				'label' => _('Applications'),
				'url' => 'dialplan_applications.php'
			),
			array
			(
				'label' => _('Feature codes'),
				'url' => 'dialplan_transfers.php'
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
				'url' => 'services_voicemail.php'
			),
			array
			(
				'label' => _('MOH'),
				'url' => 'services_media_moh.php'
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
				'url' => 'system_firmware.php'
			),
			array
			(
				'label' => _('Backup/Restore'),
				'url' => 'system_backup.php'
			),
			array
			(
				'label' => _('Factory Defaults'),
				'url' => 'system_defaults.php'
			),
			array
			(
				'label' => _('Reboot'),
				'url' => 'system_reboot.php'
			),
			array
			(
				'label' => _('Status'),
				'child' => array
				(
					array
					(
						'label' => _('Logs'),
						'url' => 'maint_status_logs.php?show=kernel'
					),
					array
					(
						'label' => _('Network Traffic'),
						'url' => 'maint_status_netstat.php'
					),
					array
					(
						'label' => _('Ports'),
						'url' => 'status_ports.php'
					),
					array
					(
						'label' => _('CPU Load'),
						'url' => 'status_graph_cpu.php'
					)
				)
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
				'url' => 'advanced_sip.php'
			),
			array
			(
				'label' => _('IAX'),
				'url' => 'advanced_iax.php'
			),
			array
			(
				'label' => _('RTP'),
				'url' => 'advanced_rtp.php'
			),
			array
			(
				'label' => _('Analog'),
				'url' => 'advanced_analog.php'
			),
			array
			(
				'label' => _('Manager'),
				'url' => 'advanced_manager.php'
			),
			array
			(
				'label' => _('GUI Options'),
				'url' => 'advanced_guioptions.php'
			)
		)
	)
);

?>