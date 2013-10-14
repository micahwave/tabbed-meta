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

	// sortable for managing a preloaded list
	$('.tm-field-sorter .sorter-list').sortable({
		placeholder: 'placeholder',
		update: function(ui, e) {
			
			var ids = [];

			$(this).find('li').each(function(){
				ids.push( $(this).data('id') );
			});

			$(this).parent().find('.sorter-ids').val( ids.join(',') );
		}
	});

});