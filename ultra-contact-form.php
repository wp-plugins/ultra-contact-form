<?php
/*
Plugin Name: Ultra Contact Form
Plugin URI: http://wordpress.org/extend/plugins/ultra-contact-form/
Version: 0.0
Description: User-friendly contact form and Intuitive inbox.
Author: ColorChips Co.,Ltd.
Author URI: http://www.colorchips.co.jp/
*/

// plugin version
define( 'UCF_PLUGIN_VERSION', '0.0' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}

load_plugin_textdomain( 'ucf_plugin', false, 'ultra-contact-form/languages' );

// admin menu
$ucf_plugin_admin_menu = array(
	'inbox'			=> array( 'title' => __( 'Inbox', 'ucf_plugin' ), 'menu' => __( 'Inbox', 'ucf_plugin' ), 'level' => 8 ),
	'form-setting'	=> array( 'title' => __( 'Form Setting', 'ucf_plugin' ), 'menu' => __( 'Form Setting', 'ucf_plugin' ), 'level' => 8 ),
	'preference'	=> array( 'title' => __( 'Preference', 'ucf_plugin' ), 'menu' => __( 'Preference', 'ucf_plugin' ), 'level' => 8 ),
);

add_action( 'init', 'ucf_init' );
function ucf_init() {
	
}

add_action( 'the_content', 'ucf_the_content' );
function ucf_the_content( $content ) {
	
	$forms = ucf_get_forms();
	
	$form_head = '<form action="#" method="post">';
	$form_foot = '</form>';
	
	foreach ( $forms as $form ) {
		$form_body = $form[ 'body' ];
		$form_body = preg_replace( '/\[name\]/', '<input type="text" name="name" />', $form_body );
		$form_body = preg_replace( '/\[mail\]/', '<input type="text" name="mail" />', $form_body );
		$form_body = preg_replace( '/\[submit ?"(.*)"\]/', '<input type="submit" value="$1" />', $form_body );
		
		$html = array( $form_head, $form_body, $form_foot );
		$content = preg_replace( '/\[form '.$form[ 'tag' ].'\]/', join( PHP_EOL, $html ), $content );
	}
	
	return $content;
}

add_action( 'admin_menu', 'ucf_admin_menu' );
function ucf_admin_menu() {
	global $ucf_plugin_admin_menu;
	
	foreach ( $ucf_plugin_admin_menu as $name => $menu ) {
		$ucf_plugin_admin_menu[ $name ][ 'slug' ] = "ucf_$name";
		$ucf_plugin_admin_menu[ $name ][ 'action' ] = "admin.php?page=ucf_$name";
		$ucf_plugin_admin_menu[ $name ][ 'function' ] = create_function( '', '$ucf_current_menu = "'.$name.'"; include( dirname( __FILE__ ) . "/admin/'.$name.'.php" );' );
	}
	$menus = $ucf_plugin_admin_menu;
	$parent_name = array_shift( array_keys( $menus ) );
	$parent = array_shift( $menus );
	
	$unread_count = get_option( "ucf_plugin_unread_count" );
	$unread_count = 13;
	if ( $unread_count > 0 ) {
		$awaiting = '<span id="awaiting-mod" class="count-'.$unread_count.'"><span class="pending-count">'.$unread_count.'</span></span>';
		add_object_page( $parent[ 'title' ], $parent[ 'menu' ].$awaiting, $parent[ 'level' ], $parent[ 'slug' ], $parent[ 'function' ], plugin_dir_url( __FILE__ ) . 'images/ucf-menu-icon-in.png' );
	} else {
		add_object_page( $parent[ 'title' ], $parent[ 'menu' ], $parent[ 'level' ], $parent[ 'slug' ], $parent[ 'function' ], plugin_dir_url( __FILE__ ) . 'images/ucf-menu-icon.png' );
	}
	foreach ( $ucf_plugin_admin_menu as $name => $menu ) {
		$ucf_plugin_admin_menu[ $name ][ 'hook' ] = add_submenu_page( $parent[ 'slug' ], $menu[ 'title' ], $menu[ 'menu' ], $menu[ 'level' ], $menu[ 'slug' ], $menu[ 'function' ] );
	}

	add_filter( 'custom_menu_order', create_function( '', 'return true;' ) );
	add_filter( 'menu_order', 'ucf_menu_order' );
	function ucf_menu_order( $menu ) {
		$new_menu = array();
		foreach ( $menu as $slug ) {
			switch ( $slug ) {
				case 'ucf_inbox' : break;
				case 'index.php' : $new_menu[] = $slug; $new_menu[] = 'ucf_inbox'; break;
				default : $new_menu[] = $slug; break;
			}
		}
		return $new_menu;
	}
}

add_action( 'admin_init', 'ucf_admin_init' );
function ucf_admin_init() {
	global $ucf_plugin_admin_menu;
	
	foreach ( $ucf_plugin_admin_menu as $menu ) {
		add_action( 'admin_head-' . $menu[ 'hook' ], 'ucf_admin_head' );
		add_action( 'admin_print_scripts-' . $menu[ 'hook' ], 'ucf_admin_scripts' );
		add_action( 'admin_print_styles-' . $menu[ 'hook' ], 'ucf_admin_styles' );
	}
	
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'ucf_action_links' );
	function ucf_action_links( $actions ) {
		global $ucf_plugin_admin_menu;
		$actions = array_merge( array( 'setting' => '<a href="' . $ucf_plugin_admin_menu[ 'setting' ][ 'action' ] . '">設定</a>' ), $actions );
		return $actions;
	}
	
	add_filter( 'all_plugins', 'ucf_all_plugins' );
	function ucf_all_plugins( $plugins ){
		foreach ( $plugins as $basename => $plugin ) {
			if ( $basename != plugin_basename( __FILE__ ) ) {
			} else {
				$plugins[ $basename ][ 'Description' ] = __( $plugins[ plugin_basename( __FILE__ ) ][ 'Description' ], 'ucf_plugin' );
				//$plugins[ $basename ][ 'Description' ].= '<br />Some Icons by <a href="http://p.yusukekamiyamane.com/">Yusuke Kamiyamane</a>.';
			}
		}
		return $plugins;
	}
}

function ucf_admin_scripts() {
	wp_enqueue_script( 'ucf_admin_script', plugin_dir_url( __FILE__ ) . '/js/ucf-admin.js', array(), UCF_PLUGIN_VERSION, 'all' );
}
function ucf_admin_styles() {
	wp_enqueue_style( 'ucf_admin_style', plugin_dir_url( __FILE__ ) . '/css/ucf-admin.css', array(), UCF_PLUGIN_VERSION, 'all' );
}

function ucf_admin_head() {
	global $ucf_plugin_admin_menu;
	
	add_filter( 'favorite_actions', 'ucf_favorite_actions' );
	function ucf_favorite_actions( $actions ) {
		global $ucf_plugin_admin_menu;
		$actions = array();
		foreach ( $ucf_plugin_admin_menu as $name => $menu ) {
			$actions[ $menu[ 'action' ] ][] = $menu[ 'menu' ];
			$actions[ $menu[ 'action' ] ][] = $menu[ 'level' ];
		}
		return $actions;
	}
}

function ucf_get_form( $form_id ) {
	global $wpdb;
	
	$form_table_name = $wpdb->prefix . "ucf_form";
	$query = "SELECT * FROM " . $form_table_name
		. " WHERE `form_id` = '$form_id' AND `data_status` = '" . '01' . "'"
		. " ;";
	$results = $wpdb->get_row( $query, ARRAY_A );
	
	return $results;
}

function ucf_get_forms() {
	global $wpdb;
	
	$form_table_name = $wpdb->prefix . "ucf_form";
	$query = "SELECT * FROM " . $form_table_name
		. " WHERE `data_status` = '" . '01' . "'"
		. " ;";
	$results = $wpdb->get_results( $query, ARRAY_A );
	
	$forms = array();
	foreach ( $results as $line ) {
		$forms[ $line[ 'form_id' ] ] = $line;
	}
	
	return $forms;
}

function ucf_update_form( $form_data ) {
	global $wpdb;
	
	if ( $form_data[ 'form_id' ] == 0 ) {
		return ucf_regist_form( $form_data );
	}
	
	$form_table_name = $wpdb->prefix . "ucf_form";
	$query = "UPDATE `$form_table_name` SET "
		. " `tag` = '" . $wpdb->escape( $form_data[ 'tag' ] ) . "', "
		. " `name` = '" . $wpdb->escape( $form_data[ 'name' ] ) . "', "
		. " `body` = '" . $wpdb->escape( $form_data[ 'body' ] ) . "', "
		. " `mail_to` = '" . $wpdb->escape( $form_data[ 'mail_to' ] ) . "', "
		. " `mail_cc` = '" . $wpdb->escape( $form_data[ 'mail_cc' ] ) . "', "
		. " `usedb_type` = '" . $wpdb->escape( $form_data[ 'usedb_type' ] ) . "', "
		. " `updated_at` = '" . date( 'Y-m-d H:i:s' ) . "' "
		. " WHERE `form_id` = '" . $form_data[ 'form_id' ] . "'"
		. " ;";
	$result = $wpdb->query( $query );
	
	return ucf_get_form( $form_data[ 'form_id' ] );
}

function ucf_regist_form( $form_data ) {
	global $wpdb;
	
	$form_data[ 'form_id' ] = 'NULL';
	$form_data = array_merge( array(
		'form_id' => NULL,
		'tag' => '',
		'name' => '',
		'body' => '',
		'mail_to' => '',
		'mail_cc' => '',
		'usedb_type' => '01',
		'data_status' => '01',
		'created_at' => date( 'Y-m-d H:i:s' ),
		'updated_at' => date( 'Y-m-d H:i:s' ),
	), $form_data );
	
	$form_table_name = $wpdb->prefix . "ucf_form";
	
	return $wpdb->insert( $form_table_name, $form_data );
}

register_activation_hook( __FILE__, 'ucf_activation' );
function ucf_activation() {
	global $wpdb;
	
	$contact_table_name = $wpdb->prefix . "ucf_contact";
	$contact_sql = "CREATE TABLE IF NOT EXISTS " . $contact_table_name . " (
		`contact_id` bigint(20) NOT NULL AUTO_INCREMENT,
		`from_at` datetime NOT NULL,
		`from_name` varchar(255) NOT NULL,
		`subject` varchar(255) NOT NULL,
		`body` text NOT NULL,
		`read_status` varchar(2) NOT NULL,
		`data_status` varchar(2) NOT NULL,
		`created_at` datetime NOT NULL,
		`updated_at` datetime NOT NULL,
		PRIMARY KEY ( `contact_id` ),
		UNIQUE KEY `contact_id` ( `contact_id` )
	);";
	
	$form_table_name = $wpdb->prefix . "ucf_form";
	$form_sql = "CREATE TABLE IF NOT EXISTS " . $form_table_name . " (
		`form_id` bigint(20) NOT NULL AUTO_INCREMENT,
		`tag` varchar(255) NOT NULL,
		`name` varchar(255) NOT NULL,
		`body` text NOT NULL,
		`mail_to` varchar(255) NOT NULL,
		`mail_cc` varchar(255) NULL,
		`usedb_type` varchar(2) NOT NULL,
		`data_status` varchar(2) NOT NULL,
		`created_at` datetime NOT NULL,
		`updated_at` datetime NOT NULL,
		PRIMARY KEY ( `form_id` ),
		UNIQUE KEY `form_id` ( `form_id` )
	);";
	
	if ( $wpdb->get_var( "show tables like '$contact_table_name'" ) != $contact_table_name ) {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $contact_sql );
		
		add_option( "ucf_plugin_version", UCF_PLUGIN_VERSION );
	}
	if ( $wpdb->get_var( "show tables like '$form_table_name'" ) != $form_table_name ) {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $form_sql );
		
		$form_data = array();
		$form_data[ 'tag' ] = 'default';
		$form_data[ 'name' ] = '名称未設定';
		$form_data[ 'body' ] = '<strong>TEST</strong>';
		$form_data[ 'mail_to' ] = '';
		$form_data[ 'mail_cc' ] = '';
		$form_data[ 'usedb_type' ] = '01';
		ucf_regist_form( $form_data );
		
		add_option( "ucf_plugin_version", UCF_PLUGIN_VERSION );
	}
	
	$installed_ver = get_option( "ucf_plugin_version" );
	if( $installed_ver != UCF_PLUGIN_VERSION ) {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $contact_sql );
		dbDelta( $form_sql );
		
		$form_data = array();
		$form_data[ 'tag' ] = 'default';
		$form_data[ 'name' ] = '名称未設定';
		$form_data[ 'body' ] = '<strong>TEST</strong>';
		$form_data[ 'mail_to' ] = '';
		$form_data[ 'mail_cc' ] = '';
		$form_data[ 'usedb_type' ] = '01';
		ucf_regist_form( $form_data );
		
		update_option( "ucf_plugin_version", UCF_PLUGIN_VERSION );
	}
}

register_deactivation_hook( __FILE__, 'ucf_activation' );
function ucf_deactivation() {
	//
}

