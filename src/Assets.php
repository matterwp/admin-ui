<?php
/**
 * Admin UI asset helper.
 *
 * @package MatterWP\AdminUI
 */

namespace MatterWP\AdminUI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helps consuming plugins enqueue compiled admin UI assets.
 */
class Assets {
	/**
	 * Enqueue a compiled admin UI stylesheet.
	 *
	 * @param string $handle Stylesheet handle.
	 * @param string $url Stylesheet URL.
	 * @param string $path Stylesheet filesystem path.
	 * @param array  $dependencies Style dependencies.
	 * @param string $fallback_version Fallback version.
	 * @return void
	 */
	public static function enqueueStyle( string $handle, string $url, string $path, array $dependencies = array(), string $fallback_version = '1.0.0' ): void {
		wp_enqueue_style(
			$handle,
			$url,
			$dependencies,
			file_exists( $path ) ? (string) filemtime( $path ) : $fallback_version,
			'all'
		);
	}
}
