(function($){

	$.fn.postPicker = function() {

		nonce = $('#tm_fields_nonce').val();

		return this.each(function(){
			
			var $self = $(this);

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
				remove_item( $(this).closest('li').data('id') );
			});

			$self.find('.picker-list').sortable({
				placeholder: 'placeholder',
				update: function(ui, e) {
					serialize();
				}
			});

			$self.find('.picker-select').change(function(){
				add_item( $(this).val() );
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
										'<a href="#" data-id="<%= ID %>" class="add">Add</a>',
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

				var data = {
					action: 'get_picker_item',
					id: id,
					_ajax_nonce: nonce
				};

				// dont allow duplicates
				if( $self.find('.picker-list li[data-id="' + id + '"]').length ) {
					alert('Sorry, this item was already added.');
					return;
				}

				// remove notice if its there
				$self.find('.notice').remove();
				
				$.post(
					ajaxurl,
					data,
					function(response) {
						if(response) {

							$self.find('.picker-list').append(response);

							serialize();

							// remove from list
							$self.find('.picker-results li[data-id="' + id + '"]').remove();

							// remove from select
							$self.find('.picker-select option[value="' + id + '"]').remove();
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

				if( $self.find('.picker-list li').length == 0 ) {
					$self.find('.picker-list').html('<p class="notice">No posts selected.</p>');
				}
			}

			/**
			 *
			 */
			function serialize() {
				
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