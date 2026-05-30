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
}
