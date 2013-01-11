var $ = jQuery.noConflict();
jQuery(function($){
	$("#a_codecs").bsmSelect({
		addItemTarget: 'bottom',
		animate: true,
		highlight: false,
		removeLabel: '<img src="img/delete9x9.png" alt="Delete Item">',
		plugins: [
			$.bsmSelect.plugins.sortable(
				{axis : 'y', opacity : 0.9},
				{listSortableClass: 'bsmListSortableCustom'}
			),
			$.bsmSelect.plugins.compatibility()
		]
	});
});
//
jQuery(function($){
	$("#v_codecs").bsmSelect({
		addItemTarget: 'bottom',
		animate: true,
		highlight: false,
		removeLabel: '<img src="img/delete9x9.png" alt="Delete Item">',
		plugins: [
			$.bsmSelect.plugins.sortable(
				{axis : 'y', opacity : 0.9},
				{listSortableClass: 'bsmListSortableCustom'}
			),
			$.bsmSelect.plugins.compatibility()
		]
	});
});