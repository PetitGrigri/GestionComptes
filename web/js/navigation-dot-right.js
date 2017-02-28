$(document).ready(function(){
	$(window).on('scroll',function(){
		if ($(document).scrollTop()>50) {
			$('#menu_top').removeClass('transparent');
			$('#navigation_puces').show();
		}
		else
		{
			$('#menu_top').addClass('transparent');
			$('#navigation_puces').hide();
		}
	})
});
