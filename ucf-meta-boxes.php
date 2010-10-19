<?php

class UCF_Meta_Boxes
{
	static function add_meta_boxes() {
		global $ucf_current_menu;
		switch ( $ucf_current_menu[ 'slug' ] ) {
			case 'ucf_form':
			case 'ucf_form-add':
				$page = 'ucf_form';
				
				$context = 'normal';
				add_meta_box( 'formshortcodediv', __( 'Shortcode', 'ucf_plugin' ), array( 'UCF_Meta_Boxes', 'form_shortcode_meta_box' ), $page, $context );
				add_meta_box( 'formnamediv', __( 'Name', 'ucf_plugin' ), array( 'UCF_Meta_Boxes', 'form_name_meta_box' ), $page, $context );
				add_meta_box( 'formbodydiv', __( 'Body', 'ucf_plugin' ), array( 'UCF_Meta_Boxes', 'form_body_meta_box' ), $page, $context );
				add_meta_box( 'formmailydiv', __( 'Mail', 'ucf_plugin' ), array( 'UCF_Meta_Boxes', 'form_mail_meta_box' ), $page, $context );
				
				$context = 'side';
				add_meta_box( 'formsubmitdiv', __( 'Save', 'ucf_plugin' ), array( 'UCF_Meta_Boxes', 'form_submit_meta_box' ), $page, $context );
				
				break;
		}
	}
	
	static function form_shortcode_meta_box( $form ) { ?>
	<input type="text" name="shortcode" size="30" class="code" tabindex="1" value="<?php echo esc_attr( $form->shortcode ); ?>" id="shortcode" />
	<p><?php _e( 'Example: ', 'ucf-plugin' ); ?></p>
<?php }
	
	static function form_name_meta_box( $form ) { ?>
	<input type="text" name="form_name" size="30" tabindex="1" value="<?php echo esc_attr( $form->form_name ); ?>" id="form_name" />
	<p><?php _e( 'Example: Contact Form', 'ucf-plugin' ); ?></p>
<?php }
	
	static function form_body_meta_box( $form ) { ?>
	<textarea name="form_body" tabindex="1" rows="10" cols="50" id="form_body"><?php echo isset( $form->form_body ) ? esc_attr( $form->form_body ) : ''; ?></textarea>
	<p><?php _e( 'TODO: ', 'ucf-plugin' ); ?></p>
<?php }
	
	static function form_mail_meta_box( $form ) { ?>
	<textarea name="form_mail" tabindex="1" rows="10" cols="50" id="form_mail"><?php echo isset( $form->mail_to ) ? esc_attr( $form->mail_to ) : ''; ?></textarea>
	<p><?php _e( 'TODO: ', 'ucf-plugin' ); ?></p>
<?php }
	
	static function form_submit_meta_box( $form ) { ?>
<div class="submitbox" id="submitucf-form">

<div id="minor-publishing">

<?php // Hidden submit button early on so that the browser chooses the right button when form is submitted with Return key ?>
<div style="display:none;">
<input type="submit" name="save" value="<?php esc_attr_e('Save'); ?>" />
</div>

</div>

<div id="major-publishing-actions">
<?php do_action('post_submitbox_start'); ?>
<div id="delete-action">
<?php
if ( empty( $_GET[ 'action' ] ) || 'edit' == $_GET[ 'action' ] && current_user_can( 'ucf_manage_forms' ) ) {
$delete_url = wp_nonce_url( "admin.php?&page=ucf-form-manager&amp;action=delete&amp;form_id=$form->form_id", 'delete-bookmark_' . $form->form_id ); ?>
	<a class="submitdelete deletion" href="<?php echo $delete_url; ?>" onclick="if ( confirm('<?php echo esc_js( sprintf( __( "You are about to delete this link '%s'\n  'Cancel' to stop, 'OK' to delete."), $form->link_name ) ); ?>') ) {return true;}return false;"><?php _e( 'Delete' ); ?></a>
<?php } ?>
</div>

<div id="publishing-action">
<?php if ( !empty($form->form_id) ) { ?>
	<input name="save" type="submit" class="button-primary" id="publish" tabindex="4" accesskey="p" value="<?php esc_attr_e('Update Link') ?>" />
<?php } else { ?>
	<input name="save" type="submit" class="button-primary" id="publish" tabindex="4" accesskey="p" value="<?php esc_attr_e('Add Link') ?>" />
<?php } ?>
</div>
<div class="clear"></div>
</div>
<?php do_action('submitucf-form_box'); ?>
<div class="clear"></div>
</div>
<?php
	}
}
