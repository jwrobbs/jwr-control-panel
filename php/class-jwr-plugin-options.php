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

/*
	[] Update Local JSON path to be specific to the plugin.
*/

/*
	Available fields:
		Text - add_text_field
		Number - add_number_field
		True/false

	Fields to add:
		Checkbox
		Select
		Radio
		URL
		Email
		Image
		File
		WYSIWYG editor

	Not a complete list, but it's a starting point.
*/

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

	// Private functions.

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

	/**
	 * Turn string into a slug.
	 *
	 * @param string $bad_string The string to turn into a slug.
	 * @return string
	 */
	private function string_to_slug( string $bad_string ) {
		return strtolower( str_replace( ' ', '_', $bad_string ) );
	}

	// Public functions.

	/**
	 * Publish the field group.
	 *
	 * @return void
	 */
	public function publish() {
		$option_prefix = 'jwrcp_';
		$saved_version = \get_option( $option_prefix . $this->group_id );
		if ( $saved_version === $this->version ) {
			return;
		}

		global $wp_filesystem;
		$file_contents = $wp_filesystem->get_contents( __DIR__ . '/../acf-json/group_jwr_control_panel.json' );
		$json_array    = json_decode( $file_contents, true );

		$fields = $this->group_data;
		foreach ( $fields as $field ) {
			$tab_position = $this->find_group_key( $json_array, $field['key'] );

			if ( false !== $tab_position ) {
				unset( $json_array['fields'][ $tab_position ] );
			}

			$json_array['fields'][] = $field;
		}
		$json_array['modified'] = time();
		$json_string            = wp_json_encode( $json_array );
		$wp_filesystem->put_contents( __DIR__ . '/../acf-json/group_jwr_control_panel.json', $json_string );

		\update_option( $option_prefix . $this->group_id, $this->version );
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
	}

	/**
	 * Add number field.
	 *
	 * @param string $field_label   The name of the field.
	 * @param string $field_slug    The slug of the field.
	 * @param string $min           The minimum value.
	 * @param string $max           The maximum value.
	 * @param string $step          The step value.
	 * @param string $default_value The default value.
	 * @param int    $width         The width of the field.
	 */
	public function add_number_field(
		$field_label,
		$field_slug,
		$min = '',
		$max = '',
		$step = '',
		$default_value = '',
		$width = 100
	) {
		$field_slug         = $this->string_to_slug( $field_slug );
		$this->group_data[] = array(
			'key'               => 'key_' . $this->group_id . '_' . $field_slug,
			'label'             => $field_label,
			'name'              => $field_slug,
			'aria-label'        => '',
			'type'              => 'number',
			'instructions'      => '',
			'required'          => 0,
			'conditional_logic' => 0,
			'wrapper'           => array(
				'width' => $width,
				'class' => '',
				'id'    => '',
			),
			'default_value'     => $default_value,
			'min'               => $min,
			'max'               => $max,
			'placeholder'       => '',
			'step'              => $step,
			'prepend'           => '',
			'append'            => '',
		);
	}

	/**
	 * Add true/false field.
	 *
	 * @param string $field_label   The name of the field.
	 * @param string $field_slug    The slug of the field.
	 * @param string $default_value The default value.
	 * @param string $on_text       The text for the "on" state.
	 * @param string $off_text      The text for the "off" state.
	 * @param int    $width         The width of the field.
	 *
	 * @return void
	 */
	public function add_true_false_field(
		$field_label,
		$field_slug,
		$default_value = 1,
		$on_text = 'True',
		$off_text = 'False',
		$width = 100
	) {
		$field_slug         = $this->string_to_slug( $field_slug );
		$this->group_data[] = array(
			'key'               => 'key_' . $this->group_id . '_' . $field_slug,
			'label'             => $field_label,
			'name'              => $field_slug,
			'aria-label'        => '',
			'type'              => 'true_false',
			'instructions'      => '',
			'required'          => 0,
			'conditional_logic' => 0,
			'wrapper'           => array(
				'width' => $width,
				'class' => '',
				'id'    => '',
			),
			'message'           => 'MESSAGE',
			'default_value'     => $default_value,
			'ui_on_text'        => $on_text,
			'ui_off_text'       => $off_text,
			'ui'                => 1,
		);
	}
}
