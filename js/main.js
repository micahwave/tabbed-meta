jQuery(document).ready(function($){
	$('.tm-menu li').click(function(){
		var group = $(this).attr('data-tab'),
			$inside = $(this).closest('.inside');

		$inside.find('.tm-menu li.selected').removeClass('selected');
		$inside.find('.tm-field-group').removeClass('selected');
		$inside.find('.tm-field-group[data-tab="' + group + '"]').addClass('selected');
		$(this).addClass('selected');
	});

	// setup datepicker fields
	$('.tm-field-date input[type=text]').datepicker({
		
	});
});