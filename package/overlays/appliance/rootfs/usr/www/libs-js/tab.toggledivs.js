var $ttd = jQuery.noConflict();
jQuery(function($ttd){
	$ttd('#sets div.wrapper').hide();
	$ttd('#sets div.wrapper:first').show();
	$ttd('#tabs ul li:first').addClass('active');
	$ttd('#tabs ul li a:first').addClass('active');
	$ttd('#tabs ul li a').click(function(){
		$ttd('#tabs ul li').removeClass('active');
		$ttd('#tabs ul li a').removeClass('active');
		$ttd(this).parent().addClass('active');
		$ttd(this).addClass('active');
		var currentTab = $ttd(this).attr('href');
		$ttd('#sets div.wrapper').hide();
		$ttd(currentTab).show();
		return false;
	});
});
