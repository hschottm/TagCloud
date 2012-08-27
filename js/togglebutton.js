$(document).ready(function() {
	$('.toggle-button').each(function(index) {
		$(this).removeClass('off');
		$(this).click(function () {
			$(this).blur();
			var display = ($(this).parent().next().css('display') == 'none') ? 'block' : 'none';
			$(this).parent().next().css('display', display); 
			(display == 'none') ? $(this).addClass('yes') : $(this).removeClass('yes');
		});
	});
	
});
