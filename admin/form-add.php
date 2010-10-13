<?php
global $ucf_plugin_admin_menu;

if ( isset( $_REQUEST[ 'ucf_form_id' ] ) ) {
	$current_form = UCF_Form::get_form( $_REQUEST[ 'ucf_form_id' ] );
} else {
	$current_form = array(
		'form_id' => '0',
		'tag' => 'undefined',
		'name' => 'フォーム',
		'body' => 'TEST',
		'mail_to' => '',
		'mail_cc' => '',
		'usedb_typ' => '01',
	);
}

if ( isset( $_POST[ 'ucf_form_id' ] ) ){
	$form_data = array(
		'form_id' => $_POST[ 'ucf_form_id' ],
		'tag' => $_POST[ 'ucf_form_tag' ],
		'name' => stripslashes_deep( $_POST[ 'ucf_form_name' ] ),
		'body' => stripslashes_deep( $_POST[ 'ucf_form_body' ] ),
		'mail_to' => $_POST[ 'ucf_form_mail_to' ],
		'mail_cc' => $_POST[ 'ucf_form_mail_cc' ],
		'usedb_type' => $_POST[ 'ucf_form_usedb_type' ],
	);
	$current_form = ucf_update_form( $form_data );
	$ucf_forms[ $current_form[ 'form_id' ] ] = $current_form;
}

$form = $current_form;
include('edit-form.php');

//global $wpdb;
//vd( $wpdb->queries );

/*
?>
<div class="wrap">
<?php screen_icon( $current_menu[ 'slug' ] ); ?>
<h2><?php echo $current_menu[ 'title' ]; ?></h2>
<form action="<?php echo $current_menu[ 'action' ]; ?>" method="post">
<?php wp_nonce_field('update-options'); ?>
<input type="hidden" name="ucf_form_id" value="<?php echo $current_form[ 'form_id' ]; ?>" />

<div id="poststuff" class="metabox-holder<?php echo 2 == $screen_layout_columns ? ' has-right-sidebar' : ''; ?>">

<div id="side-info-column" class="inner-sidebar">
<?php

do_action('submitlink_box');
$side_meta_boxes = do_meta_boxes( 'link', 'side', $link );

?>
</div>


<ul class="subsubsub">
<?php foreach ( $ucf_forms as $form ) : ?>
<li><a <?php if ( $form[ 'form_id' ] == $current_form[ 'form_id' ] ) : ?>class="current"<?php endif; ?> href="<?php echo $current_menu[ 'action' ].'&ucf_form_id='.$form[ 'form_id' ]; ?>"><?php echo $form[ 'name' ]; ?></a> |</li>
<?php endforeach; ?>
<li><a <?php if ( '0' == $current_form[ 'form_id' ] ) : ?>class="current"<?php endif; ?> href="<?php echo $current_menu[ 'action' ].'&ucf_new_form'; ?>">新規作成</a></li></ul>

<div class="ucf_form_tab_page">

<table class="form-table">
<tr valign="top">
	<th scope="row"><label for="ucf_form_name"><?php _e( 'Name', 'ucf_plugin' ); ?></label></th>
	<td><input name="ucf_form_name" type="text" id="ucf_form_name" value="<?php echo htmlspecialchars( $current_form[ 'name' ] ); ?>" class="regular-text" /></td>
</tr>
<tr valign="top">
	<th scope="row"><label for="ucf_form_tag"><?php _e( 'Shortcode', 'ucf_plugin' ); ?></label></th>
	<td><input name="ucf_form_tag" type="text" id="ucf_form_tag" value="<?php echo $current_form[ 'tag' ]; ?>" class="regular-text" /></td>
</tr>
<tr valign="top">
	<th scope="row"><label for="ucf_form_body"><?php _e( 'HTML', 'ucf_plugin' ); ?></label></th>
	<td><textarea name="ucf_form_body" id="ucf_form_body" cols="30" rows="15"><?php echo $current_form[ 'body' ]; ?></textarea></td>
</tr>
<tr valign="top">
	<th scope="row"><label for="ucf_form_mail_to">送信先メールアドレス</label></th>
	<td><input name="ucf_form_mail_to" type="text" id="ucf_form_mail_to" value="<?php echo $current_form[ 'mail_to' ]; ?>" class="regular-text" /></td>
</tr>
<tr valign="top">
	<th scope="row"><label for="ucf_form_mail_cc">転送先メールアドレス</label></th>
	<td><input name="ucf_form_mail_cc" type="text" id="ucf_form_mail_cc" value="<?php echo $current_form[ 'mail_cc' ]; ?>" class="regular-text" /></td>
</tr>
<tr valign="top">
	<th scope="row"><label for="ucf_form_usedb_type">データベースの使用方法</label></th>
	<td><fieldset><legend class="screen-reader-text"><span>データベースの使用方法</span></legend>
		<p><label>
			<input type="radio" name="ucf_form_usedb_type" value="01" /> <?php _e( '通常使用', 'ucf_plugin' ); ?><br />
			<span class="description">受信箱が通常使用できます。</span>
		</label></p>
		<p><label>
			<input type="radio" name="ucf_form_usedb_type" value="02" /> <?php _e( '個人情報を保存しない', 'ucf_plugin' ); ?><br />
			<span class="description">受信箱が通常使用できます。</span>
		</label></p>
		<p><label>
			<input type="radio" name="ucf_form_usedb_type" value="09" /> <?php _e( '使用しない', 'ucf_plugin' ); ?><br />
			<span class="description">受信箱が通常使用できます。</span>
		</label></p>
	</fieldset></td>
</tr>
</table>

<p class="submit">
<input type="submit" value="<?php _e('Save Changes') ?>" class="button-primary" name="Submit">
</p>

</div>

</form>
</div>
<?php
*/
?>