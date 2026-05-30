<?php
/**
 * Sanitization utilities for admin settings.
 *
 * @package MatterWP\AdminUI
 */

namespace MatterWP\AdminUI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Static methods for common sanitization patterns.
 *
 * Used by the Schema system and callable directly in SettingsPage controllers.
 */
/**
 * Sanitization helpers for admin settings schemas.
 */
class Sanitization {

	/**
	 * Validate a value against a whitelist of allowed choices.
	 *
	 * @param mixed $value   The value to validate.
	 * @param array $allowed Array of allowed values.
	 * @return mixed The validated value, or the first allowed value as fallback.
	 */
	public static function choice( $value, array $allowed ) {
		return in_array( $value, $allowed, true ) ? $value : reset( $allowed );
	}

	/**
	 * Sanitize and clamp an integer value.
	 *
	 * @param mixed   $value The value to sanitize.
	 * @param int|null $min   Optional minimum value.
	 * @param int|null $max   Optional maximum value.
	 * @return int Sanitized integer within bounds.
	 */
	public static function number( $value, $min = null, $max = null ) {
		$value = (int) $value;
		if ( is_numeric( $min ) ) {
			$value = max( $value, (int) $min );
		}
		if ( is_numeric( $max ) ) {
			$value = min( $value, (int) $max );
		}
		return $value;
	}

	/**
	 * Sanitize and clamp a float value.
	 *
	 * @param mixed     $value The value to sanitize.
	 * @param float|null $min   Optional minimum value.
	 * @param float|null $max   Optional maximum value.
	 * @return float Sanitized float within bounds.
	 */
	public static function float( $value, $min = null, $max = null ) {
		$value = (float) $value;
		if ( is_numeric( $min ) ) {
			$value = max( $value, (float) $min );
		}
		if ( is_numeric( $max ) ) {
			$value = min( $value, (float) $max );
		}
		return $value;
	}

	/**
	 * Normalize a color value to lowercase 6-character hex.
	 *
	 * Accepts: #rgb, #rrggbb, rrggbb, rgb. Returns empty string on invalid input.
	 *
	 * @param string $value Raw color value.
	 * @return string Normalized hex color or empty string.
	 */
	public static function color( $value ) {
		$value = trim( $value );

		if ( preg_match( '/^#([a-f0-9]{6})$/i', $value, $matches ) ) {
			return '#' . strtolower( $matches[1] );
		}

		if ( preg_match( '/^#([a-f0-9]{3})$/i', $value, $matches ) ) {
			$hex = $matches[1];
			return '#' . strtolower( $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2] );
		}

		if ( preg_match( '/^([a-f0-9]{6})$/i', $value, $matches ) ) {
			return '#' . strtolower( $matches[1] );
		}

		if ( preg_match( '/^([a-f0-9]{3})$/i', $value, $matches ) ) {
			$hex = $matches[1];
			return '#' . strtolower( $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2] );
		}

		return '';
	}

	/**
	 * Parse and validate a newline-separated list of IP addresses.
	 *
	 * @param string $raw Raw newline-separated IP list.
	 * @return array Array of validated IP addresses.
	 */
	public static function ips( $raw ) {
		if ( ! is_string( $raw ) ) {
			return array();
		}

		$lines = explode( "\n", $raw );
		$valid = array();

		foreach ( $lines as $line ) {
			$ip = trim( $line );
			if ( '' !== $ip && filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				$valid[] = $ip;
			}
		}

		return $valid;
	}

	/**
	 * Filter an array of role slugs against known roles.
	 *
	 * @param mixed $raw       Input role slugs (array or other).
	 * @param array $all_roles Array of valid role slugs.
	 * @return array Intersection of input and valid roles.
	 */
	public static function roles( $raw, array $all_roles ) {
		if ( ! is_array( $raw ) ) {
			$raw = array();
		}
		return array_intersect( $raw, $all_roles );
	}

	/**
	 * Split a delimited string into a trimmed array.
	 *
	 * @param string $raw       Input string.
	 * @param string $delimiter Delimiter character (default newline).
	 * @return array Array of non-empty trimmed strings.
	 */
	public static function stringList( $raw, $delimiter = "\n" ) {
		if ( ! is_string( $raw ) ) {
			return array();
		}

		$items = explode( $delimiter, $raw );
		$items = array_map( 'trim', $items );
		$items = array_filter( $items, function ( $v ) {
			return '' !== $v;
		} );

		return array_values( $items );
	}

	/**
	 * Validate and sanitize a URL.
	 *
	 * @param string $value Raw URL value.
	 * @return string Sanitized URL or empty string on failure.
	 */
	public static function url( $value ) {
		$value = trim( $value );
		if ( '' === $value ) {
			return '';
		}
		return wp_http_validate_url( $value ) ? esc_url_raw( $value ) : '';
	}
}
