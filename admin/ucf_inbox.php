<?php
?>
<div class="wrap">
<?php screen_icon( $ucf_current_menu[ 'slug' ] ); ?>
<h2><?php echo esc_html( $title ); ?></h2>
<form method="get" action="http://sandbox2.wordpress.org/wp-admin/edit.php" id="posts-filter">

<ul class="subsubsub">
<li><a class="current" href="edit.php?post_type=post">すべて <span class="count">(0)</span></a> |</li>
<li><a href="edit.php?post_status=publish&amp;post_type=post">未読 <span class="count">(0)</span></a> |</li>
<li><a href="edit.php?post_status=draft&amp;post_type=post">既読 <span class="count">(0)</span></a> |</li>
<li><a href="edit.php?post_status=trash&amp;post_type=post">ゴミ箱 <span class="count">(0)</span></a></li></ul>

<p class="search-box">
	<label for="post-search-input" class="screen-reader-text">投稿を検索:</label>
	<input type="text" value="" name="s" id="post-search-input">
	<input type="submit" class="button" value="投稿を検索">
</p>

<input type="hidden" value="all" class="post_status_page" name="post_status">
<input type="hidden" value="post" class="post_type_page" name="post_type">
<input type="hidden" value="list" name="mode">


<div class="tablenav">

<div class="alignleft actions">
<select name="action">
<option selected="selected" value="-1">一括操作</option>
<option value="edit">既読にする</option>
<option value="edit">未読にする</option>
<option value="trash">ゴミ箱へ移動</option>
</select>
<input type="submit" class="button-secondary action" id="doaction" name="doaction" value="適用">
<input type="hidden" value="c022a3d48f" name="_wpnonce" id="_wpnonce"><input type="hidden" value="/wp-admin/edit.php" name="_wp_http_referer">
<select name="m">
<option value="0" selected="selected">日付指定なし</option>
<option value="201009">9月 2010</option>
<option value="201008">8月 2010</option>
</select>

<input type="submit" class="button-secondary" value="フィルター" id="post-query-submit">
</div>


<div class="view-switch">
	<a href="/wp-admin/edit.php?mode=list"><img width="20" height="20" alt="一覧表示" title="一覧表示" src="http://sandbox2.wordpress.org/wp-includes/images/blank.gif" id="view-switch-list" class="current"></a>
	<a href="/wp-admin/edit.php?mode=excerpt"><img width="20" height="20" alt="抜粋表示" title="抜粋表示" src="http://sandbox2.wordpress.org/wp-includes/images/blank.gif" id="view-switch-excerpt"></a>
</div>

<div class="clear"></div>
</div>

<div class="clear"></div>

<table cellspacing="0" class="widefat post fixed">
	<thead>
	<tr>
	<th style="" class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"></th>
	<th style="" class="manage-column column-title" id="title" scope="col">タイトル</th>
	<th style="" class="manage-column column-author" id="author" scope="col">作成者</th>
	<th style="" class="manage-column column-date" id="date" scope="col">日付</th>
	</tr>
	</thead>

	<tfoot>
	<tr>
	<th style="" class="manage-column column-cb check-column" scope="col"><input type="checkbox"></th>
	<th style="" class="manage-column column-title" scope="col">タイトル</th>
	<th style="" class="manage-column column-author" scope="col">作成者</th>
	<th style="" class="manage-column column-date" scope="col">日付</th>
	</tr>
	</tfoot>

	<tbody>
	<tr valign="top" class="alternate author-self status-draft iedit" id="post-8">
		<th class="check-column" scope="row"><input type="checkbox" value="8" name="post[]"></th>
				<td class="post-title column-title"><strong><a title="“下書き” を編集する" href="http://sandbox2.wordpress.org/wp-admin/post.php?post=8&amp;action=edit" class="row-title">下書き</a> - <span class="post-state">下書き</span></strong>
		<div class="row-actions"><span class="edit"><a title="この項目を編集" href="http://sandbox2.wordpress.org/wp-admin/post.php?post=8&amp;action=edit">編集</a> | </span><span class="inline hide-if-no-js"><a title="この項目をインラインで編集" class="editinline" href="#">クイック編集</a> | </span><span class="trash"><a href="http://sandbox2.wordpress.org/wp-admin/post.php?post=8&amp;action=trash&amp;_wpnonce=befc8503e1" title="この項目をゴミ箱へ移動する " class="submitdelete">ゴミ箱</a> | </span><span class="view"><a rel="permalink" title="“下書き” をプレビュー" href="http://sandbox2.wordpress.org/?p=8&amp;preview=true">プレビュー</a></span></div>
<div id="inline_8" class="hidden">
	<div class="post_title">下書き</div>
	<div class="post_name"></div>
	<div class="post_author">1</div>
	<div class="comment_status">open</div>
	<div class="ping_status">open</div>
	<div class="_status">draft</div>
	<div class="jj">16</div>
	<div class="mm">08</div>
	<div class="aa">2010</div>
	<div class="hh">16</div>
	<div class="mn">01</div>
	<div class="ss">03</div>
	<div class="post_password"></div><div id="category_8" class="post_category">1</div><div id="post_tag_8" class="tags_input"></div><div class="sticky"></div></div>		</td>
				<td class="author column-author"><a href="edit.php?post_type=post&amp;author=1">admin</a></td>
		<td class="date column-date"><abbr title="2010年8月16日 4:01:03 pm">2010年8月16日</abbr><br>最終編集日</td>	</tr>
	<tr valign="top" class="author-self status-publish iedit" id="post-1">
		<th class="check-column" scope="row"><input type="checkbox" value="1" name="post[]"></th>
				<td class="post-title column-title"><strong><a title="“Hello world!” を編集する" href="http://sandbox2.wordpress.org/wp-admin/post.php?post=1&amp;action=edit" class="row-title">Hello world!</a></strong>
		<div class="row-actions"><span class="edit"><a title="この項目を編集" href="http://sandbox2.wordpress.org/wp-admin/post.php?post=1&amp;action=edit">編集</a> | </span><span class="inline hide-if-no-js"><a title="この項目をインラインで編集" class="editinline" href="#">クイック編集</a> | </span><span class="trash"><a href="http://sandbox2.wordpress.org/wp-admin/post.php?post=1&amp;action=trash&amp;_wpnonce=9d97c175e1" title="この項目をゴミ箱へ移動する " class="submitdelete">ゴミ箱</a> | </span><span class="view"><a rel="permalink" title="“Hello world!” を表示" href="http://sandbox2.wordpress.org/2010/08/12/hello-world/">表示</a></span></div>
<div id="inline_1" class="hidden">
	<div class="post_title">Hello world!</div>
	<div class="post_name">hello-world</div>
	<div class="post_author">1</div>
	<div class="comment_status">open</div>
	<div class="ping_status">open</div>
	<div class="_status">publish</div>
	<div class="jj">12</div>
	<div class="mm">08</div>
	<div class="aa">2010</div>
	<div class="hh">05</div>
	<div class="mn">33</div>
	<div class="ss">57</div>
	<div class="post_password"></div><div id="category_1" class="post_category">1</div><div id="post_tag_1" class="tags_input">Apple, iphone, 処理速度, 対処法, 速くする, 重い</div><div class="sticky"></div></div>		</td>
				<td class="author column-author"><a href="edit.php?post_type=post&amp;author=1">admin</a></td>
		<td class="date column-date"><abbr title="2010年8月12日 5:33:57 am">2010年8月12日</abbr><br>公開済み</td>	</tr>
	</tbody>
</table>
<div class="tablenav">


<div class="alignleft actions">
<select name="action2">
<option selected="selected" value="-1">一括操作</option>
<option value="edit">編集</option>
<option value="trash">ゴミ箱へ移動</option>
</select>
<input type="submit" class="button-secondary action" id="doaction2" name="doaction2" value="適用">
<br class="clear">
</div>
<br class="clear">
</div>


</form>
</div>