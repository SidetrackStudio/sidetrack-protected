jQuery(document).ready(function($) {
	$('#submit-addip').click(function() {
		$.ajax({
			type: "POST",
			// dataType 	: 'json',
			// encode 		: true,
			url: add_new_ip_object.add_new_ip_ajaxurl,
			data: {
				// Variables defined from form
				action    : 'add_new_ip_action',
				new_ip    : $('#add-new-ip').val(),

		      	// Admin stuff
				script_name   : 'add-new-ip.js',
		      	ajaxurl: add_new_ip_object.add_new_ip_ajaxurl,
		      	nonce  : add_new_ip_object.add_new_ip_nonce,
			},
			success:function( data ) {
				$( '#add-new-ip-results' ).html( data );
				console.log(data);
			},
			error: function(){
				console.log(errorThrown);
			}
		});
	});
});
