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

// static class
require_once( UCF_PLUGIN_DIR_PATH . 'ucf-form.php' );
if ( is_admin() )
	require_once( UCF_PLUGIN_DIR_PATH . 'ucf-meta-boxes.php' );

if ( class_exists( 'Ultra_Contact_Form', false ) )
	add_action( 'init', array( 'Ultra_Contact_Form', 'initialize' ) );

class Ultra_Contact_Form
{
	static function initialize() {
		// localize
		load_plugin_textdomain( 'ucf_plugin', false, dirname( UCF_PLUGIN_BASENAME ) . '/languages' );
		
		// add admin hooks
		add_action( 'admin_menu', array( 'Ultra_Contact_Form', 'admin_menu' ) );
		add_action( 'admin_init', array( 'Ultra_Contact_Form', 'admin_init' ) );
		
		// plugin hooks
		add_action( 'plugin_action_links_' . UCF_PLUGIN_BASENAME, array( 'Ultra_Contact_Form', 'add_plugin_action' ), 10, 4 );
		add_filter( 'plugin_row_meta', array( 'Ultra_Contact_Form', 'add_plugin_meta' ), 10, 2 );
	}
	
	static function admin_default_menu( $menu ){
		return array_merge( array(
			'slug' => '',
			'title' => '',
			'menu' => '',
			'level' => 8,
			'action' => "admin.php?page=" . $menu[ 'slug' ],
			'function' => array( 'Ultra_Contact_Form', 'admin_page_include' ),
		), $menu );
	}
	
	static function admin_menu() {
		global $ucf_toplevel_menu, $ucf_admin_menu;
		
		// menus
		$menus = array_map( array( 'Ultra_Contact_Form', 'admin_default_menu' ), array(
			array(
				'slug' => 'ucf_inbox',
				'title' => __( 'Inbox', 'ucf_plugin' ),
				'menu' => __( 'Inbox', 'ucf_plugin' ),
			),
			array(
				'slug' => 'ucf_form_manager',
				'title' => __( 'Forms', 'ucf_plugin' ),
				'menu' => __( 'Forms', 'ucf_plugin' ),
			),
			array(
				'slug' => 'ucf_form-add',
				'title' => __( 'Add New Form', 'ucf_plugin' ),
				'menu' => __( 'Add New Form', 'ucf_plugin' ),
			),
		) );
		
		// Unread count
		$awaiting = '';//UCF_Contact::getUnreadCount();
		if ( $unread_count > 0 )
			$awaiting = '<span id="awaiting-mod" class="count-'.$unread_count.'"><span class="pending-count">'.$unread_count.'</span></span>';
		
		// toplevel menu
		$ucf_toplevel_menu = $menus[0];
		add_object_page( $ucf_toplevel_menu[ 'title' ], $ucf_toplevel_menu[ 'menu' ].$awaiting, $ucf_toplevel_menu[ 'level' ], $ucf_toplevel_menu[ 'slug' ], $ucf_toplevel_menu[ 'function' ], UCF_PLUGIN_DIR_URL . 'images/ucf-menu-icon.png' );
		
		$ucf_admin_menu = array();
		
		// sub menus
		foreach ( $menus as $menu ) {
			$page_hook = add_submenu_page( $ucf_toplevel_menu[ 'slug' ], $menu[ 'title' ], $menu[ 'menu' ], $menu[ 'level' ], $menu[ 'slug' ], $menu[ 'function' ] );
			
			// load page
			add_action( "load-$page_hook", array( 'Ultra_Contact_Form', 'admin_page_load' ) );
			
			// global set
			$ucf_admin_menu[ $page_hook ] = $menu;
		}
	}
	
	static function admin_page_load() {
		global $current_screen, $hook_suffix, $ucf_admin_menu, $ucf_current_menu, $title;
		
		// current menu
		$page_hook_pieces[] = $current_screen->id;
		if ( !empty( $current_screen->action ) )
			$page_hook_pieces[] = $current_screen->action;
		$ucf_current_menu = $ucf_admin_menu[ join( '-', $page_hook_pieces ) ];
		
		// convert menu slug
		switch ( $ucf_current_menu[ 'slug' ] ) {
			case 'ucf_form_manager':
				// edit form
				if ( isset( $_REQUEST[ 'form_id' ] ) ) {
					$ucf_current_menu[ 'slug' ] = 'ucf_form';
					$ucf_current_menu[ 'title' ] = __( 'Edit Form', 'ucf_plugin' );
					if ( $_POST[ 'action' ] == 'save' )
						$_GET[ 'noheader' ] = true;
				}
				break;
			case 'ucf_form-add':
				if ( $_POST[ 'action' ] == 'add' ) {
					$ucf_current_menu[ 'slug' ] = 'ucf_form';
					$_GET[ 'noheader' ] = true;
				}
				break;
		}
		
		
		// override hook suffix, set screen.
		$hook_suffix = $ucf_current_menu[ 'slug' ];
		set_current_screen();
		
		// page title
		$title = $ucf_current_menu[ 'title' ];
		
		// page column
		switch ( $ucf_current_menu[ 'slug' ] ) {
			case 'ucf_form_manager':
				register_column_headers( $current_screen, array(
					'cb' => '<input type="checkbox" />',
					'form_name' => __( 'Name', 'ucf_plugin' ),
					'shortcode' => __( 'Tag', 'ucf_plugin' ),
					'mail_to' => __( 'TO', 'ucf_plugin' ),
					'mail_cc' => __( 'CC', 'ucf_plugin' ),
					'usedb_type' => __( 'Use DB Type', 'ucf_plugin' ),
				) );
				break;
			case 'ucf_form':
			case 'ucf_form-add':
				break;
		}
		
		// ucf head actions
		add_action( "admin_head-$hook_suffix", array( 'Ultra_Contact_Form', 'admin_page_head' ) );
		
		// append script and style
		add_action( "admin_print_scripts-$hook_suffix", array( 'Ultra_Contact_Form', 'admin_page_scripts' ) );
		add_action( "admin_print_styles-$hook_suffix", array( 'Ultra_Contact_Form', 'admin_page_styles' ) );
		
		// help
		add_contextual_help( $current_screen,
			'<p>' . __( 'You can add or edit links on this screen by entering information in each of the boxes. Only the link&#8217;s web address and name (the text you want to display on your site as the link) are required fields.' ) . '</p>' .
			'<p>' . __( 'The boxes for link name, web address, and description have fixed positions, while the others may be repositioned using drag and drop. You can also hide boxes you don&#8217;t use in the Screen Options tab, or minimize boxes by clicking on the title bar of the box.' ) . '</p>' .
			'<p>' . __( 'XFN stands for <a href="http://gmpg.org/xfn/" target="_blank">XHTML Friends Network</a>, which is optional. WordPress allows the generation of XFN attributes to show how you are related to the authors/owners of the site to which you are linking.' ) . '</p>' .
			'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
			'<p>' . __( '<a href="http://codex.wordpress.org/Links_Add_New_SubPanel" target="_blank">Documentation on Creating Links</a>' ) . '</p>' .
			'<p>' . __( '<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>' ) . '</p>'
		);
		
		UCF_Meta_Boxes::add_meta_boxes();
		
	}
	
	static function admin_page_include() {
		global $current_screen, $screen_layout_columns, $ucf_admin_menu, $ucf_current_menu, $title;
		
		// page slug
		$slug = $ucf_current_menu[ 'slug' ];
		
		// include page file
		include( dirname( __FILE__ ) . "/admin/$slug.php" );
	}
	
	static function admin_page_head() {
		// plugin page head
	}
	
	static function admin_page_scripts() {
		// plugin page script
		wp_enqueue_script( 'ucf_admin_script', UCF_PLUGIN_DIR_URL . '/js/ucf-admin.js', array( 'wp-lists', 'postbox' ), UCF_PLUGIN_VERSION, 'all' );
	}
	
	static function admin_page_styles() {
		// plugin page style
		wp_enqueue_style( 'ucf_admin_style', UCF_PLUGIN_DIR_URL . '/css/ucf-admin.css', array(), UCF_PLUGIN_VERSION, 'all' );
	}
	
	static function admin_init() {
		// customize favorite actions
		add_filter( 'favorite_actions', array( 'Ultra_Contact_Form', 'customize_favorite_actions' ) );
		
		// page columns layout
		add_filter( 'screen_layout_columns', array( 'Ultra_Contact_Form', 'screen_layout_columns' ) );
		
		// translate plugin data
		add_filter( 'all_plugins', array( 'Ultra_Contact_Form', 'translate_plugins' ) );
		
		// display notice
		//add_action( 'admin_notices', array( 'Ultra_Contact_Form', 'admin_notice' ) );
	}
	
	static function customize_favorite_actions( $actions ) {
		global $ucf_toplevel_menu;
		$actions[ $ucf_toplevel_menu[ 'action' ] ] = array(
			$ucf_toplevel_menu[ 'menu' ],
			$ucf_toplevel_menu[ 'level' ],
		);
		return $actions;
	}
	
	static function screen_layout_columns( $columns ) {
		global $current_screen;
		$columns[ $current_screen->id ] = 2;
		return $columns;
	}
	
	static function add_plugin_action( $links ) {
		global $ucf_admin_menu;
		$actions = array(
			'ucf_form_manager' => '<a href="admin.php?page=ucf_form_manager">' . __( 'Setting', 'ucf_plugin' ) . '</a>',
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

