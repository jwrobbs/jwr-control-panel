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

defined( 'ABSPATH' ) || die();

// Return if Advanced Custom Fields is not installed.
if ( ! function_exists( 'get_fields' ) ) {
	return;
}

if ( ! \function_exists( __NAMESPACE__ . '\init' ) ) {
	/**
	 * Initialize the plugin.
	 *
	 * @return void
	 */
	function init() {
		/*
			[x] set local json
			[] check for fields group and set
			[] check for page function and set
			[] read json, create basic function, and set framework
		*/
	}
}

/**
 * Set the path for the ACF JSON files.
 *
 * @param string $path The path to the ACF JSON files.
 * @return string
 */
function set_acf_json_save_point( $path ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	return __DIR__ . '/acf-json';
}
add_filter( 'acf/settings/save_json', 'set_acf_json_save_point' );

/**
 * Check if ACF field group exists.
 *
 * @param string $field_group_name The name of the field group.
 */
function field_group_exists( $field_group_name ) {
	$groups = \acf_get_field_groups();
	foreach ( $groups as $group ) {
		if ( $group['title'] === $field_group_name ) {
			return true;
		}
		return false;
	}
}

/**
 * Add control panel field group if it doesn't exist.
 *
 * @return void
 */
function add_control_panel_field_group() {
	if ( ! field_group_exists( 'jwr-control-panel' ) ) {
		acf_add_local_field_group(
			array(
				'key'                   => 'group_5e2f1b0b0e0e4',
				'title'                 => 'test',
				'fields'                => array(),
				'location'              => array(
					array(
						array(
							'param'    => 'options_page',
							'operator' => '==',
							'value'    => 'options-general.php?page=jwr-control-panel',
						),
					),
				),
				'menu_order'            => 0,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'active'                => true,
				'description'           => '',
			)
		);
	}
}
