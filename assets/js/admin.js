jQuery(document).ready(function() {

	jQuery('.copyme-link').click(function(e) {
		e.preventDefault();
		var post_id = jQuery(this).data('post');
		jQuery('#copyme-box-' + post_id).slideToggle();
	});

	jQuery('.copyme-button').click(function(e) {
		e.preventDefault();
		var post_id = jQuery(this).data('post');
		var target_site = jQuery('#copyme-target-site-' + post_id).val();

		if( post_id > 0 && target_site > 0 ) {
			jQuery('#copyme-box-' + post_id).empty();
			jQuery('#copyme-box-' + post_id).append('<span class="copyme-copying">Copying...</span>');			
			jQuery('#copyme-link-' + post_id).remove();
			copy_item(target_site, post_id);
		}
	});

});

function copy_item(target_site, post_id) {
    post_data = 'action=copyme_copy_item&id=' + post_id+ '&target=' + target_site;
    jQuery.ajax({
		type: 'post',
		url: ajaxurl,
		data: post_data,
		dataType: 'json',
		error: function(XMLHttpRequest, textStatus, errorThrown){
			jQuery('#copyme-box-' + post_id).empty();
			jQuery('#copyme-box-' + post_id).append('<span class="copyme-ko">ERROR!</span>');
			jQuery('#copyme-box-' + post_id).show();

		},
		success: function(data, textStatus){
			if(data.response && data.response == 'OK') {
				jQuery('#copyme-box-' + post_id).empty();
				jQuery('#copyme-box-' + post_id).append('<span class="copyme-ok">Copy successful!</span>');
				jQuery('#copyme-box-' + post_id).show();
			} else {
				jQuery('#copyme-box-' + post_id).empty();
				jQuery('#copyme-box-' + post_id).append('<span class="copyme-ko">ERROR!</span>');
				jQuery('#copyme-box-' + post_id).show();				
			}			
		}
	});
}
