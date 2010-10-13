<?php

class UCF_Form
{
	static function sanitize_form( $form, $context = 'display' ) {
		$fields = array('link_id', 'link_url', 'link_name', 'link_image', 'link_target', 'link_category',
			'link_description', 'link_visible', 'link_owner', 'link_rating', 'link_updated',
			'link_rel', 'link_notes', 'link_rss', );
		
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
		case 'link_rating' :
			$value = (int) $value;
			break;
		case 'link_category' : // array( ints )
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
	
	static function get_edit_form_link( $form = 0 ) {
		global $ucf_plugin_admin_menu;
		
		$form = self::get_form( $form );
	
		if ( !current_user_can('manage_links') )
			return;
		
		$location = $ucf_plugin_admin_menu[ 'form-manager' ][ 'action' ] . '&form_id=' . $form->form_id;
		return apply_filters( 'ucf_get_edit_form_link', $location, $form->form_id );
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
			wp_cache_add($form->form_id, $form, 'ultra-contact-form');
			$_form = $form;
		} else {
			if ( isset($GLOBALS['ucf-form']) && ($GLOBALS['ucf-form']->form_id == $form) ) {
				$_form = & $GLOBALS['ucf-form'];
			} elseif ( ! $_form = wp_cache_get($form, 'ultra-contact-form') ) {
				$_form = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE form_id = %d LIMIT 1", $form));
				wp_cache_add($_form->form_id, $_form, 'ultra-contact-form');
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
			'orderby' => 'name', 'order' => 'ASC',
			'limit' => -1, 'hide_invisible' => 1,
			'show_updated' => 0, 'include' => '',
			'exclude' => '', 'search' => ''
		);
		
		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );
		
		$cache = array();
		$key = md5( serialize( $r ) );
		if ( $cache = wp_cache_get( 'ucf_get_forms', 'ultra-contact-form' ) ) {
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
				$length = ", CHAR_LENGTH(name) AS length";
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
		wp_cache_set( 'ucf_get_forms', $cache, 'ultra-contact-form' );
		
		return apply_filters('ucf_get_forms', $results, $r);
	}
	
}
