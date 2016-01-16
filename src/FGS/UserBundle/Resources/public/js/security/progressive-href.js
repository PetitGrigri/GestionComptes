$(document).ready(function(){
	$('body').scrollspy({target:"#navigation_puces", offset:50});
	$('#navigation_puces a,a.top, a.down, a.decouvrir').on('click',function(e) {
		e.preventDefault();

		var qui	= $(this).attr('href');
		var ou	= $(qui).offset().top;
		
		$('html, body').animate({
			scrollTop: ou
		}, 500);
	});
});