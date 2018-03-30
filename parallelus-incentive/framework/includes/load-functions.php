<?php

//-----------------------------------------------------------------
// Load data-types
//-----------------------------------------------------------------

if ( !function_exists( 'load_data_types' ) ) :
	function load_data_types() {
		global $data_types_list;

		// $data_types_path = get_theme_root() . "/runway-framework/data-types";
		$data_types_path = FRAMEWORK_DIR .'data-types';
		$data_types_base = $data_types_path . "/data-type.php";

		if(!file_exists($data_types_path) || !file_exists($data_types_base)) {
			wp_die("Error: has no data types.");
		} else {			
			include_once $data_types_base;

			foreach(array_diff(scandir($data_types_path), array('..', '.', 'data-type.php')) as $filename) {													
				include_once "$data_types_path/$filename";								

				$data_type_slug = basename("$data_types_path/$filename", '.php');				

				$data_types_list[$data_type_slug] = array(			
					'filename' => $filename, 
					'classname' => ucfirst(str_replace('-', '_', $data_type_slug)),
				); 

				// Unsupported in old PHP versions
				$data_types_list[$data_type_slug]['classname']::assign_actions_and_filters();

				// This works in some older versions
				// call_user_func(array($data_types_list[$data_type_slug]['classname'], "assign_actions_and_filters"));

			}
		}
	}
endif;


//-----------------------------------------------------------------
// Get options from DB
//-----------------------------------------------------------------

// recursive find option by path
if ( !function_exists( 'r_option' ) ) {
	function r_option( $path, $array ) {

		$step = trim( current( $path ) );

		if ( count( $path ) > 1 ) {
			array_splice( $path, 0, 1 );
			return r_option( $path, $array[$step] );
		} else {
			return $array[$step];
		}

	}
}

if ( !function_exists( 'rf__' ) ) {
	function rf__( $var, $domain = 'framework' ){
		return call_user_func( '__', $var, $domain );    
	}
}

if ( !function_exists( 'rf_e' ) ) {
	function rf_e( $var, $domain = 'framework' ){
		call_user_func( '_e', $var, $domain );    
	}
}

// get framework data
if ( !function_exists( 'get_options_data' ) ) {
	// $key (required) to identify options-set in database
	// $option (optional) is path to option in options-set (coma separated values)
	// $default (optional) if nothing was matched then return this value
	function get_options_data( $key, $option = false, $default = null ) {

		if($option && isset($_REQUEST['customized'])){
			$submited_value = json_decode(stripslashes($_REQUEST['customized']));
			if(isset($submited_value->{$option})){
				$value = $submited_value->{$option};						
				return $value;
			}
		}

		global $shortname;

		if ( empty( $key ) ) {
			return $default;
		}

		// get coma separated path
		if ( $option != false ) {
			$option_path = explode( ',', $option );
			if ( count( $option_path ) == 1 ) {
				$option = current( $option_path );
			} else {
				$option = $option_path;
			}
		}
	
		// create a database value key based on
		// current active theme key and requested key		
		$original_key = $key;
		$key = $shortname . $key;

		// get value from database
		$value = get_option( $key );

		// apply data-type filter
		if(isset($value['field_types'][$option])) {
			$field_type = $value['field_types'][$option];
			$value[$option] = apply_filters( 'get_options_data_type_' . $field_type, $value[$option] );						
		}

		// apply option option_key filter
		$value[$option] = (isset($value[$option])) ? $value[$option] : '';
		$value[$option] = apply_filters( 'options_data_' . $option, $value[$option] );
		// apply option page_key+option_key filter
		$opt = (isset($value[$option])) ? $value[$option] : '';
		$value[$option] = apply_filters( 'options_data_' . $original_key . '_' . $option, $value[$option] );
		
		if ( !empty( $value ) ) {
			// return value by defined option path
			if ( $option != false ) {
				if ( is_array( $option ) ) {
					$value = r_option( $option, $value );
					
					if(!empty($value) && is_string($value) && $value === 'false')
						$value = false;
					if(!empty($value) && is_string($value) && $value === 'true')
						$value = true;
					
					if ( empty( $value ) ) {
						return $default;
					} else {
						return is_array( $value ) && count( $value ) == 1 ? $value[0] : $value;
					}
				} else {
					if(isset( $value[$option] ) && is_string($value[$option]) && $value[$option] === 'false')
						$value[$option] = false;
					if(isset( $value[$option] ) && is_string($value[$option]) && $value[$option] === 'true')
						$value[$option] = true;
					
					if ( isset( $value[$option] ) ) {
						return is_array( $value[$option] ) && count( $value[$option] ) == 1 ? $value[$option][0] : $value[$option];
					} else {
						return $default;
					}
				}
			} else {
				return is_array( $value ) && count( $value ) == 1 ? $value[0] : $value;
			}
		} else {
			return $default;
		}

	}
}

// get and display framework data
if ( !function_exists( 'options_data' ) ) {
	function options_data( $key, $option = false, $default = null ) {

		echo get_options_data( $key, $option, $default );

	}
}


//-----------------------------------------------------------------
// Load libraries
//-----------------------------------------------------------------

if ( !function_exists( 'load_framework_libraries' ) ) :
	function load_framework_libraries() {
		$libs_path = get_template_directory().'/framework/libs/';
		if ( file_exists( $libs_path ) ) {
			$libs = scandir( $libs_path );
			global $libraries;	
			$libraries = array();	
			foreach ( $libs as $key => $lib ) {
				if ( $lib != '.' && $lib != '..' && is_file( $libs_path.$lib ) ) {
					include_once $libs_path.$lib;			
					$name = str_replace( '.php', '', str_replace( '-', '_', $lib ) );			
					if ( class_exists( $name ) ) {				
						$libraries[$name] = new $name();
					}
				}
			}
		}

	}
endif;

//-----------------------------------------------------------------
// Framework functions ???
//-----------------------------------------------------------------

function get_page_values( $page_id ) {
	$result = maybe_unserialize( get_option( 'runway_option_key_'.$page_id, true ) );

	return $result;
}


function get_extensions() {

	// value caching
	global $extensions;

	if ( isset( $extensions ) ) {
		return $extensions;
	}

	// set extensions folders
	$additional_extensions_dir = get_template_directory() . '/extensions/';
	$builtin_extensions_dir = FRAMEWORK_DIR . 'framework/includes/';
	$dynamic_pages_dir = get_template_directory() . '/data/pages';

	$extensions = array();

	// get additional extensions name and path
	$keys = array();
	if ( file_exists( $additional_extensions_dir ) ) {
		$keys = array_diff( (array)scandir( $additional_extensions_dir ), array( '.', '..' ) );
	}

	foreach ( $keys as $key ) {
		$extensions[$key] = $additional_extensions_dir.$key;
	}

	// get built-in extensions name and path
	$keys = array_diff( scandir( $builtin_extensions_dir ), array( '.', '..' ) );

	foreach ( $keys as $key ) {
		$extensions[$key] = $builtin_extensions_dir.$key;
	}

	// get pages names and path
	if ( file_exists( $dynamic_pages_dir ) ) {
		$keys = array_diff( (array)scandir( $dynamic_pages_dir ), array( '.', '..' ) );

		foreach ( $keys as $key ) {
			$key = str_replace( '.json', '', $key );
			$extensions['option_key_'.$key] = $dynamic_pages_dir.'/'.$key;
		}
	}

	return $extensions;
}

function theme_option_filter() {

	global $wp_current_filter, $shortname;
	// check if current option is runway extension option
	$is_runway_option = false;
	$option_key = '';

	foreach ( $wp_current_filter as $filter ) {
		if ( strstr( $filter, 'pre_option_'.$shortname ) ) {
			// prevent loop
			if ( $is_runway_option )
				return false;
			else
				$is_runway_option = true;

			// get option key
			$option_key = str_replace( 'pre_option_', '', $filter );
		}
	}

	// if current options is from runway extension
	if ( $is_runway_option ) {
		// get option from database (the same way as wordpress default)
		global $wpdb;

		$suppress = $wpdb->suppress_errors();
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $option_key ) );
		$wpdb->suppress_errors( $suppress );

		if ( is_object( $row ) ) {
			// if option was founded then return it
			return maybe_unserialize( $row->option_value );
		}
		else {
			// else search this option in /data folder (situation when user move extension or theme manually)
			$extension_name = str_replace( $shortname, '', $option_key );
			$extension_json_settings = THEME_DIR.'/data/'.$extension_name.'.json';
			if ( file_exists( $extension_json_settings ) ) {
				// if have option save it into database
				$value = json_decode( file_get_contents( $extension_json_settings ), true );
				update_option( $option_key, $value );
				return $value;
			} else {
				// else search default options in extension folder (situation when this extension
				// was never being installed or need to reset settings to default)

				$extensions = get_extensions();
				$extension_path = $extensions[str_replace( '_', '-', $extension_name )];

				$default_settings_file = $extension_path . '/default-settings.json';

				if ( file_exists( $default_settings_file ) ) {
					// copy and rename default settings JSON into /data folder
					copy( $default_settings_file, $extension_json_settings );
					$value = json_decode( file_get_contents( $extension_json_settings ), true );
					// save default settings into database
					update_option( $option_key, $value );
				}
			}
		}
	} else
		return false;

}

function theme_option_dual_save_filter( $newvalue ) {

	global $wp_current_filter, $shortname;

	// check if current option is runway extension option
	$is_runway_option = false;
	$option_key = '';

	foreach ( $wp_current_filter as $filter ) {
		if ( strstr( $filter, 'pre_update_option_'.$shortname ) ) {
			// prevent loop
			if ( $is_runway_option )
				return false;
			else
				$is_runway_option = true;
			// get option key
			$option_key = str_replace( 'pre_update_option_', '', $filter );
		}
	}

	// if current options is from runway extension
	if ( $is_runway_option ) {
		global $wpdb;

		$option = $option_key;

		$oldvalue = get_option( $option );

		if ( false === $oldvalue ) {
			add_option( $option, $newvalue );
			return false;
		}

		$result = $wpdb->update( $wpdb->options, array( 'option_value' => maybe_serialize( $newvalue ) ), array( 'option_name' => $option ) );

		$extension_name = str_replace( $shortname, '', $option_key );
		// convert option new value from php serialized to JSON format
		$newvalue = maybe_unserialize( $newvalue );
		$newvalue_json = json_encode( $newvalue );
		// save updated option to file in /data folder
		if ( is_writable( THEME_DIR.'data/' ) ) {
			file_put_contents( THEME_DIR.'data/'.str_replace( '-', '_', $extension_name ).'.json', $newvalue_json );
		}
	}

	return $newvalue;
}




function rw_get_theme_data( $theme_dir = null, $stylesheet = null ) {
	if ( function_exists( 'wp_get_theme' ) ) {
		$tmp = wp_get_theme();
		if ( $theme_dir == null ) {
			$theme_dir = get_stylesheet_directory();
		}
	}

	unset( $tmp );

	if ( function_exists( 'wp_get_theme' ) ) {
		if ( file_exists( $theme_dir.'/style.css' ) && is_dir( $theme_dir ) ) {
			$stylesheet_files = array();
			$template_files = array();

			$theme_files = scandir( $theme_dir );
			foreach ( $theme_files as $file ) {
				if ( is_file( $theme_dir.'/'.$file ) ) {
					if ( preg_match( '/(.+).css/', $file ) ) {
						$stylesheet_files[] = $theme_dir.'/'.$file;
					}
					else {
						$template_files[] = $theme_dir.'/'.$file;
					}
				}
			}
			if ( $stylesheet == null ) {
				$explodeTheme_dir = explode( '/', $theme_dir );
				$stylesheet = array_pop( $explodeTheme_dir );
			}

			$theme = wp_get_theme( $stylesheet );

			$theme_type = '';
			if ( file_exists( $theme_dir.'/framework/setup.php' ) &&
				file_exists( $theme_dir.'/framework/core/admin-object.php' ) &&
				file_exists( $theme_dir.'/framework/core/common-object.php' ) ) {
				$theme_type = 'runway-framework';
			}

			return array(
				'Name' => $theme->get( 'Name' ),
				'URI' => $theme->get( 'ThemeURI' ),
				'Description' => $theme->get( 'Description' ),
				'Author' => $theme->get( 'Author' ),
				'AuthorURI' => $theme->get( 'AuthorURI' ),
				'Version' => $theme->get( 'Version' ),
				'Template' => $theme->get( 'Template' ),
				'Status' => $theme->get( 'Status' ),
				'Tags' => $theme->get( 'Tags' ),
				'TextDomain' => $theme->get( 'TextDomain' ),
				'DomainPath' => $theme->get( 'DomainPath' ),
				'Title' => $theme->get( 'Name' ),
				'AuthorName' => $theme->get( 'Author' ),
				'StylesheetFiles' => $stylesheet_files,
				'TemplateFiles' => $template_files,
				'Folder' => $stylesheet,
			);
		}
	} else {
		if ( !$stylesheet )
			$stylesheet = get_stylesheet_directory() . '/style.css';

		return get_theme_data( $stylesheet );
	}

}

function custom_theme_menu_icon() {
	global $menu, $submenu, $developer_tools; $theme = rw_get_theme_data();

	if ( isset( $menu, $developer_tools, $submenu ) && $theme['Folder'] != 'runway-framework' ) {
		unset( $submenu['current-theme']['current-theme'] ); // Delete duplicate of theme name
		$options = $developer_tools->load_settings( $theme['Folder'] );
		$themeKey = null;
		foreach ( $menu as $key => $values ) {
			if ( $menu[$key][0] == $theme['Title'] ) {
				$themeKey = $key;
			}
		}
		if ( isset($options['Icon']) && $options['Icon'] != '' && $themeKey != null ) {
			$menu[$themeKey][3] = $options['Name'];  // Icon class
			$menu[$themeKey][4] = str_replace( 'menu-icon-generic', '', $menu[$themeKey][4] );
			$menu[$themeKey][4] .= ' '.$options['Icon'];  // Icon class

			if ( $options['Icon'] == 'custom-icon' && file_exists( THEME_DIR.'custom-icon.png' ) ) {
				$menu[$themeKey][6] = get_bloginfo( 'stylesheet_directory' ).'/custom-icon.png';
			} else {
				$menu[$themeKey][6] = '';
			}
		}
	}
}
add_action( 'admin_head', 'custom_theme_menu_icon' );

function activate_default_child_theme() {
	global $pagenow, $developer_tools;
	$theme = rw_get_theme_data();
	if ( is_admin() && $pagenow != 'admin.php' && $pagenow == 'themes.php' && isset( $_GET['activated'] ) && $theme['Folder'] == 'runway-framework' ) {
		wp_redirect( 'admin.php?page=themes&activate-default=activate' );
	}
}
add_action( 'after_setup_theme', 'activate_default_child_theme' );



// Core Admin Menu Object
// -----------------------------------------------------------------

class Admin_menu {
	function __construct() {
		// this is a place holder
	}
}
$admin_menu = new Admin_menu();


// Check for and include "functions-before/after.php"
// -----------------------------------------------------------------

if ( !function_exists('before_functions_file') ) :
	function before_functions_file() {
		locate_template('functions-before.php', true);
	}
	add_action( 'functions_before', 'before_functions_file' );
endif;

if ( !function_exists('after_functions_file') ) :
	function after_functions_file() {
		locate_template('functions-after.php', true);
	}
	add_action( 'functions_after', 'after_functions_file' );
endif;

?>
