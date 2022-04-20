jQuery(function($){

	// the Configure link click event
	$('#misha_dashboard_widget .edit-box.open-box').click(function(){
		var button = $(this);
		$.ajax({
			url: ajaxurl, // it is predefined in /wp-admin/
			type: 'POST',
			data: 'action=showform',
			beforeSend : function( xhr ){
				// add preloader
				button.hide().before('<span class="spinner" style="visibility:visible;display:block;margin:0 0 0 15px"></span>');
			},
			success : function( data ){
				// remove preloader
				button.prev().remove();
				// insert settings form
				$('#misha_dashboard_widget').find('.inside').html(data);
			}

		});
		return false;
	});
		
	// form submit event
	$('body').on('submit', '#misha_widget_settings', function(){
		var form = $(this);
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: $(this).serialize(), // all form fields
			beforeSend : function( xhr ){
				// add preloader just after the submit button
				form.find('.submit').append('<span class="spinner" style="display:inline-block;float:none;visibility:visible;margin:0 0 0 15px"></span>');
			},
			success : function( data ){
				$('#misha_dashboard_widget').find('.inside').html(data);
				// show the Configure link again
				$('#misha_dashboard_widget .edit-box.open-box').show();
			}
		});
		return false;
	});
});