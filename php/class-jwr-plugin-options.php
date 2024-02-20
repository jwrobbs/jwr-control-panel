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
	Available fields:
		Repeater
		Number - add_number_field
		True/false
		Color picker
		Checkbox
		Text
		Select
		URL

	Needs testing:


	Needs updating:

	Fields to add:
		Radio
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
	 * @deprecated 2024-02-13
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
	 * Instance
	 *
	 * @var JWR_Plugin_Options
	 */
	public static $instance;

	/**
	 * Repeater key.
	 *
	 * Set while building a repeater field. Unset by end_repeater_field().
	 *
	 * @var string
	 */
	private string $repeater_key;

	/**
	 * Repeater array.
	 *
	 * Set while building a repeater field. Unset by end_repeater_field().
	 *
	 * @var array
	 */
	private array $repeater;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->group_data = array();

		self::$instance = $this;
	}

	// Private functions.

	/**
	 * Find the tab key in the JSON.
	 *
	 * @param array  $json_array The JSON array.
	 * @param string $key       The key to find.
	 * @deprecated 2024-02-13
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
		global $wp_filesystem;
		if ( null === $wp_filesystem ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}
		global $wp_filesystem;
		$json_array = json_decode( $wp_filesystem->get_contents( \JWR_CONTROL_PANEL_DATA_FILE ), true );

		$json_array['fields'] = $this->group_data;

		$json_array['modified'] = time();
		$json_string            = wp_json_encode( $json_array );
		$wp_filesystem->put_contents( \JWR_CONTROL_PANEL_JSON_FILE, $json_string );
	}

	/**
	 * Update the Local JSON file.
	 *
	 * @return void
	 */
	public static function update_local_json() {
		$options = new JWR_Plugin_Options();

		do_action( 'update_jwr_control_panel' );

		self::add_tab( 'Control panel settings', 'control_panel_settings' );
		$checkbox_field = array( 1 => 'Yes, refresh control panel' );
		self::add_checkbox( 'Refresh control panel', 'refresh_control_panel', $checkbox_field );

		$options->publish();
	}

	/**
	 * Attach field array to field group or repeater.
	 *
	 * @param array $field_array The field array to attach.
	 *
	 * @return void
	 */
	private function attach_field( $field_array ) {
		if ( isset( $this->repeater_key ) ) {
			$field_array['parent_repeater'] = $this->repeater_key;
			$this->repeater['sub_fields'][] = $field_array;
		} else {
			$this->group_data[] = $field_array;
		}
	}

	// Adding fields.

	/**
	 * Add tab.
	 *
	 * Will close any open repeater field.
	 *
	 * @param string $group_name The name of the group.
	 * @param string $group_id   The ID of the group.
	 *
	 * @return void
	 */
	public static function add_tab( string $group_name, string $group_id ) {
		$options = self::get_instance();
		$slug    = $options->string_to_slug( $group_name );

		if ( isset( $options->repeater_key ) ) {
			$options->end_repeater_field();
		}

		$new_tab = array(
			'key'               => 'key_' . $group_id . '_' . $slug,
			'label'             => $group_name,
			'name'              => $slug,
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

		$options->group_data[] = $new_tab;
	}

	/**
	 * Add checkbox.
	 *
	 * @param string $field_label The name of the field.
	 * @param string $field_slug  The slug of the field.
	 * @param array  $choices     The choices for the checkbox. Value => Label.
	 * @param bool   $toggle_all  Whether to give option to toggle all choices. Default: false.
	 * @param int    $width       The width of the field.
	 *
	 * @return void
	 */
	public static function add_checkbox( string $field_label, string $field_slug, array $choices, bool $toggle_all = false, int $width = 100 ) {
		$options     = self::get_instance();
		$field_slug  = $options->string_to_slug( $field_slug );
		$toggle      = $toggle_all ? 1 : 0;
		$field_array = array(
			'key'                       => 'key_' . $field_slug,
			'label'                     => $field_label,
			'name'                      => $field_slug,
			'aria-label'                => '',
			'type'                      => 'checkbox',
			'instructions'              => '',
			'required'                  => 0,
			'conditional_logic'         => 0,
			'wrapper'                   => array(
				'width' => $width,
				'class' => '',
				'id'    => '',
			),
			'choices'                   => $choices,
			'default_value'             => array(),
			'return_format'             => 'value',
			'allow_custom'              => 0,
			'layout'                    => 'vertical',
			'toggle'                    => $toggle,
			'save_custom'               => 0,
			'custom_choice_button_text' => '',
		);

		$options->attach_field( $field_array );
	}

	/**
	 * Add text field.
	 *
	 * @param string $field_label The name of the field.
	 * @param string $field_slug  The slug of the field.
	 * @param int    $width       The width of the field.
	 */
	public static function add_text_field( string $field_label, string $field_slug, int $width = 100 ) {
		$options     = self::get_instance();
		$field_slug  = $options->string_to_slug( $field_slug );
		$field_array = array(
			'key'               => 'key_' . $field_slug,
			'label'             => $field_label,
			'name'              => $field_slug,
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

		$options->attach_field( $field_array );
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
	public static function add_number_field(
		$field_label,
		$field_slug,
		$min = '',
		$max = '',
		$step = '',
		$default_value = '',
		$width = 100
	) {
		$options     = self::get_instance();
		$field_slug  = $options->string_to_slug( $field_slug );
		$field_array = array(
			'key'               => 'key_' . $field_slug,
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

		$options->attach_field( $field_array );
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
	public static function add_true_false_field(
		$field_label,
		$field_slug,
		$default_value = 1,
		$on_text = 'True',
		$off_text = 'False',
		$width = 100
	) {
		$options     = self::get_instance();
		$field_slug  = $options->string_to_slug( $field_slug );
		$field_array = array(
			'key'               => 'key_' . $field_slug,
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
			'message'           => '',
			'default_value'     => $default_value,
			'ui_on_text'        => $on_text,
			'ui_off_text'       => $off_text,
			'ui'                => 1,
		);

		$options->attach_field( $field_array );
	}

	/**
	 * Add color picker field.
	 *
	 * @param string $field_label   The name of the field.
	 * @param string $field_slug    The slug of the field.
	 * @param string $default_value The default value.
	 * @param int    $width         The width of the field.
	 *
	 * @return void
	 */
	public static function add_color_picker_field( string $field_label, string $field_slug, string $default_value = '#FFFFFF', int $width = 25 ) {
		$options     = self::get_instance();
		$field_slug  = $options->string_to_slug( $field_slug );
		$field_array = array(
			'key'               => 'key_' . $field_slug,
			'label'             => $field_label,
			'name'              => $field_slug,
			'aria-label'        => '',
			'type'              => 'color_picker',
			'instructions'      => '',
			'required'          => 0,
			'conditional_logic' => 0,
			'wrapper'           => array(
				'width' => $width,
				'class' => '',
				'id'    => '',
			),
			'default_value'     => $default_value,
			'enable_opacity'    => 1,
			'return_format'     => 'string',
		);

		$options->attach_field( $field_array );
	}

	/**
	 * Add URL field.
	 *
	 * @param string $field_label   The name of the field.
	 * @param string $field_slug    The slug of the field.
	 * @param int    $width         The width of the field.
	 */
	public static function add_url_field( string $field_label, string $field_slug, int $width = 100 ) {
		$options     = self::get_instance();
		$field_slug  = $options->string_to_slug( $field_slug );
		$field_array = array(
			'key'               => 'key_' . $field_slug,
			'label'             => $field_label,
			'name'              => $field_slug,
			'aria-label'        => '',
			'type'              => 'url',
			'instructions'      => '',
			'required'          => 0,
			'conditional_logic' => 0,
			'wrapper'           => array(
				'width' => $width,
				'class' => '',
				'id'    => '',
			),
			'default_value'     => '',
			'placeholder'       => '',
		);

		$options->attach_field( $field_array );
	}

	/**
	 * Add select field.
	 *
	 * @param string $field_label The name of the field.
	 * @param string $field_slug  The slug of the field.
	 * @param array  $choices     The choices for the select. Value => Label.
	 * @param bool   $allow_null  Whether to allow a null value. Default: false.
	 * @param int    $width       The width of the field.
	 *
	 * @return void
	 */
	public static function add_select_field(
		$field_label,
		$field_slug,
		$choices,
		$allow_null = false,
		$width = 100
	) {
		$options     = self::get_instance();
		$field_slug  = $options->string_to_slug( $field_slug );
		$can_null    = $allow_null ? 1 : 0;
		$field_array = array(
			'key'               => 'key_' . $field_slug,
			'label'             => $field_label,
			'name'              => $field_slug,
			'aria-label'        => '',
			'type'              => 'select',
			'instructions'      => '',
			'required'          => 0,
			'conditional_logic' => 0,
			'wrapper'           => array(
				'width' => $width,
				'class' => '',
				'id'    => '',
			),
			'choices'           => $choices,
			'default_value'     => false,
			'return_format'     => 'value',
			'multiple'          => 0,
			'allow_null'        => $can_null,
			'ui'                => 0,
			'ajax'              => 0,
			'placeholder'       => '',
		);

		$options->attach_field( $field_array );
	}

	/**
	 * Start repeater field.
	 * After adding the fields, call end_repeater_field.
	 *
	 * @param string $field_label  The name of the field.
	 * @param string $field_slug   The slug of the field.
	 * @param string $layout       The layout of the field. *Row*, table, or block.
	 * @param string $button_label The label for the "Add" button.
	 * @param int    $width        The width of the field.
	 *
	 * @return void
	 */
	public static function start_repeater_field(
		$field_label,
		$field_slug,
		$layout = 'row',
		$button_label = 'Add Row',
		$width = 100
	) {
		$options               = self::get_instance();
		$field_slug            = $options->string_to_slug( $field_slug );
		$options->repeater_key = 'key_' . $field_slug;
		$options->repeater     = array(
			'key'               => 'key_' . $field_slug,
			'label'             => $field_label,
			'name'              => $field_slug,
			'aria-label'        => '',
			'type'              => 'repeater',
			'instructions'      => '',
			'required'          => 0,
			'conditional_logic' => 0,
			'wrapper'           => array(
				'width' => $width,
				'class' => '',
				'id'    => '',
			),
			'layout'            => $layout,
			'pagination'        => 0,
			'min'               => 0,
			'max'               => 0,
			'collapsed'         => '',
			'button_label'      => $button_label,
			'rows_per_page'     => 20,
			'sub_fields'        => array(),
		);
	}

	/**
	 * End repeater field.
	 * Used to add a completed repeater field to the group data.
	 *
	 * @return void
	 */
	public static function end_repeater_field() {
		$options               = self::get_instance();
		$options->group_data[] = $options->repeater;
		unset( $options->repeater_key );
		unset( $options->repeater );
	}

	// Public static functions.

	/**
	 * Get singleton.
	 *
	 * @return JWR_Plugin_Options
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self( '', '', '' );
		}

		return self::$instance;
	}
}
