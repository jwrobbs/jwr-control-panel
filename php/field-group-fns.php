<?php
/**
 * Functions for working with control panel field group.
 *
 * @author Josh Robbs <josh@joshrobbs.com>
 * @package JWR_control_panel
 * @version 1.0.0
 * @since   2024-01-29
 */

namespace JWR\JWR_Control_Panel\PHP;

defined( 'ABSPATH' ) || die();

/**
 * Check if options page exists.
 *
 * @param string $options_page_title The title of the options page.
 * @return bool
 */
function options_page_exists( $options_page_title ) {
	$pages  = \acf_get_options_pages();
	$titles = array();
	foreach ( $pages as $page ) {
		$titles[] = $page['page_title'];
	}
	if ( \in_array( $options_page_title, $titles, true ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Check if ACF field group exists.
 *
 * @param string $field_group_name The name of the field group.
 */
function field_group_exists( $field_group_name ) {
	$groups = \acf_get_field_groups();
	$titles = array();
	foreach ( $groups as $group ) {
		$titles[] = $group['title'];
	}
	if ( \in_array( $field_group_name, $titles, true ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Create jwr-control-panel field group.
 *
 * @return void
 */
function create_jwr_control_panel() {
	acf_add_local_field_group(
		array(
			'key'                   => 'group_jwr_control_panel',
			'title'                 => 'jwr-control-panel',
			'fields'                => array(
				array(
					'key'               => 'field_65b7d011b1e78',
					'label'             => '',
					'name'              => '',
					'aria-label'        => '',
					'type'              => 'message',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'message'           => 'Each panel/group is a set of options for a separate plugin.',
					'new_lines'         => 'wpautop',
					'esc_html'          => 0,
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'jwr-control-panel',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
			'show_in_rest'          => 0,
		)
	);

	$field_group           = \acf_get_field_group( 'group_jwr_control_panel' );
	$field_group['fields'] = acf_get_fields( 'group_jwr_control_panel' );

	acf_write_json_field_group( $field_group );
}
