<?php
/**
 * Class for adding options to the JWR Control Panel.
 *
 * @author Josh Robbs <josh@joshrobbs.com>
 * @package JWR_control_panel
 * @version 1.0.0
 * @since   2024-01-29
 */

namespace JWR\JWR_Control_Panel\PHP;

defined( 'ABSPATH' ) || die();

/**
 * Class for adding options to the JWR Control Panel.
 */
class JWR_Plugin_Options {

	/**
	 * Slug.
	 *
	 * @var string
	 */
	private string $slug;

	/**
	 * Group data array.
	 *
	 * @var array
	 */
	private array $group_data;

	/**
	 * Constructor.
	 *
	 * @param string $group_name The name of the field group.
	 * @param string $group_id   The ID of the field group.
	 * @param string $version    The version of the field group.
	 */
	public function __construct(
		private string $group_name,
		private string $group_id,
		private string $version
	) {

		$this->group_id     = $this->string_to_slug( $group_id );
		$this->slug         = $this->string_to_slug( $group_name );
		$this->group_data   = array();
		$this->group_data[] = array(
			'key'               => 'key_' . $this->group_id . '_' . $this->slug,
			'label'             => $group_name,
			'name'              => $this->slug,
			'aria-label'        => '',
			'type'              => 'tab',
			'instructions'      => '',
			'required'          => 0,
			'conditional_logic' => 0,
			'wrapper'           => array(
				'width' => '',
				'class' => '',
				'id'    => '',
			),
			'placement'         => 'left',
			'endpoint'          => 0,
		);
	}

	/**
	 * Add text field.
	 *
	 * @param string $field_label The name of the field.
	 * @param string $field_slug  The slug of the field.
	 * @param int    $width       The width of the field.
	 */
	public function add_text_field( string $field_label, string $field_slug, int $width = 100 ) {
		$field_slug         = $this->string_to_slug( $field_slug );
		$this->group_data[] = array(
			'key'               => 'key_' . $this->group_id . '_' . $field_slug,
			'label'             => $field_label,
			'name'              => $this->group_id . '_' . $field_slug,
			'aria-label'        => '',
			'type'              => 'text',
			'instructions'      => '',
			'required'          => 0,
			'conditional_logic' => 0,
			'wrapper'           => array(
				'width' => $width,
				'class' => '',
				'id'    => '',
			),
			'default_value'     => 'default',
			'maxlength'         => '',
			'placeholder'       => '',
			'prepend'           => '',
			'append'            => '',
		);
		$test_data          = print_r( $this->group_data, true );
			\update_field( 'field_65b7c94a8f5af', $test_data, 'option' );
	}

	/**
	 * Turn string into a slug.
	 *
	 * @param string $bad_string The string to turn into a slug.
	 * @return string
	 */
	private function string_to_slug( string $bad_string ) {
		return strtolower( str_replace( ' ', '_', $bad_string ) );
	}

	/**
	 * Publish the field group.
	 *
	 * @return void
	 */
	public function publish() {
		/*
			Get JSON.
			Remove tab if it exists.
			Add new tab.
			Save JSON.
		*/
		$saved_version = \get_option( 'cp_' . $this->group_id );
		if ( $saved_version === $this->version ) {
			return;
		}

		$file_contents = file_get_contents( __DIR__ . '/../acf-json/group_jwr_control_panel.json' );
		$json_array    = json_decode( $file_contents, true );

		// $test_data = print_r( $this->group_data, true );
		// \update_field( 'field_65b7c94a8f5af', $test_data, 'option' );

		$fields = $this->group_data;
		foreach ( $fields as $field ) {
			$tab_position = $this->find_group_key( $json_array, $field['key'] );

			if ( false !== $tab_position ) {
				unset( $json_array['fields'][ $tab_position ] );
			}

			$json_array['fields'][] = $field;
		}

		// $test_data = print_r( $json_array, true );
		// \update_field( 'field_65b7c94a8f5af', $test_data, 'option' );

		$json_string = json_encode( $json_array );
		file_put_contents( __DIR__ . '/../acf-json/group_jwr_control_panel.json', $json_string );

		\update_option( 'cp_' . $this->group_id, $this->version );
	}

	/**
	 * Find the tab key in the JSON.
	 *
	 * @param array  $json_array The JSON array.
	 * @param string $key       The key to find.
	 *
	 * @return int|false
	 */
	private function find_group_key( array $json_array, $key ) {
		$fields   = $json_array['fields'];
		$position = 0;
		foreach ( $fields as $field ) {
			if ( $key === $field['key'] ) {
				return $position;
			}
			++$position;
		}
		return false;
	}
}
