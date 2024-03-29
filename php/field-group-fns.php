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

if ( ! function_exists( 'options_page_exists' ) ) {
	/**
	 * Check if options page exists.
	 *
	 * @param string $options_page_title The title of the options page.
	 * @return bool
	 */
	function options_page_exists( $options_page_title ) {
		$pages = \acf_get_options_pages();

		// [] Is this guard adequate?
		if ( false === $pages ) {
			return false;
		}

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
}
