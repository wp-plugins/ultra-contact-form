<?php

class UCF_Meta_Boxes
{
	static function form_name_meta_box( $form ) {
?>
<input type="text" name="name" size="30" tabindex="1" value="<?php echo esc_attr($form->name); ?>" id="name" />
<p><?php _e( 'Example: Contact Form', 'ucf-plugin' ); ?></p>
<?php
	}
	
	static function form_tag_meta_box( $form ) {
?>
<input type="text" name="tag" size="30" class="code" tabindex="1" value="<?php echo esc_attr($form->tag); ?>" id="tag" />
<p><?php _e( 'Example: ', 'ucf-plugin' ); ?></p>
<?php
	}
	
	static function form_body_meta_box( $form ) {
?>
<textarea name="body" tabindex="1" rows="10" cols="50" id="body"><?php echo isset($form->body) ? esc_attr($form->body) : ''; ?></textarea>
<p><?php _e( 'TODO: ', 'ucf-plugin' ); ?></p>
<?php
	}
	
	static function form_submit_meta_box( $form ) {
?>
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
if ( !empty($_GET['action']) && 'edit' == $_GET['action'] && current_user_can('manage_links') ) { ?>
	<a class="submitdelete deletion" href="<?php echo wp_nonce_url("link.php?action=delete&amp;form_id=$form->form_id", 'delete-bookmark_' . $form->form_id); ?>" onclick="if ( confirm('<?php echo esc_js(sprintf(__("You are about to delete this link '%s'\n  'Cancel' to stop, 'OK' to delete."), $form->link_name )); ?>') ) {return true;}return false;"><?php _e('Delete'); ?></a>
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
