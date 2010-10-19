<?php
switch ($order_by) {
	case 'order_id' :
		$sqlorderby = 'id';
		break;
	case 'order_url' :
		$sqlorderby = 'url';
		break;
	case 'order_desc' :
		$sqlorderby = 'description';
		break;
	case 'order_owner' :
		$sqlorderby = 'owner';
		break;
	case 'order_rating' :
		$sqlorderby = 'rating';
		break;
	case 'order_name' :
	default :
		$sqlorderby = 'form_name';
		break;
} ?>

<div class="wrap nosubsub">
<?php screen_icon( $ucf_current_menu[ 'slug' ] ); ?>
<h2><?php echo esc_html( $title ); ?> <a href="<?php echo UCF_Form::get_add_form_link(); ?>" class="button add-new-h2"><?php echo esc_html_x('Add New', 'ucf_plugin'); ?></a> <?php
if ( !empty($_GET['s']) )
	printf( '<span class="subtitle">' . __('Search results for &#8220;%s&#8221;') . '</span>', esc_html( stripslashes($_GET['s']) ) ); ?>
</h2>

<?php
if ( isset($_GET['deleted']) ) {
	echo '<div id="message" class="updated"><p>';
	$deleted = (int) $_GET['deleted'];
	printf(_n('%s link deleted.', '%s links deleted', $deleted), $deleted);
	echo '</p></div>';
	$_SERVER['REQUEST_URI'] = remove_query_arg(array('deleted'), $_SERVER['REQUEST_URI']);
}
?>

<form class="search-form" action="" method="get">
<p class="search-box">
	<input type="hidden" name="page" value="<?php echo $ucf_current_menu[ 'slug' ]; ?>" />
	<label class="screen-reader-text" for="link-search-input"><?php _e( 'Search Links' ); ?>:</label>
	<input type="text" id="link-search-input" name="s" value="<?php _admin_search_query(); ?>" />
	<input type="submit" value="<?php esc_attr_e( 'Search Links' ); ?>" class="button" />
</p>
</form>
<br class="clear" />

<form id="posts-filter" action="" method="get">
	<input type="hidden" name="page" value="<?php echo $ucf_current_menu[ 'slug' ]; ?>" />
<div class="tablenav">

<?php
if ( 'all' == $cat_id )
	$cat_id = '';
$args = array( 'category' => $cat_id, 'hide_invisible' => 0, 'orderby' => $sqlorderby, 'hide_empty' => 0 );
if ( ! empty( $_GET['s'] ) )
	$args['search'] = $_GET['s'];
$forms = UCF_Form::get_forms( $args );
if ( $forms ) {
?>

<div class="alignleft actions">
<select name="action">
<option value="" selected="selected"><?php _e('Bulk Actions'); ?></option>
<option value="delete"><?php _e('Delete'); ?></option>
</select>
<input type="submit" value="<?php esc_attr_e('Apply'); ?>" name="doaction" id="doaction" class="button-secondary action" />

<?php
/*
$categories = get_terms('link_category', array("hide_empty" => 1));
$select_cat = "<select name=\"cat_id\">\n";
$select_cat .= '<option value="all"'  . (($cat_id == 'all') ? " selected='selected'" : '') . '>' . __('View all Categories') . "</option>\n";
foreach ((array) $categories as $cat)
	$select_cat .= '<option value="' . esc_attr($cat->term_id) . '"' . (($cat->term_id == $cat_id) ? " selected='selected'" : '') . '>' . sanitize_term_field('name', $cat->name, $cat->term_id, 'link_category', 'display') . "</option>\n";
$select_cat .= "</select>\n";
*/
$select_order = "<select name=\"order_by\">\n";
$select_order .= '<option value="order_id"' . (($order_by == 'order_id') ? " selected='selected'" : '') . '>' .  __('Order by Link ID') . "</option>\n";
$select_order .= '<option value="order_name"' . (($order_by == 'order_name') ? " selected='selected'" : '') . '>' .  __('Order by Name') . "</option>\n";
$select_order .= '<option value="order_url"' . (($order_by == 'order_url') ? " selected='selected'" : '') . '>' .  __('Order by Address') . "</option>\n";
$select_order .= '<option value="order_rating"' . (($order_by == 'order_rating') ? " selected='selected'" : '') . '>' .  __('Order by Rating') . "</option>\n";
$select_order .= "</select>\n";

//echo $select_cat;
echo $select_order;

?>
<input type="submit" id="post-query-submit" value="<?php esc_attr_e('Filter'); ?>" class="button-secondary" />

</div>

<br class="clear" />
</div>

<div class="clear"></div>

<?php
	$form_columns = get_column_headers( $current_screen );
	$hidden = get_hidden_columns( $current_screen );
?>

<?php wp_nonce_field('bulk-bookmarks') ?>
<table class="widefat fixed" cellspacing="0">
	<thead>
	<tr>
<?php print_column_headers( $current_screen ); ?>
	</tr>
	</thead>

	<tfoot>
	<tr>
<?php print_column_headers( $current_screen, false); ?>
	</tr>
	</tfoot>

	<tbody>
<?php
	$alt = 0;
	
	foreach ($forms as $form) {
		$form = UCF_Form::sanitize_form($form);
		$form->name = esc_attr($form->name);
		$visible = ($form->link_visible == 'Y') ? __('Yes') : __('No');
		$rating  = $form->link_rating;
		$style = ($alt % 2) ? '' : ' class="alternate"';
		++ $alt;
		$edit_link = UCF_Form::get_edit_form_link( $form );
		$delete_link = UCF_Form::get_delete_form_link( $form );
		?><tr id="link-<?php echo $form->form_id; ?>" valign="middle" <?php echo $style; ?>><?php
		foreach( $form_columns as $column_name => $column_display_name ) {
			$class = "class=\"column-$column_name\"";

			$style = '';
			if ( in_array($column_name, $hidden) )
				$style = ' style="display:none;"';

			$attributes = "$class$style";

			switch($column_name) {
				case 'cb':
					echo '<th scope="row" class="check-column"><input type="checkbox" name="linkcheck[]" value="'. esc_attr($form->form_id) .'" /></th>';
					break;
				case 'form_name':
					echo "<td $attributes><strong><a class='row-title' href='$edit_link' title='" . esc_attr(sprintf(__('Edit &#8220;%s&#8221;'), $form->form_name)) . "'>$form->form_name</a></strong><br />";
					$actions = array();
					$actions['edit'] = '<a href="' . $edit_link . '">' . __('Edit') . '</a>';
					$actions['delete'] = "<a class='submitdelete' href='" . wp_nonce_url( $delete_link, 'delete-form_' . $form->form_id) . "' onclick=\"if ( confirm('" . esc_js(sprintf( __("You are about to delete this form '%s'\n  'Cancel' to stop, 'OK' to delete."), $form->name )) . "') ) { return true;}return false;\">" . __('Delete') . "</a>";
					$action_count = count($actions);
					$i = 0;
					echo '<div class="row-actions">';
					foreach ( $actions as $action => $formaction ) {
						++$i;
						( $i == $action_count ) ? $sep = '' : $sep = ' | ';
						echo "<span class='$action'>$formaction$sep</span>";
					}
					echo '</div>';
					echo '</td>';
					break;
				case 'shortcode':
					echo "<td $attributes><code>[$form->shortcode]</code></td>";
					break;
				case 'mail_to':
					echo "<td $attributes><a href='mailto:$form->mail_to'>$form->mail_to</a></td>";
					break;
				case 'mail_cc':
					echo "<td $attributes><a href='mailto:$form->mail_to'>$form->mail_to</a></td>";
					break;
				case 'usedb_type':
					echo "<td $attributes>" . ( $form->usedb_type == '01' ? 'あ' : 'い' ) . "</td>";
					break;
				default:
					?>
					<td <?php echo $attributes ?>><?php do_action('manage_link_custom_column', $column_name, $form->form_id); ?></td>
					<?php
					break;

			}
		}
		echo "\n    </tr>\n";
	}
?>
	</tbody>
</table>

<div class="tablenav">

<div class="alignleft actions">
<select name="action2">
<option value="" selected="selected"><?php _e('Bulk Actions'); ?></option>
<option value="delete"><?php _e('Delete'); ?></option>
</select>
<input type="submit" value="<?php esc_attr_e('Apply'); ?>" name="doaction2" id="doaction2" class="button-secondary action" />
</div>

<?php } else { ?>
<p><?php _e( 'No forms found.' ) ?></p>
<?php } ?>

<br class="clear" />
</div>

</form>

<div id="ajax-response"></div>

</div>