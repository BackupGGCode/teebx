/*
  $Id$
  - Part of BoneOS build platform (http://www.teebx.com/)
  - look at TeeBX website to get details about license
*/
var $ecf = jQuery.noConflict();
$ecf(document).ready(function(){
	$ecf('#dhcp').change(function (){
		if ($ecf('#dhcp').val() == "no"){
			$ecf('#ipaddr').prop('disabled', false);
			$ecf('#subnet').prop('disabled', false);
			$ecf('#gateway').prop('disabled', false);
			//
			$ecf('#ipaddr').addClass('required');
		}
		else{
			$ecf('#ipaddr').prop('disabled', true);
			$ecf('#subnet').prop('disabled', true);
			$ecf('#gateway').prop('disabled', true);
			//
			$ecf('#ipaddr').removeClass('required');
		}
	}).change(); // sync initial state
	//
	$ecf('#topology').change(function (){
		if ($ecf('#topology').val() == "natstatic"){
			$ecf('#extipaddr').prop('disabled', false);
			$ecf('#exthostname').prop('disabled', true);
			$ecf('input:radio[name="hostnameupdatesrc"]').prop('disabled', true);
			//
			$ecf('#extipaddr').addClass('required');
			$ecf('#exthostname').removeClass('required');
		}
		else if ($ecf('#topology').val() == "natdynamichost"){
			$ecf('#extipaddr').prop('disabled', true);
			$ecf('#exthostname').prop('disabled', false);
			$ecf('input:radio[name="hostnameupdatesrc"]').prop('disabled', false);
			//
			$ecf('#exthostname').addClass('required');
			$ecf('#extipaddr').removeClass('required');
		}
		else {
			$ecf('#extipaddr').prop('disabled', true);
			$ecf('#exthostname').prop('disabled', true);
			$ecf('input:radio[name="hostnameupdatesrc"]').prop('disabled', true);
			//
			$ecf('#extipaddr').removeClass('required');
			$ecf('#exthostname').removeClass('required');
		}
		$ecf('input:radio[name="hostnameupdatesrc"]').change();
	}).change(); // sync initial state
	//
	$ecf('input:radio[name="hostnameupdatesrc"]').change(function (){
		if ($ecf('#topology option:selected').val() == 'natdynamichost' && $ecf('#hostnameupdatesrc_1').is(':checked')){
			$ecf('#dyndnstype').prop('disabled', false);
			$ecf('#dyndnsusername').prop('disabled', false);
			$ecf('#dyndnspassword').prop('disabled', false);
			$ecf('#dyndnswildcard').prop('disabled', false);
			//
			$ecf('#dyndnsusername').addClass('required');
			$ecf('#dyndnspassword').addClass('required');
		}
		else {
			$ecf('#dyndnstype').prop('disabled', true);
			$ecf('#dyndnsusername').prop('disabled', true);
			$ecf('#dyndnspassword').prop('disabled', true);
			$ecf('#dyndnswildcard').prop('disabled', true);
			//
			$ecf('#dyndnsusername').removeClass('required');
			$ecf('#dyndnspassword').removeClass('required');
		}
	}).change(); // sync initial state
	//
	var regex = /^([a-zA-Z\-\_]*)(\d+)$/i;
	// static route dynamic fields collections
	var prefix = 'route';
	var cloneIndex = $ecf('.clone').length;
	$ecf('#addrow').live('click', function(){
		suffix = '_' + cloneIndex;
		$ecf('#routetpl').clone()
			.appendTo('#rw_sets')
			.attr('id', 'routetpl' + suffix)
			.attr('class', 'clone')
			.css({visibility: "visible"})
			.find('*').each(function() {
				var id = this.id || '';
				this.id = id + suffix;
				if (this.name)
				{
					this.name = prefix + '[' + cloneIndex + '][' + this.name + ']';
				}
				if (this.disabled)
				{
					this.disabled = '';
				}
		});
		$ecf('#remove_' + (cloneIndex)).css({visibility: "visible"});
		cloneIndex++;
	});

	$ecf('.remove').live('click', function(event){
		var btn = event.target.id.match(regex) || [];
		if (btn.length == 3) {
			if (btn[2] >= 0)
			{
				$ecf('#routetpl_' + (btn[2])).remove();
			}
		}
	});
	// system dns fields
	var cloneIndex = $ecf('.dnsclone').length;
	$ecf('#adddns').live('click', function(){
		suffix = '_' + cloneIndex;
		$ecf('#dnstpl').clone()
			.appendTo('#rw_dnsopts')
			.attr('id', 'dnstpl' + suffix)
			.attr('class', 'dnsclone')
			.css({visibility: "visible"})
			.find('*').each(function() {
				var id = this.id || '';
				this.id = id + suffix;
				if (this.name)
				{
					this.name = this.name + '[]';
				}
				if (this.disabled)
				{
					this.disabled = '';
				}
		});
		$ecf('#dnsremove_' + (cloneIndex)).css({visibility: "visible"});
		cloneIndex++;
	});

	$ecf('.dnsremove').live('click', function(event){
		var btn = event.target.id.match(regex) || [];
		if (btn.length == 3) {
			if (btn[2] >= 0)
			{
				$ecf('#dnstpl_' + (btn[2])).remove();
			}
		}
	});
	//var ipRegex = '\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b';
});