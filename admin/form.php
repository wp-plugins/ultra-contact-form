<?php
global $ucf_plugin_admin_menu;

wp_reset_vars(array('action', 'cat_id', 'linkurl', 'name', 'image', 'description', 'visible', 'target', 'category', 'link_id', 'submit', 'order_by', 'links_show_cat_id', 'rating', 'rel', 'notes', 'linkcheck[]'));

if ( ! current_user_can('manage_links') )
	wp_die( __('You do not have sufficient permissions to edit the links for this site.') );

if ( !empty($_POST['deletebookmarks']) )
	$action = 'deletebookmarks';
if ( !empty($_POST['move']) )
	$action = 'move';
if ( !empty($_POST['linkcheck']) )
	$linkcheck = $_POST['linkcheck'];

$this_file = 'link-manager.php';

switch ($action) {
	case 'deletebookmarks' :
		check_admin_referer('bulk-bookmarks');

		//for each link id (in $linkcheck[]) change category to selected value
		if (count($linkcheck) == 0) {
			wp_redirect($this_file);
			exit;
		}

		$deleted = 0;
		foreach ($linkcheck as $link_id) {
			$link_id = (int) $link_id;

			if ( wp_delete_link($link_id) )
				$deleted++;
		}

		wp_redirect("$this_file?deleted=$deleted");
		exit;
		break;

	case 'move' :
		check_admin_referer('bulk-bookmarks');

		//for each link id (in $linkcheck[]) change category to selected value
		if (count($linkcheck) == 0) {
			wp_redirect($this_file);
			exit;
		}
		$all_links = join(',', $linkcheck);
		// should now have an array of links we can change
		//$q = $wpdb->query("update $wpdb->links SET link_category='$category' WHERE link_id IN ($all_links)");

		wp_redirect($this_file);
		exit;
		break;

	case 'add' :
		check_admin_referer('add-bookmark');

		$redir = wp_get_referer();
		if ( add_link() )
			$redir = add_query_arg( 'added', 'true', $redir );

		wp_redirect( $redir );
		exit;
		break;

	case 'save' :
		$link_id = (int) $_POST['link_id'];
		check_admin_referer('update-bookmark_' . $link_id);

		edit_link($link_id);

		wp_redirect($this_file);
		exit;
		break;

	case 'delete' :
		$link_id = (int) $_GET['link_id'];
		check_admin_referer('delete-bookmark_' . $link_id);

		wp_delete_link($link_id);

		wp_redirect($this_file);
		exit;
		break;

	default :
	case 'edit' :
		//wp_enqueue_script('link');
		//wp_enqueue_script('xfn');

		$parent_file = 'form-manager.php';
		$submenu_file = 'form-manager.php';
		$title = __( 'Edit Form', 'ucf_plugin' );

		$form_id = (int) $_GET['form_id'];

		if (!$form = UCF_Form::get_form( $form_id, OBJECT, 'edit' ) )
			wp_die( __( 'Form not found.', 'ucf_plugin' ) );

		include ('edit-form.php');
		break;

	//default :
	//	break;
}
