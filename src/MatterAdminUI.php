<?php
/**
 * Admin UI rendering helpers.
 *
 * @package MatterWP\AdminUI
 */

namespace MatterWP\AdminUI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Facade for reusable admin UI rendering helpers.
 *
 * All methods delegate to focused sub-classes, maintaining backward compatibility.
 */
class MatterAdminUI {

	public static function panel( string $id, callable $content ): void {
		Layout::panel( $id, $content );
	}

	public static function section( array $args, callable $content ): void {
		Layout::section( $args, $content );
	}

	public static function option( array $args, callable $control ): void {
		Layout::option( $args, $control );
	}

	public static function card( array $args, callable $content ): void {
		Layout::card( $args, $content );
	}

	public static function form( array $args, callable $content ): void {
		Layout::form( $args, $content );
	}

	public static function field( string $label, callable $control ): void {
		Layout::field( $label, $control );
	}

	public static function emptyState( array $args ): void {
		Layout::emptyState( $args );
	}

	public static function switch( array $args ): void {
		Controls::switch( $args );
	}

	public static function input( array $args ): void {
		Controls::input( $args );
	}

	public static function textarea( array $args ): void {
		Controls::textarea( $args );
	}

	public static function select( array $args ): void {
		Controls::select( $args );
	}

	public static function button( array $args ): void {
		Controls::button( $args );
	}

	public static function badge( array $args ): void {
		Controls::badge( $args );
	}

	public static function notice( array $args ): void {
		Controls::notice( $args );
	}

	public static function modal( array $args, callable $content ): void {
		Overlays::modal( $args, $content );
	}

	public static function confirmDialog( array $args ): void {
		Overlays::confirmDialog( $args );
	}

	public static function lightbox( array $args ): void {
		Components::lightbox( $args );
	}

	public static function accordion( array $args ): void {
		Components::accordion( $args );
	}

	public static function table( array $args ): void {
		Components::table( $args );
	}

	public static function paginatedTable( array $args ): void {
		Components::paginatedTable( $args );
	}

	public static function progress( array $args ): void {
		Components::progress( $args );
	}

	public static function statCard( array $args ): void {
		Components::statCard( $args );
	}

	public static function colorPicker( array $args ): void {
		InputGroups::colorPicker( $args );
	}

	public static function inputButton( array $args ): void {
		InputGroups::inputButton( $args );
	}

	public static function radioGroup( array $args ): void {
		InputGroups::radioGroup( $args );
	}

	public static function buttonGroup( array $args ): void {
		InputGroups::buttonGroup( $args );
	}

	public static function mediaField( array $args ): void {
		InputGroups::mediaField( $args );
	}

	public static function premiumBadge( array $args ): void {
		Premium::premiumBadge( $args );
	}

	public static function controlLockedAttrs( bool $locked ): void {
		Premium::controlLockedAttrs( $locked );
	}

	public static function attrs( array $attributes ): string {
		return Attrs::render( $attributes );
	}

	public static function dataAttrs( array $attributes ): string {
		return Attrs::data( $attributes );
	}
}
