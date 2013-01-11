
jQuery.noConflict();

jQuery(document).ready(function(){

	jQuery.preloadImages(['/img/tri_o.gif', '/img/tri_c.gif']);

});


function showhide(tspan, tri) {
	tspanel = document.getElementById(tspan);
	triel = document.getElementById(tri);
	if (tspanel.style.display == 'none') {
		jQuery("#" + tspan).slideDown("slow");
		triel.src = "img/tri_o.gif";
	} else {
		jQuery("#" + tspan).slideUp("slow");
		triel.src = "img/tri_c.gif";
	}
}
