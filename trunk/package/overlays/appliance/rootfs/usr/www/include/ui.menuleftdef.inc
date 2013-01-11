<?php
/*
  $Id$
  part of BoneOS build platform (http://www.teebx.com/)
*/

$left_items = array
(
	array
	(
		'label' => gettext('System'),
		'child' => array
		(
			array
			(
				'label' => gettext('General Setup'),
				'url' => 'system.php'
			),
			array
			(
				'label' => gettext('Networking'),
				'url' => 'network_lan.php'
			),
			array
			(
				'label' => gettext('Storage'),
				'url' => 'system_storage.php'
			),
			array
			(
				'label' => gettext('Notifications'),
				'url' => 'notifications_email.php'
			),
			array
			(
				'label' => gettext('Telephony Ports'),
				'child' => array
				(
					array
					(
						'label' => gettext('Analog'),
						'url' => 'ports_analog.php'
					),
					array
					(
						'label' => gettext('ISDN'),
						'url' => 'ports_isdn.php'
					)
				)
			)
		)
	),
	array
	(
		'label' => gettext('Accounts'),
		'child' => array
		(
			array
			(
				'label' => gettext('Providers'),
				'url' => 'accounts_providers.php'
			),
			array
			(
				'label' => gettext('Groups'),
				'url' => 'accounts_phones.php'
			)
		)
	),
	array
	(
		'label' => gettext('Dialplan'),
		'child' => array
		(
			array
			(
				'label' => gettext('Phones'),
				'url' => 'accounts_phones.php'
			),
			array
			(
				'label' => gettext('Ring groups'),
				'url' => 'dialplan_callgroups.php'
			),
			array
			(
				'label' => gettext('Conferencing'),
				'url' => 'services_conferencing.php'
			),
			array
			(
				'label' => gettext('Faxes'),
				'url' => 'accounts_faxes.php'
			),
			array
			(
				'label' => gettext('Applications'),
				'url' => 'dialplan_applications.php'
			),
			array
			(
				'label' => gettext('Feature codes'),
				'url' => 'dialplan_transfers.php'
			)
		)
	),
	array
	(
		'label' => gettext('Services'),
		'child' => array
		(
			array
			(
				'label' => gettext('Voicemail'),
				'url' => 'services_voicemail.php'
			),
			array
			(
				'label' => gettext('MOH'),
				'url' => 'services_media_moh.php'
			)
		)
	),
	array
	(
		'label' => gettext('Maintenance'),
		'child' => array
		(
			array
			(
				'label' => gettext('Firmware'),
				'url' => 'system_firmware.php'
			),
			array
			(
				'label' => gettext('Backup/Restore'),
				'url' => 'system_backup.php'
			),
			array
			(
				'label' => gettext('Factory Defaults'),
				'url' => 'system_defaults.php'
			),
			array
			(
				'label' => gettext('Reboot'),
				'url' => 'system_reboot.php'
			),
			array
			(
				'label' => gettext('Status'),
				'child' => array
				(
					array
					(
						'label' => gettext('Logs'),
						'url' => 'status_logs.php?show=kernel'
					),
					array
					(
						'label' => gettext('Network Traffic'),
						'url' => 'status_graph_traffic.php'
					),
					array
					(
						'label' => gettext('Ports'),
						'url' => 'status_ports.php'
					),
					array
					(
						'label' => gettext('CPU Load'),
						'url' => 'status_graph_cpu.php'
					)
				)
			)
		)
	),
	array
	(
		'label' => gettext('Advanced'),
		'child' => array
		(
			array
			(
				'label' => gettext('SIP'),
				'url' => 'advanced_sip.php'
			),
			array
			(
				'label' => gettext('IAX'),
				'url' => 'advanced_iax.php'
			),
			array
			(
				'label' => gettext('RTP'),
				'url' => 'advanced_rtp.php'
			),
			array
			(
				'label' => gettext('Analog'),
				'url' => 'advanced_analog.php'
			),
			array
			(
				'label' => gettext('Manager'),
				'url' => 'advanced_manager.php'
			),
			array
			(
				'label' => gettext('GUI Options'),
				'url' => 'advanced_guioptions.php'
			)
		)
	)
);

?>