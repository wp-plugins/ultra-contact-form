<?php
/*
Plugin Name: Ultra Contact Form
Plugin URI: http://wordpress.org/extend/plugins/ultra-contact-form/
Version: 0.0
Description: User-friendly contact form and Intuitive inbox.
Author: COLORCHIPS
Author URI: http://www.colorchips.co.jp/
Text Domain: ucf_plugin
*/

// plugin version
define( 'UCF_PLUGIN_VERSION', '0.0' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}

// plugin basename, path, url
define( 'UCF_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'UCF_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'UCF_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );

require_once 'ucf-form.php';

if ( class_exists( 'Ultra_Contact_Form', false ) )
	add_action( 'init', array( 'Ultra_Contact_Form', 'initialize' ) );

class Ultra_Contact_Form
{
	static function initialize() {
		global $ucf_plugin_admin_menu;
		
		// localize
		load_plugin_textdomain( 'ucf_plugin', false, dirname( UCF_PLUGIN_BASENAME ) . '/languages' );
		
		// admin menu
		$ucf_plugin_admin_menu = array(
			'inbox'			=> array( 'title' => __( 'Inbox', 'ucf_plugin' ), 'menu' => __( 'Inbox', 'ucf_plugin' ), 'level' => 8 ),
			'form-manager'	=> array( 'title' => __( 'Forms', 'ucf_plugin' ), 'menu' => __( 'Forms', 'ucf_plugin' ), 'level' => 8 ),
			'form-add'		=> array( 'title' => __( 'Add New Form', 'ucf_plugin' ), 'menu' => __( 'Add New Form', 'ucf_plugin' ), 'level' => 8 ),
			'preference'	=> array( 'title' => __( 'Preference', 'ucf_plugin' ), 'menu' => __( 'Preference', 'ucf_plugin' ), 'level' => 8 ),
		);
		
		// add admin hooks
		add_action( 'admin_menu', array( 'Ultra_Contact_Form', 'admin_menu' ) );
		add_action( 'admin_init', array( 'Ultra_Contact_Form', 'admin_init' ) );
		
		// plugin hooks
		add_action( 'plugin_action_links_' . UCF_PLUGIN_BASENAME, array( 'Ultra_Contact_Form', 'add_plugin_action' ), 10, 4 );
		add_filter( 'plugin_row_meta', array( 'Ultra_Contact_Form', 'add_plugin_meta' ), 10, 2 );
	}
	
	static function admin_menu() {
		global $ucf_plugin_admin_menu;
		
		foreach ( $ucf_plugin_admin_menu as $name => $menu ) {
			$ucf_plugin_admin_menu[ $name ][ 'slug' ] = "ucf-$name";
			$ucf_plugin_admin_menu[ $name ][ 'action' ] = "admin.php?page=ucf-$name";
			$ucf_plugin_admin_menu[ $name ][ 'function' ] = create_function( '', 'Ultra_Contact_Form::page_function( "'.$name.'" );' );
		}
		$menus = $ucf_plugin_admin_menu;
		$parent_name = array_shift( array_keys( $menus ) );
		$parent = array_shift( $menus );
		
		$unread_count = get_option( "ucf_plugin_unread_count" );
		//$unread_count = 13;
		if ( $unread_count > 0 ) {
			$awaiting = '<span id="awaiting-mod" class="count-'.$unread_count.'"><span class="pending-count">'.$unread_count.'</span></span>';
			add_object_page( $parent[ 'title' ], $parent[ 'menu' ].$awaiting, $parent[ 'level' ], $parent[ 'slug' ], $parent[ 'function' ], UCF_PLUGIN_DIR_URL . 'images/ucf-menu-icon-in.png' );
		} else {
			add_object_page( $parent[ 'title' ], $parent[ 'menu' ], $parent[ 'level' ], $parent[ 'slug' ], $parent[ 'function' ], UCF_PLUGIN_DIR_URL . 'images/ucf-menu-icon.png' );
		}
		foreach ( $ucf_plugin_admin_menu as $name => $menu ) {
			$ucf_plugin_admin_menu[ $name ][ 'hook' ] = add_submenu_page( $parent[ 'slug' ], $menu[ 'title' ], $menu[ 'menu' ], $menu[ 'level' ], $menu[ 'slug' ], $menu[ 'function' ] );
		}
		
		//add_filter( 'custom_menu_order', create_function( '', 'return true;' ) );
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
	
	static function page_function( $name ) {
		global $ucf_plugin_admin_menu;
		global $current_screen;
		$current_menu = $ucf_plugin_admin_menu[ $name ];
		
		switch ( $name ) {
			case 'form-manager':
				if ( isset( $_REQUEST[ 'form_id' ] ) && $form = UCF_Form::get_form( $_REQUEST[ 'form_id' ] ) ) {
					$title = __( 'Edit Form', 'ucf_plugin' );
					include( dirname( __FILE__ ) . "/admin/form.php" );
					break;
				}
			default:
				$title = $current_menu[ 'title' ];
				include( dirname( __FILE__ ) . "/admin/$name.php" );
				break;
		}
	}
	
	static function admin_init() {
		global $ucf_plugin_admin_menu;
		
		foreach ( $ucf_plugin_admin_menu as $menu ) {
			// ucf head actions
			add_action( 'admin_head-' . $menu[ 'hook' ], array( 'Ultra_Contact_Form', 'admin_head' ) );
			// append script and style
			add_action( 'admin_print_scripts-' . $menu[ 'hook' ], array( 'Ultra_Contact_Form', 'admin_scripts' ) );
			add_action( 'admin_print_styles-' . $menu[ 'hook' ], array( 'Ultra_Contact_Form', 'admin_styles' ) );
		}
		
		// translate plugin data
		add_filter( 'all_plugins', array( 'Ultra_Contact_Form', 'translate_plugins' ) );
		
		// display notice
		//add_action( 'admin_notices', array( 'Ultra_Contact_Form', 'admin_notice' ) );
		
		register_column_headers( 'ucf-form-manager', array(
			'cb' => '<input type="checkbox" />',
			'name' => __( 'Name', 'ucf_plugin' ),
			'tag' => __( 'Tag', 'ucf_plugin' ),
			'mail_to' => __( 'TO', 'ucf_plugin' ),
			'mail_cc' => __( 'CC', 'ucf_plugin' ),
			'usedb_type' => __( 'Use DB Type', 'ucf_plugin' ),
		) );
	}
	
	static function admin_head() {
		// customize favorite actions
		add_filter( 'favorite_actions', array( 'Ultra_Contact_Form', 'customize_favorite_actions' ) );
	}
	
	static function admin_scripts() {
		wp_enqueue_script( 'ucf_admin_script', UCF_PLUGIN_DIR_URL . '/js/ucf-admin.js', array( 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable', 'jquery-form' ), UCF_PLUGIN_VERSION, 'all' );
	}
	
	static function admin_styles() {
		wp_enqueue_style( 'ucf_admin_style', UCF_PLUGIN_DIR_URL . '/css/ucf-admin.css', array(), UCF_PLUGIN_VERSION, 'all' );
	}
	
	static function customize_favorite_actions( $actions ) {
		global $ucf_plugin_admin_menu;
		$actions = array();
		foreach ( $ucf_plugin_admin_menu as $name => $menu ) {
			$actions[ $menu[ 'action' ] ][] = $menu[ 'menu' ];
			$actions[ $menu[ 'action' ] ][] = $menu[ 'level' ];
		}
		return $actions;
	}
	
	static function add_plugin_action( $links ) {
		global $ucf_plugin_admin_menu;
		$actions = array(
			'preference' => '<a href="' . $ucf_plugin_admin_menu[ 'preference' ][ 'action' ] . '">' . $ucf_plugin_admin_menu[ 'preference' ][ 'title' ] . '</a>',
		);
		return array_merge( $actions, $links );
	}
	
	static function add_plugin_meta( $links, $file ) {
		if ( $file == UCF_PLUGIN_BASENAME ) {
			$links[] = 'Some Icons by <a href="http://p.yusukekamiyamane.com/">Yusuke Kamiyamane</a>.';
		}
		return $links;
	}
	
	static function translate_plugins( $plugins ){
		$plugins[ UCF_PLUGIN_BASENAME ] = get_plugin_data( __FILE__, false, true );
		return $plugins;
	}
	
	static function admin_notice() {
		
		$deactivate = array(
			'action' => 'deactivate',
			'plugin' => UCF_PLUGIN_BASENAME,
		);
		if ( isset( $_REQUEST['plugin_status'] ) )
			$deactivate['plugin_status'] = $_REQUEST['plugin_status'];
		if ( isset( $_REQUEST['paged'] ) )
			$deactivate['paged'] = $_REQUEST['paged'];
		
		$deactivate['_wpnonce'] = wp_create_nonce( 'deactivate-plugin_' . UCF_PLUGIN_BASENAME );
		
		echo '<div class="updated fade"><p>';
		echo '<strong>Ultra Contact Form</strong>: ';
		echo __( 'This plugin does not work correctly.', 'ucf_plugin' );
		echo '[<a href="plugins.php?'.http_build_query( $deactivate ).'">'.__( 'Deactivate' )."</a>]";
		echo "</p></div>";
	}
}


add_action( 'the_content', 'ucf_the_content' );
function ucf_the_content( $content ) {
	
	$forms = UCF_Form::get_forms();
	
	$form_head = '<form action="#" method="post">';
	$form_foot = '</form>';
	
	foreach ( $forms as $form ) {
		$form_body = $form->body;
		$form_body = preg_replace( '/\[name\]/', '<input type="text" name="name" />', $form_body );
		$form_body = preg_replace( '/\[mail\]/', '<input type="text" name="mail" />', $form_body );
		
		$form_body = preg_replace( '/\[text (.*)\]/', '<input type="text" name="$1" />', $form_body );
		$form_body = preg_replace( '/\[textarea (.*)\]/', '<textarea name="$1"></textarea>', $form_body );
		$form_body = preg_replace( '/\[submit ?"(.*)"\]/', '<input type="submit" value="$1" />', $form_body );
		
		$html = array( $form_head, $form_body, $form_foot );
		$content = preg_replace( '/\[form '.$form->tag.'\]/', join( PHP_EOL, $html ), $content );
	}
	
	return $content;
}

function shortcode_exists($tag) {
	global $shortcode_tags;
	return isset($shortcode_tags[$tag]);
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

