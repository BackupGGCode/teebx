var $ecf = jQuery.noConflict();
$ecf(document).ready(function(){
	$ecf('#publicaccess').change(function (){
		if ($ecf('#publicaccess').is(':checked')){
			$ecf('#publicname').prop('disabled', false);
		}
		else{
			$ecf('#publicname').prop('disabled', true);
		}
	}).change(); // match checkbox field initial state
	//
	$ecf('#emailcallnotify').change(function (){
		if ($ecf('#emailcallnotify').is(':checked')){
			$ecf('#emailcallnotifyaddress').prop('disabled', false);
		}
		else{
			$ecf('#emailcallnotifyaddress').prop('disabled', true);
		}
	}).change();
	//
	$ecf('#vmtoemail').change(function (){
		if ($ecf('#vmtoemail').is(':checked')){
			$ecf('#vmtoemailaddress').prop('disabled', false);
		}
		else{
			$ecf('#vmtoemailaddress').prop('disabled', true);
		}
	}).change();
});