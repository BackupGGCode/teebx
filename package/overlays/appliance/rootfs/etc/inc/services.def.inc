<?php
/*
  $Id$
  part of BoneOS build platform (http://www.teebx.com/)
*/

	$services = array(
		'astmedia' => array(
			'form_field' => '',
			'fld_label' => gettext('Media'),
			'fld_desc' => gettext('store voice prompts and music-on-hold on disk'),
		),
		'astdb' => array(
			'form_field' => '',
			'fld_label' => gettext('Persistence'),
			'fld_desc' => gettext("store Asterisk's database on disk"),
		),
		'astcdr' => array(
			'form_field' => '',
			'fld_label' => gettext('Call Report'),
			'fld_desc' => gettext("store Asterisk's call detail records on disk"),
		)
	);
?>