<?php
/**
 * Shared control panel for JWR plugins.
 * Included as a submodule in each of my plugins.
 *
 * Assumes that it will be included in the top level of the plugin.
 *
 * @author Josh Robbs <josh@joshrobbs.com>
 * @package JWR_control_panel
 * @version 1.0.0
 * @since   2024-01-29
 */

namespace JWR\ControlPanel;

use function JWR\JWR_Control_Panel\PHP\create_jwr_control_panel;
use function JWR\JWR_Control_Panel\PHP\field_group_exists;
use function JWR\JWR_Control_Panel\PHP\options_page_exists;

defined( 'ABSPATH' ) || die();

/*
	[x] set local json
	[] check for page function and set
	[] check for fields group and set
	[] read json, create basic function, and set framework
*/

// Return if Advanced Custom Fields is not installed.
if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
}
if ( ! is_plugin_active( 'advanced-custom-fields-pro/acf.php' ) ) {
	return;
}

require_once 'php/field-group-fns.php';

/**
 * Set the path for the ACF JSON files.
 *
 * @param string $path The path to the ACF JSON files.
 * @return string
 */
function set_acf_json_save_point( $path ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	return __DIR__ . '/acf-json';
}
add_filter( 'acf/settings/save_json', __NAMESPACE__ . '\set_acf_json_save_point' );

/**
 * Set load path for ACF JSON files.
 *
 * @param array $paths The paths to the ACF JSON files.
 * @return array
 */
function set_acf_json_load_point( $paths ) {
	unset( $paths[0] );
	$paths[] = __DIR__ . '/acf-json';
	return $paths;
}
add_filter( 'acf/settings/load_json', __NAMESPACE__ . '\set_acf_json_load_point' );


/**
 * Create options page if needed.
 *
 * @return void
 */
function create_options_page() {
	if ( ! options_page_exists( 'JWR Control Panel' ) ) {
		\acf_add_options_page(
			array(
				'page_title' => 'JWR Control Panel',
				'menu_title' => 'JWR Control Panel',
				'menu_slug'  => 'jwr-control-panel',
				'capability' => 'edit_posts',
				'redirect'   => false,
			)
		);
	}
}
add_action( 'acf/init', __NAMESPACE__ . '\create_options_page', 8 );

// If json file missing, add it.
// ?? Should I hook this to something?


/**
 * Update JWR Control Panel.
 *
 * @return void
 */
function update_jwr_control_panel() {
	global $wp_filesystem;

	if ( ! file_exists( __DIR__ . '/acf-json/group_jwr_control_panel.json' ) ) {
		$wp_filesystem->copy( __DIR__ . '/data/group_jwr_control_panel.json', __DIR__ . '/acf-json/group_jwr_control_panel.json' );
	}

	$json  = $wp_filesystem->get_contents( __DIR__ . '/acf-json/group_jwr_control_panel.json' );
	$array = json_decode( $json, true );

	$array = apply_filters( 'jwr_control_panel_update', $array );
	$dump  = \var_export( $array, true );
	\update_field( 'field_65b7c94a8f5af', 'In hook: ' . $dump, 'option' );

	$json = json_encode( $array );
	file_put_contents( __DIR__ . '/acf-json/group_jwr_control_panel.json', $json );
}
add_action( 'wp_loaded', __NAMESPACE__ . '\update_jwr_control_panel' );

/**
 * Hook pass through.
 * This does NOTHING - only ensures that the array is passed when there are no other hooks.
 * ?? Is there a better way to do this?
 *
 * @param array $data The array to be updated.
 * @return array
 */
function hook_test( array $data ) {
	// $dump = \var_export( $data, true );
	\ob_start();
	\var_dump( $data );
	$dump = \ob_get_clean();
	\update_field( 'field_65b7c94a8f5af', 'In hook: ' . $dump, 'option' );
	return $data;
}
add_action( 'jwr_control_panel_update', __NAMESPACE__ . '\hook_test', 1, 1 );
