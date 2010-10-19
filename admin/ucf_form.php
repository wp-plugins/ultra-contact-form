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

if ( !empty( $_POST['deleteforms'] ) )
	$action = 'deleteforms';
if ( !empty( $_POST['move'] ) )
	$action = 'move';
if ( !empty( $_POST['formcheck'] ) )
	$formcheck = $_POST['formcheck'];

$this_file = 'admin.php?page=ucf_form_manager';

switch ( $action ) {
	case 'deleteforms' :
		check_admin_referer( 'bulk-ucf_forms' );
		
		//for each link id (in $formcheck[]) change category to selected value
		if ( count($formcheck) == 0 ) {
			wp_redirect( $this_file );
			exit;
		}
		
		$deleted = 0;
		foreach ( $formcheck as $form_id ) {
			$form_id = (int) $form_id;
			
			if ( wp_delete_link( $form_id ) )
				$deleted++;
		}
		
		wp_redirect( add_query_arg( 'deleted', $deleted, $this_file ) );
		exit;
		break;
	
	case 'move' :
		check_admin_referer( 'bulk-ucf_forms' );
		
		//for each link id (in $formcheck[]) change category to selected value
		if ( count( $formcheck ) == 0 ) {
			wp_redirect( $this_file );
			exit;
		}
		$all_links = join( ',', $formcheck );
		// should now have an array of links we can change
		//$q = $wpdb->query("update $wpdb->links SET link_category='$category' WHERE form_id IN ($all_links)");
		
		wp_redirect( $this_file );
		exit;
		break;
	
	case 'add' :
		check_admin_referer('add-ucf_form');
		
		$redir = wp_get_referer();
		if ( $form_id = UCF_Form::add_form() )
			$redir = add_query_arg( 'added', 'true', UCF_Form::get_edit_form_link( $form_id ) );
		
		wp_redirect( $redir );
		exit;
		break;
	
	case 'save' :
		$form_id = (int) $_POST['form_id'];
		check_admin_referer( 'update-ucf_form_' . $form_id );
		
		$redir = wp_get_referer();
		if( $form_id = UCF_Form::edit_form( $form_id ) )
			$redir = add_query_arg( 'added', 'true', UCF_Form::get_edit_form_link( $form_id ) );
		
		wp_redirect( $redir );
		exit;
		break;
	
	case 'delete' :
		$form_id = (int) $_GET['form_id'];
		check_admin_referer( 'delete-ucf_form_' . $form_id );
		
		wp_delete_link($form_id);
		
		wp_redirect($this_file);
		exit;
		break;
	
	default :
	case 'edit' :
		//wp_enqueue_script('link');
		//wp_enqueue_script('xfn');
		
		$parent_file = 'admin.php?page=ucf_form_manager';
		$submenu_file = 'admin.php?page=ucf_form_manager';
		
		$form_id = (int) $_GET['form_id'];
		
		if (!$form = UCF_Form::get_form( $form_id, OBJECT, 'edit' ) )
			wp_die( __( 'Form not found.', 'ucf_plugin' ) );
		
		include( 'edit-form.php' );
		break;
}
