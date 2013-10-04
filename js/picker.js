(function($){

	$.fn.postPicker = function() {

		nonce = $('#tm_fields_nonce').val();

		return this.each(function(){
			
			var $self = $(this), limit = $self.find('.post-picker').data('limit');

			// bind picker elements
			$self.find('button').click(function(e){
				e.preventDefault();
				search();
			});

			$self.on('click', '.picker-results a', function(e){
				e.preventDefault();
				add_item( $(this).data('id'), $(this).data('title') );
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
				if( $(this).val() > 0 ) {
					add_item( $(this).val(), $('option:selected', this).text() );
				}
			});

			/**
			 * These are the posts you're looking for
			 */
			function search() {

				var data = {
					action: 'tm_search_posts',
					s: $self.find('.picker-query').val(),
					post_type: $self.find('.post-picker').data('post-type'),
					_ajax_nonce: nonce
				};

				// only search posts without a parent if doing child post picker
				if( $self.hasClass( 'tm-field-child_post_picker' ) ) {
					data.post_parent = 0;
				}

				$.getJSON(
					ajaxurl,
					data,
					function(response) {

						if(response) {

							html = '';

							posts = response;
				
							_.each(response, function(post){
								
								html += _.template([
									'<div class="result" data-id="<%= ID %>">',
										'<%= post_title %>',
										'<a href="#" data-id="<%= ID %>" data-title="<%= post_title %>" class="add">Add</a>',
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
			function add_item( id, title ) {

				// dont allow duplicates
				if( $self.find('.picker-list li[data-id="' + id + '"]').length ) {
					alert('Sorry, this item was already added.');
					return;
				}

				// dont allow more than the limit
				if( $self.find('.picker-list li').length == limit ) {
					alert('Sorry, the maximum number of items have been added.');
					return;
				}

				html = _.template([
					'<li data-id="<%= id %>">',
						'<h4><%= title %></h4>',
						'<nav>',
							'<a href="' + post_picker_settings.admin_url + 'post.php?post=<%= id %>&action=edit" target="_blank" class="edit">Edit</a>',
							'<a href="#" class="remove">Remove</a>',
							'<a href="' + post_picker_settings.home_url + '?p=<%= id %>" target="_blank">View</a>',
						'</nav>',
					'</li>'
				].join(''), { id: id, title: title } );

				// remove notice if its there
				$self.find('.notice').remove();

				$self.find('.picker-list').append(html);

				serialize();

				// remove from list
				$self.find('.picker-results .result[data-id="' + id + '"]').remove();

				// remove from select
				//$self.find('.picker-select option[value="' + id + '"]').remove();
			
			}

			/**
			 *
			 */
			function remove_item( id ) {
				$self.find('.picker-list li[data-id="' + id + '"]').remove();
				serialize();

				if( $self.find('.picker-list li').length == 0 ) {
					$self.find('.picker-list').html('<p class="notice">No items selected.</p>');
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
	$('.tm-field-post_picker, .tm-field-child_post_picker').postPicker();
});