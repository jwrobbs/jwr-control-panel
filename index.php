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

use function JWR\JWR_Control_Panel\PHP\options_page_exists;

defined( 'ABSPATH' ) || die();

// Return if Advanced Custom Fields is not installed.
if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
}
if ( ! is_plugin_active( 'advanced-custom-fields-pro/acf.php' ) || \function_exists( 'set_acf_json_save_point' ) ) {
	return;
}

require_once 'php/field-group-fns.php';
require_once 'php/class-jwr-plugin-options.php';

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

/**
 * Update JWR Control Panel.
 *
 * @return void
 */
function update_jwr_control_panel() {
	global $wp_filesystem;

	if ( ! file_exists( __DIR__ . '/acf-json/group_jwr_control_panel.json' ) ) {
		global $wpdb;
		$wpdb->query( "DELETE FROM `wp_options` WHERE option_name LIKE 'jwrcp_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wp_filesystem->copy( __DIR__ . '/data/group_jwr_control_panel.json', __DIR__ . '/acf-json/group_jwr_control_panel.json' );
	}

	do_action( 'update_jwr_control_panel' );
}
add_action( 'wp_loaded', __NAMESPACE__ . '\update_jwr_control_panel' );
