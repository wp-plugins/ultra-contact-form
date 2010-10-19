<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

if ( ! empty($form_id) ) {
	$form_tag = '<form name="editform" id="editform" method="post" action="'.$ucf_current_menu[ 'action' ].'">';
	$nonce_action = 'update-ucf_form_' . $form_id;
} else {
	$form_tag = '<form name="addform" id="addform" method="post" action="'.$ucf_current_menu[ 'action' ].'">';
	$nonce_action = 'add-ucf_form';
}

?>
<div class="wrap">
<?php screen_icon( $ucf_current_menu[ 'slug' ] ); ?>
<h2><?php echo esc_html( $title ); ?></h2>

<?php if ( isset( $_GET['added'] ) ) : ?>
<div id="message" class="updated"><p><?php _e('Form added.'); ?></p></div>
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

do_action( 'submitucf-form_box' );
do_meta_boxes( 'ucf_form', 'side', $form );

?>
</div>

<div id="post-body">
<div id="post-body-content">

<?php
do_meta_boxes( 'ucf_form', 'normal', $form );
do_meta_boxes( 'ucf_form', 'advanced', $form );

if ( $form_id ) : ?>
<input type="hidden" name="action" value="save" />
<input type="hidden" name="form_id" value="<?php echo (int) $form_id; ?>" />
<input type="hidden" name="order_by" value="<?php echo esc_attr( $order_by ); ?>" />
<?php else: ?>
<input type="hidden" name="action" value="add" />
<?php endif; ?>

</div>
</div>
</div>

</form>
</div>
