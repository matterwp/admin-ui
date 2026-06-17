<?php
/**
 * Admin UI asset helper.
 *
 * @package MTWP\ADMIN\V130
 */

namespace MTWP\ADMIN\V130;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helps consuming plugins enqueue compiled admin UI assets.
 *
 * The package ships pre-built minified assets in `dist/` for drop-in use,
 * plus source files in `resources/` for plugins that bundle admin-ui into
 * their own build pipeline.
 */
class Assets {
	/**
	 * Package root path.
	 *
	 * @var string
	 */
	private static $package_dir = '';

	/**
	 * Get the package root directory.
	 *
	 * @return string
	 */
	private static function getPackageDir(): string {
		if ( '' === self::$package_dir ) {
			self::$package_dir = dirname( __DIR__ );
		}

		return self::$package_dir;
	}

	/**
	 * Enqueue a compiled stylesheet.
	 *
	 * @param string $handle Stylesheet handle.
	 * @param string $url Stylesheet URL.
	 * @param string $path Stylesheet filesystem path.
	 * @param array  $dependencies Style dependencies.
	 * @param string $fallback_version Fallback version.
	 * @return void
	 */
	public static function enqueueStyle( string $handle, string $url, string $path, array $dependencies = array(), string $fallback_version = '1.3.0' ): void {
		wp_enqueue_style(
			$handle,
			$url,
			$dependencies,
			file_exists( $path ) ? (string) filemtime( $path ) : $fallback_version,
			'all'
		);
	}

	/**
	 * Enqueue a compiled script.
	 *
	 * @param string $handle Script handle.
	 * @param string $url Script URL.
	 * @param string $path Script filesystem path.
	 * @param array  $dependencies Script dependencies.
	 * @param bool   $in_footer Whether to enqueue in footer.
	 * @param string $fallback_version Fallback version.
	 * @return void
	 */
	public static function enqueueScript( string $handle, string $url, string $path, array $dependencies = array(), bool $in_footer = true, string $fallback_version = '1.3.0' ): void {
		wp_enqueue_script(
			$handle,
			$url,
			$dependencies,
			file_exists( $path ) ? (string) filemtime( $path ) : $fallback_version,
			$in_footer
		);
	}

	/**
	 * Enqueue the package's pre-built minified stylesheet.
	 *
	 * Uses plugins_url() relative to the package directory, so it works
	 * regardless of where Composer installed the package.
	 *
	 * @param string $handle Stylesheet handle.
	 * @param array  $dependencies Style dependencies.
	 * @param string $version Optional version string. Defaults to filemtime.
	 * @return void
	 */
	public static function enqueueBuiltStyle( string $handle, array $dependencies = array(), string $version = '' ): void {
		$dir  = self::getPackageDir();
		$file = 'dist/css/admin-ui.css';

		if ( '' === $version ) {
			$path    = $dir . '/' . $file;
			$version = file_exists( $path ) ? (string) filemtime( $path ) : '1.3.0';
		}

		wp_enqueue_style(
			$handle,
			plugins_url( $file, $dir . '/composer.json' ),
			$dependencies,
			$version,
			'all'
		);
	}

	/**
	 * Enqueue the package's pre-built minified script.
	 *
	 * @param string $handle Script handle.
	 * @param array  $dependencies Script dependencies.
	 * @param string $version Optional version string. Defaults to filemtime.
	 * @param bool   $in_footer Whether to enqueue in footer.
	 * @return void
	 */
	public static function enqueueBuiltScript( string $handle, array $dependencies = array(), string $version = '', bool $in_footer = true ): void {
		$dir  = self::getPackageDir();
		$file = 'dist/js/admin-ui.js';

		if ( '' === $version ) {
			$path    = $dir . '/' . $file;
			$version = file_exists( $path ) ? (string) filemtime( $path ) : '1.3.0';
		}

		wp_enqueue_script(
			$handle,
			plugins_url( $file, $dir . '/composer.json' ),
			$dependencies,
			$version,
			$in_footer
		);
	}
}
