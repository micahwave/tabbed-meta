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

		return this.each(function(){
			console.log('each');
			console.log( this );
		});

	};

})(jQuery);

jQuery('document').ready(function($){
	$('.tm-field-post_picker').postPicker();
});