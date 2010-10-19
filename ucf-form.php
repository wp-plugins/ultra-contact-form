<?php

class UCF_Form
{
	static function sanitize_form( $form, $context = 'display' ) {
		$fields = array('form_id', 'form_name', 'shortcode', 'form_body', 'mail_to', 'mail_cc' );
		
		if ( is_object($form) ) {
			$do_object = true;
			$form_id = $form->form_id;
		} else {
			$do_object = false;
			$form_id = $form['form_id'];
		}
		
		foreach ( $fields as $field ) {
			if ( $do_object ) {
				if ( isset( $form->$field ) )
					$form->$field = self::sanitize_form_field($field, $form->$field, $form_id, $context);
			} else {
				if ( isset( $form[ $field ] ) )
					$form[$field] = self::sanitize_form_field($field, $form[$field], $form_id, $context);
			}
		}
		
		return $form;
	}
	
	static function sanitize_form_field( $field, $value, $bookmark_id, $context ) {
		switch ( $field ) {
			case 'form_id' : // ints
				$value = (int) $value;
				break;
			case 'form_category' : // array( ints )
				$value = array_map('absint', (array) $value);
				// We return here so that the categories aren't filtered.
				// The 'link_category' filter is for the name of a link category, not an array of a link's link categories
				return $value;
				break;
			case 'link_visible' : // bool stored as Y|N
				$value = preg_replace('/[^YNyn]/', '', $value);
				break;
			case 'link_target' : // "enum"
				$targets = array('_top', '_blank');
				if ( ! in_array($value, $targets) )
					$value = '';
				break;
		}
		
		if ( 'raw' == $context )
			return $value;
		
		if ( 'edit' == $context ) {
			$format_to_edit = array('link_notes');
			$value = apply_filters("edit_$field", $value, $bookmark_id);
			
			if ( in_array($field, $format_to_edit) ) {
				$value = format_to_edit($value);
			} else {
				$value = esc_attr($value);
			}
		} else if ( 'db' == $context ) {
			$value = apply_filters("pre_$field", $value);
		} else {
			// Use display filters by default.
			$value = apply_filters($field, $value, $bookmark_id, $context);
			
			if ( 'attribute' == $context )
				$value = esc_attr($value);
			else if ( 'js' == $context )
				$value = esc_js($value);
		}
		
		return $value;
	}
	
	static function get_add_form_link() {
		if ( !current_user_can( 'ucf_manage_forms' ) )
			return;
		
		$location = 'admin.php?page=ucf_form-add';
		return apply_filters( 'ucf_get_add_form_link', $location );
	}
	
	static function get_edit_form_link( $form = 0 ) {
		if ( !current_user_can( 'ucf_manage_forms' ) )
			return;
		
		$form = self::get_form( $form );
		$location = 'admin.php?page=ucf_form_manager&form_id=' . $form->form_id . '&action=edit';
		return apply_filters( 'ucf_get_edit_form_link', $location, $form->form_id );
	}
	
	static function get_delete_form_link( $form = 0 ) {
		if ( !current_user_can( 'ucf_manage_forms' ) )
			return;
		
		$form = self::get_form( $form );
		$location = 'admin.php?page=ucf_form_manager&form_id=' . $form->form_id . '&action=delete';
		return apply_filters( 'ucf_get_delete_form_link', $location, $form->form_id );
	}
	
	static function get_form( $form, $output = OBJECT, $filter = 'raw' ) {
		global $wpdb;
		
		$table = $wpdb->prefix . "ucf_form";
		
		if ( empty($form) ) {
			if ( isset($GLOBALS['ucf-form']) )
				$_form = & $GLOBALS['ucf-form'];
			else
				$_form = null;
		} elseif ( is_object($form) ) {
			wp_cache_add($form->form_id, $form, 'ucf_form');
			$_form = $form;
		} else {
			if ( isset($GLOBALS['ucf-form']) && ($GLOBALS['ucf-form']->form_id == $form) ) {
				$_form = & $GLOBALS['ucf-form'];
			} elseif ( ! $_form = wp_cache_get($form, 'ucf_form') ) {
				$_form = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE form_id = %d LIMIT 1", $form));
				wp_cache_add($_form->form_id, $_form, 'ucf_form');
			}
		}
		
		$_form = self::sanitize_form($_form, $filter);
		
		if ( $output == OBJECT ) {
			return $_form;
		} elseif ( $output == ARRAY_A ) {
			return get_object_vars($_form);
		} elseif ( $output == ARRAY_N ) {
			return array_values(get_object_vars($_form));
		} else {
			return $_form;
		}
	}
	
	static function get_forms( $args = '' ) {
		global $wpdb;
		
		$table = $wpdb->prefix . "ucf_form";
		
		$defaults = array(
			'orderby' => 'form_name', 'order' => 'ASC',
			'limit' => -1, 'hide_invisible' => 1,
			'show_updated' => 0, 'include' => '',
			'exclude' => '', 'search' => ''
		);
		
		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );
		
		$cache = array();
		$key = md5( serialize( $r ) );
		if ( $cache = wp_cache_get( 'ucf_get_forms', 'ucf_form' ) ) {
			if ( is_array($cache) && isset( $cache[ $key ] ) )
				return apply_filters('ucf_get_forms', $cache[ $key ], $r );
		}
		
		if ( !is_array($cache) )
			$cache = array();
		
		// todo: support args
		
		$orderby = strtolower($orderby);
		$length = '';
		switch ($orderby) {
			case 'length':
				$length = ", CHAR_LENGTH(form_name) AS length";
				break;
			case 'rand':
				$orderby = 'rand()';
				break;
			default:
				$orderparams = array();
				foreach ( explode(',', $orderby) as $ordparam )
					$orderparams[] = '' . trim($ordparam);
				$orderby = implode(',', $orderparams);
		}
		
		if ( 'form_id' == $orderby )
			$orderby = "$table.form_id";
		
		$visible = '';
		//if ( $hide_invisible )
		//	$visible = "AND link_visible = 'Y'";
		
		$query = "SELECT * $length $recently_updated_test $get_updated FROM $table $join WHERE 1=1 $visible $category_query";
		$query .= " $exclusions $inclusions $search";
		$query .= " ORDER BY $orderby $order";
		if ($limit != -1)
			$query .= " LIMIT $limit";
			
		
		$results = $wpdb->get_results( $query );
		
		$cache[ $key ] = $results;
		wp_cache_set( 'ucf_get_forms', $cache, 'ucf_form' );
		
		return apply_filters('ucf_get_forms', $results, $r);
	}
	
	static function add_form() {
		return self::edit_form();
	}
	
	static function edit_form( $form_id = '' ) {
		
		//if (!current_user_can( 'ucf_manage_forms' ))
		//	wp_die( __( 'Cheatin&#8217; uh?' ));
		
		$_POST['form_name'] = esc_html( $_POST['form_name'] );
		$_POST['shortcode'] = esc_html( $_POST['shortcode'] );
		
		if ( !isset( $_POST[ 'mail_to' ] ) )
			$_POST[ 'mail_to' ] = 'test';
		if ( !isset( $_POST[ 'mail_cc' ] ) )
			$_POST[ 'mail_cc' ] = 'test';
		if ( !isset( $_POST[ 'usedb_type' ] ) )
			$_POST[ 'usedb_type' ] = 'test';
		
		if ( !empty( $form_id ) ) {
			$_POST['form_id'] = $form_id;
			return self::insert_form( $_POST );
		} else {
			return self::insert_form( $_POST );
		}
	}
	
	static function insert_form( $formdata, $wp_error = false ) {
		global $wpdb;
		
		$defaults = array( 'form_id' => 0 );
		
		$formdata = wp_parse_args( $formdata, $defaults );
		$formdata = self::sanitize_form( $formdata, 'db' );
		
		extract( stripslashes_deep( $formdata ), EXTR_SKIP );
		
		$update = false;
		
		if ( !empty( $form_id ) )
			$update = true;
		
		if ( trim( $shortcode ) == '' )
			return 0;
		
/*
		if ( empty( $link_rating ) )
			$link_rating = 0;
		
		if ( empty( $link_image ) )
			$link_image = '';
		
		if ( empty( $link_target ) )
			$link_target = '';
		
		if ( empty( $link_visible ) )
			$link_visible = 'Y';
		
		if ( empty( $link_owner ) )
			$link_owner = get_current_user_id();
		
		if ( empty( $link_notes ) )
			$link_notes = '';
		
		if ( empty( $link_description ) )
			$link_description = '';
		
		if ( empty( $link_rss ) )
			$link_rss = '';
		
		if ( empty( $link_rel ) )
			$link_rel = '';
 */
		if ( $update ) {
			if ( false === $wpdb->update( $wpdb->prefix . 'ucf_form' , compact( 'shortcode', 'form_name', 'form_body', 'mail_to', 'mail_cc', 'usedb_type' ), compact( 'form_id' ) ) ) {
				if ( $wp_error )
					return new WP_Error( 'db_update_error', __( 'Could not update link in the database' ), $wpdb->last_error );
				else
					return 0;
			}
		} else {
			if ( false === $wpdb->insert( $wpdb->prefix . 'ucf_form', compact( 'shortcode', 'form_name', 'form_body', 'mail_to', 'mail_cc', 'usedb_type' ) ) ) {
				if ( $wp_error )
					return new WP_Error( 'db_insert_error', __( 'Could not insert link into the database' ), $wpdb->last_error );
				else
					return 0;
			}
			$form_id = (int) $wpdb->insert_id;
		}
		
		if ( $update )
			do_action( 'ucf_edit_form', $form_id );
		else
			do_action( 'ucf_add_form', $form_id );
		
		self::clean_form_cache( $form_id );
		
		return $form_id;
	}
	
	static function clean_form_cache( $form_id ) {
		wp_cache_delete( $form_id, 'ucf_form' );
		wp_cache_delete( 'ucf_get_forms', 'ucf_form' );
	}
}
