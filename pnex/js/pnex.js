/* Expands/Collapses Advanced Search Box */


jQuery(document).ready(function(){
	pnex_adv_toggle = function(el) {
		jQuery(el).toggleClass('expand');
		var target = el.parentNode.getElementsByTagName('form')[0];
		jQuery(target).slideToggle(500);
	}
});


