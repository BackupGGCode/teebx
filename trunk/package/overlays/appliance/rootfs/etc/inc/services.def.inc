<?php
/*
  $Id$
  part of BoneOS build platform (http://www.teebx.com/)
*/

	$services = array(
		'astmedia' => array(
			'form_field' => '',
			'fld_label' => _('Media'),
			'fld_desc' => _('store voice prompts and music-on-hold on disk'),
		),
		'astdb' => array(
			'form_field' => '',
			'fld_label' => _('Persistence'),
			'fld_desc' => _("store Asterisk's database on disk"),
		),
		'astcdr' => array(
			'form_field' => '',
			'fld_label' => _('Call Report'),
			'fld_desc' => _("store Asterisk's call detail records on disk"),
		)
	);
?>