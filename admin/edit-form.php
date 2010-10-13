<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

if ( ! empty($form_id) ) {
	$heading = sprintf( __( '<a href="%s">Links</a> / Edit Form' ), 'link-manager.php' );
	$submit_text = __('Update Link');
	$form_tag = '<form name="editform" id="editform" method="post" action="'.$current_menu[ 'action' ].'">';
	$nonce_action = 'update-ucf-form_' . $form_id;
} else {
	$heading = sprintf( __( '<a href="%s">Links</a> / Add New Link' ), 'link-manager.php' );
	$submit_text = __('Add Link');
	$form_tag = '<form name="addlink" id="addlink" method="post" action="'.$current_menu[ 'action' ].'">';
	$nonce_action = 'add-ucf-form';
}

require_once('./includes/meta-boxes.php');

//add_meta_box('linksubmitdiv', __('Save'), 'link_submit_meta_box', 'link', 'side', 'core');
//add_meta_box('linkcategorydiv', __('Categories'), 'link_categories_meta_box', 'link', 'normal', 'core');
//add_meta_box('linktargetdiv', __('Target'), 'link_target_meta_box', 'link', 'normal', 'core');
//add_meta_box('linkxfndiv', __('Link Relationship (XFN)'), 'link_xfn_meta_box', 'link', 'normal', 'core');
//add_meta_box('linkadvanceddiv', __('Advanced'), 'link_advanced_meta_box', 'link', 'normal', 'core');

do_action('add_meta_boxes', 'ucf-form', $form);
do_action('add_meta_boxes_ucf-form', $form);

do_action('do_meta_boxes', 'ucf-form', 'normal', $form);
do_action('do_meta_boxes', 'ucf-form', 'advanced', $form);
do_action('do_meta_boxes', 'ucf-form', 'side', $form);

add_contextual_help($current_screen,
	'<p>' . __( 'You can add or edit links on this screen by entering information in each of the boxes. Only the link&#8217;s web address and name (the text you want to display on your site as the link) are required fields.' ) . '</p>' .
	'<p>' . __( 'The boxes for link name, web address, and description have fixed positions, while the others may be repositioned using drag and drop. You can also hide boxes you don&#8217;t use in the Screen Options tab, or minimize boxes by clicking on the title bar of the box.' ) . '</p>' .
	'<p>' . __( 'XFN stands for <a href="http://gmpg.org/xfn/" target="_blank">XHTML Friends Network</a>, which is optional. WordPress allows the generation of XFN attributes to show how you are related to the authors/owners of the site to which you are linking.' ) . '</p>' .
	'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
	'<p>' . __( '<a href="http://codex.wordpress.org/Links_Add_New_SubPanel" target="_blank">Documentation on Creating Links</a>' ) . '</p>' .
	'<p>' . __( '<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>' ) . '</p>'
);

//require_once ('admin-header.php');

?>
<div class="wrap">
<?php screen_icon(); ?>
<h2><?php echo esc_html( $title ); ?></h2>

<?php if ( isset( $_GET['added'] ) ) : ?>
<div id="message" class="updated"><p><?php _e('Link added.'); ?></p></div>
<?php endif; ?>

<?php
if ( !empty($form_tag) )
	echo $form_tag;
if ( !empty($form_added) )
	echo $form_added;

wp_nonce_field( $nonce_action );
wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>

<div id="poststuff" class="metabox-holder<?php echo 2 == $screen_layout_columns ? ' has-right-sidebar' : ''; ?>">

<div id="side-info-column" class="inner-sidebar">
<?php

do_action('submitlink_box');
$side_meta_boxes = do_meta_boxes( 'form', 'side', $form );

?>
</div>

<div id="post-body">
<div id="post-body-content">

<div id="namediv" class="stuffbox">
<h3><label for="name"><?php _e( 'Name', 'ucf-plugin' ) ?></label></h3>
<div class="inside">
	<input type="text" name="name" size="30" tabindex="1" value="<?php echo esc_attr($form->name); ?>" id="name" />
	<p><?php _e( 'Example: Contact Form', 'ucf-plugin' ); ?></p>
</div>
</div>

<div id="addressdiv" class="stuffbox">
<h3><label for="tag"><?php _e( 'Tag', 'ucf-plugin' ) ?></label></h3>
<div class="inside">
	<input type="text" name="tag" size="30" class="code" tabindex="1" value="<?php echo esc_attr($form->tag); ?>" id="tag" />
	<p><?php _e( 'Example: ', 'ucf-plugin' ); ?></p>
</div>
</div>

<div id="descriptiondiv" class="stuffbox">
<h3><label for="body"><?php _e( 'Body', 'ucf-plugin' ) ?></label></h3>
<div class="inside">
	<input type="text" name="body" size="30" tabindex="1" value="<?php echo isset($form->body) ? esc_attr($form->body) : ''; ?>" id="body" />
	<p><?php _e( 'TODO: ', 'ucf-plugin' ); ?></p>
</div>
</div>

<?php

//do_meta_boxes('link', 'normal', $form);

//do_meta_boxes('link', 'advanced', $form);

if ( $form_id ) : ?>
<input type="hidden" name="action" value="save" />
<input type="hidden" name="form_id" value="<?php echo (int) $form_id; ?>" />
<input type="hidden" name="order_by" value="<?php echo esc_attr($order_by); ?>" />
<?php else: ?>
<input type="hidden" name="action" value="add" />
<?php endif; ?>

</div>
</div>
</div>

</form>
</div>
