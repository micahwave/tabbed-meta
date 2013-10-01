(function($){
	

	/*
	picker = {
		init: function() {
			
		},
		add_item: function() {

		},
		remove_item: function() {

		},
		search: function() {

		},
		serialize: function() {

		}
	}
	*/

	$.fn.postPicker = function() {

		nonce = $('#tm_fields_nonce').val();

		return this.each(function(){
			
			var $self = $(this), posts = [];

			// bind picker elements
			$self.find('button').click(function(e){
				e.preventDefault();
				search();
			});

			$self.on('click', '.picker-results a', function(e){
				e.preventDefault();
				add_item( $(this).data('id') );
			});

			$self.on('click', '.picker-list .remove', function(e){
				e.preventDefault();
				remove_item( $(this).data('id') );
			});


			/**
			 * These are the posts you're looking for
			 */
			function search() {

				$.getJSON(
					ajaxurl,
					{
						action: 'get_picker_posts',
						s: $self.find('.picker-query').val(),
						_ajax_nonce: nonce
					},
					function(response) {

						if(response) {

							html = '';

							posts = response;
				
							_.each(response, function(post){
								console.log(post);

								html += _.template([
									'<div class="result">',
										'<%= post_title %>',
										'<nav>',
											'<a href="#" data-id="<%= ID %>">Add</a>',
										'</nav>',
									'</div>'
								].join(''), post);

							});

							$self.find('.picker-results').html(html);
						}
					}
				);
			}

			/**
			 *
			 */
			function add_item( id ) {
				
				$.post(
					ajaxurl,
					{
						action: 'get_picker_item',
						id: id,
						_ajax_nonce: nonce
					},
					function(response) {
						if(response) {
							$self.find('.picker-list').append(response);
							serialize();
						}
					}
				);

			}

			/**
			 *
			 */
			function remove_item( id ) {
				$self.find('.picker-list li[data-id="' + id + '"]').remove();
				serialize();
			}

			/**
			 *
			 */
			function serialize() {

				console.log('called serialize');

				var ids = [];

				$self.find('.picker-list li').each(function(){
					ids.push( $(this).data('id') );
				});

				$self.find('.picker-ids').val( ids.join(',') );
			}

		});

	};

})(jQuery);

jQuery('document').ready(function($){
	$('.tm-field-post_picker').postPicker();
});