<?php
global $ucf_plugin_admin_menu;
$current_menu = $ucf_plugin_admin_menu[ $ucf_current_menu ];
$ucf_forms = ucf_get_forms();

if ( isset( $_REQUEST[ 'ucf_form_id' ] ) && $ucf_forms[ $_REQUEST[ 'ucf_form_id' ] ] ) {
	$ucf_form_id = $_REQUEST[ 'ucf_form_id' ];
}

if ( isset( $_REQUEST[ 'ucf_new_form' ] ) ) {
	$current_form = array(
		'form_id' => '0',
		'tag' => 'undefined',
		'name' => 'フォーム',
		'body' => 'TEST',
		'mail_to' => '',
		'mail_cc' => '',
		'usedb_typ' => '01',
	);
} else {
	if ( !$ucf_form_id ) {
		$current_form = array_shift( array_values( $ucf_forms ) );
	} else {
		$current_form = $ucf_forms[ $ucf_form_id ];
	}
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

//global $wpdb;
//vd( $wpdb->queries );
?>
<div class="wrap">
<?php screen_icon( 'ucf-'.$ucf_current_menu ); ?>
<h2><?php echo $current_menu[ 'title' ]; ?></h2>
<form action="<?php echo $current_menu[ 'action' ]; ?>" method="post">
<?php wp_nonce_field('update-options'); ?>
<input type="hidden" name="ucf_form_id" value="<?php echo $current_form[ 'form_id' ]; ?>" />
<h3>フォームの定義</h3>

<ul class="subsubsub">
<?php foreach ( $ucf_forms as $form ) : ?>
<li><a <?php if ( $form[ 'form_id' ] == $current_form[ 'form_id' ] ) : ?>class="current"<?php endif; ?> href="<?php echo $current_menu[ 'action' ].'&ucf_form_id='.$form[ 'form_id' ]; ?>"><?php echo $form[ 'name' ]; ?></a> |</li>
<?php endforeach; ?>
<li><a <?php if ( '0' == $current_form[ 'form_id' ] ) : ?>class="current"<?php endif; ?> href="<?php echo $current_menu[ 'action' ].'&ucf_new_form'; ?>">新規作成</a></li></ul>

<div class="ucf_form_tab_page">

<table class="form-table">
<tr valign="top">
<th scope="row"><label for="ucf_form_tag">生成フォームタグ</label></th>
<td><input name="ucf_form_tag" type="text" id="ucf_form_tag" value="<?php echo $current_form[ 'tag' ]; ?>" class="regular-text" /><br />
<label class="ucf_form_tag"><input type="text" value="[form <?php echo $current_form[ 'tag' ]; ?>]" readonly="readonly" /><br />
このコードを投稿またはページの本文に入力してください。</label></td>
</tr>
<tr valign="top">
<th scope="row"><label for="ucf_form_name">フォーム名</label></th>
<td><input name="ucf_form_name" type="text" id="ucf_form_name" value="<?php echo htmlspecialchars( $current_form[ 'name' ] ); ?>" class="regular-text" /></td>
</tr>
<tr valign="top">
<th scope="row"><label for="ucf_form_body">フォームHTML</label></th>
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
<td><select name="ucf_form_usedb_type" id="ucf_form_usedb_type">
<option value="01">通常使用</option>
<option value="02">個人情報を保存しない</option>
<option value="09">使用しない</option>
</select></td>
</tr>
</table>

<p class="submit">
<input type="submit" value="<?php _e('Save Changes') ?>" class="button-primary" name="Submit">
</p>

</div>

</form>
</div>