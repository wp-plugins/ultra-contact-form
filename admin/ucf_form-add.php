<?php
$vars = array( 'action', 'cat_id', 'linkurl', 'name', 'image', 'description', 'visible', 'target', 'category', 'form_id', 'submit', 'order_by', 'links_show_cat_id', 'rating', 'rel', 'notes', 'linkcheck[]' );
for ( $i=0; $i<count( $vars ); $i += 1 ) {
	$var = $vars[$i];
	global $$var;
	if ( empty( $_POST[$var] ) ) {
		if ( empty( $_GET[$var] ) )
			$$var = '';
		else
			$$var = $_GET[$var];
	} else {
		$$var = $_POST[$var];
	}
}

if ( ! current_user_can( 'ucf_manage_forms' ) )
	wp_die( __('You do not have sufficient permissions to edit the links for this site.') );

include('edit-form.php');
?>