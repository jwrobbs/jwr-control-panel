<?php
/**
 * Refresh the control panel.
 *
 * @since 2024-02-12
 * @package JWR_Control_Panel
 */

namespace JWR\ControlPanel\PHP;

use JWR\JWR_Control_Panel\PHP\JWR_Plugin_Options;

defined( 'ABSPATH' ) || die();

/**
 * Refresh the control panel.
 * Checked when control panel is saved.
 *
 * @param int $post_id The post ID.
 *
 * @return void
 */
function refresh_control_panel( $post_id ) {
	if ( 'options' !== $post_id ) {
		return;
	}

	$refresh = get_field( 'checkbox_field', 'option' );
	if ( $refresh[0] ) {
		JWR_Plugin_Options::update_local_json();
		update_field( 'refresh_control_panel', null, 'option' );
	}
}

add_action( 'acf/save_post', __NAMESPACE__ . '\refresh_control_panel', 20, 1 );
