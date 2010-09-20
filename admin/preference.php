<?php
global $ucf_plugin_admin_menu;

$ucf_plugin_setting_elements = array(
	'test' => array(  ),
);

?>
<div class="wrap">
<?php screen_icon( 'ucf-'.$ucf_current_menu ); ?>
<h2><?php echo $ucf_plugin_admin_menu[ $ucf_current_menu ][ 'title' ]; ?></h2>
<form action="" method="post">
<table class="form-table">
<tr valign="top">
<th scope="row"><label for="">タイトル</label></th>
<td><input name="" type="text" id="" value="" class="reqular-text" /></td>
</tr>
</table>
<p class="submit">
<input type="submit" value="変更を保存" class="button-primary" name="Submit">
</p>
</form>
</div>