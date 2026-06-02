<?php
/**
 * Attribute rendering helpers.
 *
 * @package MatterWP\AdminUI
 */

namespace MatterWP\AdminUI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utility methods for rendering escaped HTML attributes.
 */
class Attrs {

	/**
	 * Render an escaped HTML attribute string.
	 *
	 * Boolean true renders a valueless attribute. Null and false are skipped.
	 *
	 * @param array<string, mixed> $attributes Attribute map.
	 * @return string
	 */
	public static function render( array $attributes ): string {
		$output = array();

		foreach ( $attributes as $name => $value ) {
			if ( null === $value || false === $value || '' === $name ) {
				continue;
			}

			$name = (string) $name;

			if ( true === $value ) {
				$output[] = esc_attr( $name );
				continue;
			}

			if ( is_array( $value ) ) {
				$value = implode( ' ', array_filter( array_map( 'strval', $value ) ) );
			}

			$output[] = sprintf( '%s="%s"', esc_attr( $name ), esc_attr( (string) $value ) );
		}

		return implode( ' ', $output );
	}

	/**
	 * Render escaped data attributes from a short key map.
	 *
	 * @param array<string, mixed> $attributes Data attribute map without the data- prefix.
	 * @return string
	 */
	public static function data( array $attributes ): string {
		$normalized = array();

		foreach ( $attributes as $name => $value ) {
			$normalized[ 'data-' . ltrim( (string) $name, '-' ) ] = $value;
		}

		return self::render( $normalized );
	}

	/**
	 * Merge base attributes with component class and data attribute arguments.
	 *
	 * @param array<string, mixed> $attributes Base attribute map.
	 * @param string              $classes Root class string.
	 * @param array<string, mixed> $data_attributes Data attribute map.
	 * @return array<string, mixed>
	 */
	public static function merge( array $attributes, string $classes = '', array $data_attributes = array() ): array {
		$merged = $attributes;

		if ( '' !== $classes ) {
			$merged['class'] = trim( $classes . ' ' . ( isset( $merged['class'] ) ? (string) $merged['class'] : '' ) );
		}

		foreach ( $data_attributes as $key => $value ) {
			$key                       = (string) $key;
			$normalized_key            = 0 === strpos( $key, 'data-' ) ? $key : 'data-' . ltrim( $key, '-' );
			$merged[ $normalized_key ] = $value;
		}

		return $merged;
	}

	/**
	 * Get a scoped class value from a classes map with a legacy fallback key.
	 *
	 * @param array<string, mixed> $args Args containing optional classes map.
	 * @param string              $slot Slot name.
	 * @param string              $fallback_key Legacy class arg key.
	 * @return string
	 */
	public static function slotClass( array $args, string $slot, string $fallback_key = '' ): string {
		$classes = isset( $args['classes'] ) && is_array( $args['classes'] ) ? $args['classes'] : array();
		$value   = $classes[ $slot ] ?? '';

		if ( '' === $value && '' !== $fallback_key && isset( $args[ $fallback_key ] ) ) {
			$value = $args[ $fallback_key ];
		}

		return is_scalar( $value ) ? (string) $value : '';
	}
}
