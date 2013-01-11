var $ = jQuery.noConflict();
$(document).ready(function(){
	$("#tztoload").bsmSelect({
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