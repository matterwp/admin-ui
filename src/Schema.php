<?php
/**
 * Settings schema definition and sanitization.
 *
 * @package MatterWP\AdminUI
 */

namespace MatterWP\AdminUI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Typed option definitions with built-in validation, defaults, and sanitization.
 *
 * Provides a structured way to define plugin settings with type checking,
 * automatic sanitization per type, and easy retrieval of defaults and stored values.
 */
/**
 * Defines and sanitizes structured admin settings.
 */
class Schema {

	/**
	 * Registered setting definitions.
	 *
	 * @var array
	 */
	private $definitions = array();

	/**
	 * Register a setting definition.
	 *
	 * @param string $key  Setting key.
	 * @param array  $args Setting arguments. Supported keys:
	 *                     - type (string): boolean|integer|float|string|color|url|email|select|multi_select|textarea
	 *                     - default (mixed): Default value.
	 *                     - label (string): Human-readable label.
	 *                     - description (string): Help text.
	 *                     - choices (array): Allowed values for select/multi_select types.
	 *                     - min (int|null): Minimum value for integer/float types.
	 *                     - max (int|null): Maximum value for integer/float types.
	 *                     - sanitize_callback (callable|null): Custom sanitization callback.
	 * @return void
	 */
	public function define( string $key, array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'type'              => 'string',
				'default'           => '',
				'label'             => '',
				'description'       => '',
				'choices'           => array(),
				'min'               => null,
				'max'               => null,
				'sanitize_callback' => null,
			)
		);

		$valid_types = array( 'boolean', 'integer', 'float', 'string', 'text', 'color', 'url', 'email', 'select', 'multi_select', 'textarea' );
		if ( ! in_array( $args['type'], $valid_types, true ) ) {
			wp_doing_it_wrong( __METHOD__, sprintf( 'Unknown schema type "%s".', esc_html( $args['type'] ) ), '1.0.0' );
			return;
		}

		$this->definitions[ $key ] = $args;
	}

	/**
	 * Get all default values.
	 *
	 * @return array Key-value pairs of all defaults.
	 */
	public function getDefaults(): array {
		$defaults = array();
		foreach ( $this->definitions as $key => $args ) {
			$defaults[ $key ] = $args['default'];
		}
		return $defaults;
	}

	/**
	 * Retrieve stored option values merged with defaults.
	 *
	 * @param string $option_name WordPress option name.
	 * @return array Key-value pairs of sanitized values.
	 */
	public function getValues( string $option_name ): array {
		$stored = get_option( $option_name, array() );
		$values = $this->getDefaults();

		if ( ! is_array( $stored ) ) {
			return $values;
		}

		foreach ( $this->definitions as $key => $args ) {
			if ( array_key_exists( $key, $stored ) ) {
				$values[ $key ] = $this->sanitizeValue( $key, $stored[ $key ] );
			}
		}

		return $values;
	}

	/**
	 * Sanitize a single value according to its schema definition.
	 *
	 * @param string $key   Setting key.
	 * @param mixed  $value Raw value to sanitize.
	 * @return mixed Sanitized value, or null if key is undefined.
	 */
	public function sanitizeValue( string $key, $value ) {
		if ( ! isset( $this->definitions[ $key ] ) ) {
			return null;
		}

		$args = $this->definitions[ $key ];

		if ( is_callable( $args['sanitize_callback'] ) ) {
			return call_user_func( $args['sanitize_callback'], $value );
		}

		switch ( $args['type'] ) {
			case 'boolean':
				return (bool) rest_sanitize_boolean( $value );

			case 'integer':
				return Sanitization::number( $value, $args['min'], $args['max'] );

			case 'float':
				return Sanitization::float( $value, $args['min'], $args['max'] );

			case 'color':
				return Sanitization::color( $value );

			case 'url':
				return Sanitization::url( $value );

			case 'email':
				$value = sanitize_email( trim( $value ) );
				return is_email( $value ) ? $value : '';

			case 'select':
				return Sanitization::choice( $value, $args['choices'] );

			case 'multi_select':
				if ( ! is_array( $value ) ) {
					return is_array( $args['default'] ) ? $args['default'] : array();
				}
				return Sanitization::roles( $value, $args['choices'] );

			case 'textarea':
				return sanitize_textarea_field( $value );

			case 'text':
			case 'string':
			default:
				return sanitize_text_field( $value );
		}
	}

	/**
	 * Sanitize an entire input array against the schema.
	 *
	 * Unknown keys are ignored. Missing keys are filled with defaults.
	 *
	 * @param array $input Raw input array.
	 * @return array Sanitized key-value pairs.
	 */
	public function sanitizeAll( array $input ): array {
		$sanitized = array();
		foreach ( $this->definitions as $key => $args ) {
			if ( array_key_exists( $key, $input ) ) {
				$sanitized[ $key ] = $this->sanitizeValue( $key, $input[ $key ] );
			} else {
				$sanitized[ $key ] = $args['default'];
			}
		}
		return $sanitized;
	}
}
