<?php
/**
 * Plugin Name: Josh's control panel
 *
 * Shared control panel for JWR plugins.
 *
 * @author Josh Robbs <josh@joshrobbs.com>
 * @package JWR_control_panel
 * @version 2.0.0
 * @since   2024-02-11
 */

namespace JWR\ControlPanel;

use function JWR\JWR_Control_Panel\PHP\options_page_exists;

defined( 'ABSPATH' ) || die();

/**
 * Plugin constants.
 */
$upload_dir = wp_upload_dir();
$json_dir   = $upload_dir['basedir'] . '/control-panel-json';
$json_file  = $json_dir . '/group_jwr_control_panel.json';
$data_dir   = __DIR__ . '/data';
$data_file  = $data_dir . '/group_jwr_control_panel.json';
define( 'JWR_CONTROL_PANEL_DATA_FILE', $data_file );
define( 'JWR_CONTROL_PANEL_JSON_DIR', $json_dir );
define( 'JWR_CONTROL_PANEL_JSON_FILE', $json_file );

/**
 * Initialize the plugin.
 *
 * @return void
 */
function init() {
	if ( ! is_plugin_active( 'advanced-custom-fields-pro/acf.php' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( 'This plugin requires Advanced Custom Fields Pro to be installed and active.' );
	}

	global $wp_filesystem;
	if ( null === $wp_filesystem ) {
		require_once ABSPATH . '/wp-admin/includes/file.php';
		WP_Filesystem();
	}

	$json_dir  = str_replace( \ABSPATH, $wp_filesystem->abspath(), \JWR_CONTROL_PANEL_JSON_DIR );
	$data_file = str_replace( \ABSPATH, $wp_filesystem->abspath(), \JWR_CONTROL_PANEL_DATA_FILE );
	$json_file = str_replace( \ABSPATH, $wp_filesystem->abspath(), \JWR_CONTROL_PANEL_JSON_FILE );

	if ( ! $wp_filesystem->is_dir( $json_dir ) ) {
		$wp_filesystem->mkdir( $json_dir );
		$content = "<?php\n/** Silence is golden. */\n";
		$wp_filesystem->put_contents( $json_dir . '/index.php', $content );
	}

	if ( ! $wp_filesystem->exists( $json_file ) ) {
		$contents = $wp_filesystem->get_contents( $data_file );
		$wp_filesystem->put_contents( $json_file, $contents );
	}
}
\register_activation_hook( __FILE__, __NAMESPACE__ . '\init' );

require_once 'php/field-group-fns.php';
require_once 'php/class-jwr-plugin-options.php';

if ( ! \function_exists( 'set_acf_json_save_point' ) ) {
	/**
	 * Set the path for the ACF JSON files.
	 *
	 * @param string $path The path to the ACF JSON files.
	 * @return string
	 */
	function set_acf_json_save_point( $path ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		return \JWR_CONTROL_PANEL_JSON_FILE;
	}
	add_filter( 'acf/settings/save_json/key=group_jwr_control_panel', __NAMESPACE__ . '\set_acf_json_save_point' );
}


if ( ! function_exists( 'set_acf_json_load_point' ) ) {
	/**
	 * Set load path for ACF JSON files.
	 *
	 * @param array $paths The paths to the ACF JSON files.
	 * @return array
	 */
	function set_acf_json_load_point( $paths ) {
		unset( $paths[0] );
		$paths[] = \JWR_CONTROL_PANEL_JSON_DIR;
		return $paths;
	}
	add_filter( 'acf/settings/load_json', __NAMESPACE__ . '\set_acf_json_load_point' );
}


if ( ! function_exists( 'options_page_exists' ) ) {
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
}

if ( ! function_exists( 'update_jwr_control_panel' ) ) {

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
}
