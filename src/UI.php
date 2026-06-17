<?php
/**
 * Admin UI rendering helpers.
 *
 * @package MTWP\ADMIN\V130
 */

namespace MTWP\ADMIN\V130;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Facade for reusable admin UI rendering helpers.
 *
 * All methods delegate to focused sub-classes, maintaining backward compatibility.
 */
class UI {

	/**
	 * Render admin navigation.
	 *
	 * @param array $args Navigation arguments.
	 * @return void
	 */
	public static function navigation( array $args ): void {
		Navigation::render( $args );
	}

	/**
	 * Render a tab panel wrapper.
	 *
	 * @param string   $id Panel identifier.
	 * @param callable $content Content callback.
	 * @return void
	 */
	public static function panel( string $id, callable $content ): void {
		Layout::panel( $id, $content );
	}

	/**
	 * Render an admin app shell.
	 *
	 * @param array    $args App shell arguments.
	 * @param callable $content Content callback.
	 * @return void
	 */
	public static function app( array $args, callable $content ): void {
		Layout::app( $args, $content );
	}

	/**
	 * Render a settings section.
	 *
	 * @param array    $args Section arguments.
	 * @param callable $content Content callback.
	 * @return void
	 */
	public static function section( array $args, callable $content ): void {
		Layout::section( $args, $content );
	}

	/**
	 * Render a generic grid wrapper.
	 *
	 * @param array    $args Grid arguments.
	 * @param callable $content Content callback.
	 * @return void
	 */
	public static function grid( array $args, callable $content ): void {
		Layout::grid( $args, $content );
	}

	/**
	 * Render a section divider.
	 *
	 * @param array $args Divider arguments.
	 * @return void
	 */
	public static function divider( array $args = array() ): void {
		Layout::divider( $args );
	}

	/**
	 * Render a settings option row.
	 *
	 * @param array    $args Option arguments.
	 * @param callable $control Control callback.
	 * @return void
	 */
	public static function option( array $args, callable $control ): void {
		Layout::option( $args, $control );
	}

	/**
	 * Render an option row from a schema field definition.
	 *
	 * @param array $schema Field schema.
	 * @param mixed $value Current value.
	 * @param array $overrides Option/control overrides.
	 * @return void
	 */
	public static function schemaOption( array $schema, $value = null, array $overrides = array() ): void {
		Layout::schemaOption( $schema, $value, $overrides );
	}

	/**
	 * Render a card container.
	 *
	 * @param array    $args Card arguments.
	 * @param callable $content Content callback.
	 * @return void
	 */
	public static function card( array $args, callable $content ): void {
		Layout::card( $args, $content );
	}

	/**
	 * Render a grouped form container.
	 *
	 * @param array    $args Form arguments.
	 * @param callable $content Content callback.
	 * @return void
	 */
	public static function form( array $args, callable $content ): void {
		Layout::form( $args, $content );
	}

	/**
	 * Render a labeled field wrapper.
	 *
	 * @param string   $label Field label.
	 * @param callable $control Control callback.
	 * @param array    $args Field arguments.
	 * @return void
	 */
	public static function field( string $label, callable $control, array $args = array() ): void {
		Layout::field( $label, $control, $args );
	}

	/**
	 * Render a field grid wrapper.
	 *
	 * @param array    $args Field grid arguments.
	 * @param callable $content Content callback.
	 * @return void
	 */
	public static function fieldGrid( array $args, callable $content ): void {
		Layout::fieldGrid( $args, $content );
	}

	/**
	 * Render a toggle switch control.
	 *
	 * @param array $args Switch arguments.
	 * @return void
	 */
	public static function switch( array $args ): void {
		Controls::switch( $args );
	}

	/**
	 * Render an input control.
	 *
	 * @param array $args Input arguments.
	 * @return void
	 */
	public static function input( array $args ): void {
		Controls::input( $args );
	}

	/**
	 * Render a textarea control.
	 *
	 * @param array $args Textarea arguments.
	 * @return void
	 */
	public static function textarea( array $args ): void {
		Controls::textarea( $args );
	}

	/**
	 * Render a select control.
	 *
	 * @param array $args Select arguments.
	 * @return void
	 */
	public static function select( array $args ): void {
		Controls::select( $args );
	}

	/**
	 * Render a button control.
	 *
	 * @param array $args Button arguments.
	 * @return void
	 */
	public static function button( array $args ): void {
		Controls::button( $args );
	}

	/**
	 * Render a badge.
	 *
	 * @param array $args Badge arguments.
	 * @return void
	 */
	public static function badge( array $args ): void {
		Controls::badge( $args );
	}

	/**
	 * Render an inline notice.
	 *
	 * @param array $args Notice arguments.
	 * @return void
	 */
	public static function notice( array $args ): void {
		Controls::notice( $args );
	}

	/**
	 * Render a modal and its trigger.
	 *
	 * @param array    $args Modal arguments.
	 * @param callable $content Modal content callback.
	 * @return void
	 */
	public static function modal( array $args, callable $content ): void {
		Overlays::modal( $args, $content );
	}

	/**
	 * Render a confirmation dialog.
	 *
	 * @param array $args Confirmation dialog arguments.
	 * @return void
	 */
	public static function confirmDialog( array $args ): void {
		Overlays::confirmDialog( $args );
	}

	/**
	 * Render a lightbox trigger.
	 *
	 * @param array $args Lightbox arguments.
	 * @return void
	 */
	public static function lightbox( array $args ): void {
		Components::lightbox( $args );
	}

	/**
	 * Render an accordion.
	 *
	 * @param array $args Accordion arguments.
	 * @return void
	 */
	public static function accordion( array $args ): void {
		Components::accordion( $args );
	}

	/**
	 * Render a table.
	 *
	 * @param array $args Table arguments.
	 * @return void
	 */
	public static function table( array $args ): void {
		Components::table( $args );
	}

	/**
	 * Render a client-side paginated table.
	 *
	 * @param array $args Paginated table arguments.
	 * @return void
	 */
	public static function paginatedTable( array $args ): void {
		Components::paginatedTable( $args );
	}

	/**
	 * Render a richer data table.
	 *
	 * @param array $args Data table arguments.
	 * @return void
	 */
	public static function dataTable( array $args ): void {
		Components::dataTable( $args );
	}

	/**
	 * Render DataTable filter buttons and search input.
	 *
	 * @param array $args Filter toolbar arguments.
	 * @return void
	 */
	public static function tableFilters( array $args ): void {
		Components::tableFilters( $args );
	}

	/**
	 * Return typed data table rows as HTML.
	 *
	 * @param array $args Data table row arguments.
	 * @return string
	 */
	public static function dataTableRows( array $args ): string {
		return Components::dataTableRows( $args );
	}

	/**
	 * Return one typed data table row as HTML.
	 *
	 * @param array<string, mixed>             $row Row data.
	 * @param array<int, array<string, mixed>> $columns Columns.
	 * @param array<string, mixed>             $args Table args.
	 * @return string
	 */
	public static function dataTableRow( array $row, array $columns, array $args = array() ): string {
		return Components::dataTableRow( $row, $columns, $args );
	}

	/**
	 * Build the supported DataTable AJAX response payload.
	 *
	 * @param array $args Response arguments.
	 * @return array<string, mixed>
	 */
	public static function dataTableResponse( array $args ): array {
		return Components::dataTableResponse( $args );
	}

	/**
	 * Render compact action buttons for table action columns.
	 *
	 * @param array $args Action group arguments.
	 * @return void
	 */
	public static function actionGroup( array $args ): void {
		Components::actionGroup( $args );
	}

	/**
	 * Return named icon markup.
	 *
	 * @param string $icon Icon name.
	 * @return string
	 */
	public static function icon( string $icon ): string {
		return Components::iconMarkup( $icon );
	}

	/**
	 * Render an empty state.
	 *
	 * @param array         $args Empty state arguments.
	 * @param callable|null $content Optional custom content callback.
	 * @return void
	 */
	public static function emptyState( array $args, callable $content = null ): void {
		Components::emptyState( $args, $content );
	}

	/**
	 * Render a switch grid.
	 *
	 * @param array $args Switch grid arguments.
	 * @return void
	 */
	public static function switchGrid( array $args ): void {
		Components::switchGrid( $args );
	}

	/**
	 * Render a choice grid.
	 *
	 * @param array $args Choice grid arguments.
	 * @return void
	 */
	public static function choiceGrid( array $args ): void {
		Components::choiceGrid( $args );
	}

	/**
	 * Render a segmented tab control.
	 *
	 * @param array $args Tabs arguments.
	 * @return void
	 */
	public static function tabs( array $args ): void {
		Tabs::render( $args );
	}

	/**
	 * Render a unit input control.
	 *
	 * @param array $args Unit input arguments.
	 * @return void
	 */
	public static function unitInput( array $args ): void {
		Controls::unitInput( $args );
	}

	/**
	 * Render a key/value list.
	 *
	 * @param array $args Key/value list arguments.
	 * @return void
	 */
	public static function keyValueList( array $args ): void {
		Components::keyValueList( $args );
	}

	/**
	 * Render an action bar.
	 *
	 * @param array $args Action bar arguments.
	 * @return void
	 */
	public static function actionBar( array $args ): void {
		Components::actionBar( $args );
	}

	/**
	 * Render a result/preview card.
	 *
	 * @param array $args Result card arguments.
	 * @return void
	 */
	public static function resultCard( array $args ): void {
		Components::resultCard( $args );
	}

	/**
	 * Return a badge table cell.
	 *
	 * @param string $label Badge label.
	 * @param mixed  $args Badge args.
	 * @return string
	 */
	public static function badgeCell( string $label, $args = array() ): string {
		return Components::badgeCell( $label, $args );
	}

	/**
	 * Return a link table cell.
	 *
	 * @param string $label Link label.
	 * @param string $url Link URL.
	 * @param array  $args Link args.
	 * @return string
	 */
	public static function linkCell( string $label, string $url, array $args = array() ): string {
		return Components::linkCell( $label, $url, $args );
	}

	/**
	 * Return a code table cell.
	 *
	 * @param mixed $value Cell value.
	 * @return string
	 */
	public static function codeCell( $value ): string {
		return Components::codeCell( $value );
	}

	/**
	 * Return an image table cell.
	 *
	 * @param string $src Image URL.
	 * @param string $alt Image alt.
	 * @param array  $args Image args.
	 * @return string
	 */
	public static function imageCell( string $src, string $alt = '', array $args = array() ): string {
		return Components::imageCell( $src, $alt, $args );
	}

	/**
	 * Return a date table cell.
	 *
	 * @param mixed  $value Date value.
	 * @param string $format Date format.
	 * @return string
	 */
	public static function dateCell( $value, string $format = '' ): string {
		return Components::dateCell( $value, $format );
	}

	/**
	 * Return an actions table cell.
	 *
	 * @param array $actions Actions.
	 * @return string
	 */
	public static function actionsCell( array $actions ): string {
		return Components::actionsCell( $actions );
	}

	/**
	 * Render a progress indicator.
	 *
	 * @param array $args Progress arguments.
	 * @return void
	 */
	public static function progress( array $args ): void {
		Components::progress( $args );
	}

	/**
	 * Render a statistic card.
	 *
	 * @param array $args Statistic card arguments.
	 * @return void
	 */
	public static function statCard( array $args ): void {
		Components::statCard( $args );
	}

	/**
	 * Render a color picker synced with a text input.
	 *
	 * @param array $args Color picker arguments.
	 * @return void
	 */
	public static function colorPicker( array $args ): void {
		InputGroups::colorPicker( $args );
	}

	/**
	 * Render an input and button pair.
	 *
	 * @param array $args Input button arguments.
	 * @return void
	 */
	public static function inputButton( array $args ): void {
		InputGroups::inputButton( $args );
	}

	/**
	 * Render a radio group.
	 *
	 * @param array $args Radio group arguments.
	 * @return void
	 */
	public static function radioGroup( array $args ): void {
		InputGroups::radioGroup( $args );
	}

	/**
	 * Render a group of buttons.
	 *
	 * @param array $args Button group arguments.
	 * @return void
	 */
	public static function buttonGroup( array $args ): void {
		InputGroups::buttonGroup( $args );
	}

	/**
	 * Render a WordPress media picker field.
	 *
	 * @param array $args Media field arguments.
	 * @return void
	 */
	public static function mediaField( array $args ): void {
		InputGroups::mediaField( $args );
	}

	/**
	 * Render a premium feature badge.
	 *
	 * @param array $args Premium badge arguments.
	 * @return void
	 */
	public static function premiumBadge( array $args ): void {
		Premium::premiumBadge( $args );
	}

	/**
	 * Render disabled attributes for locked controls.
	 *
	 * @param bool $locked Whether the control is locked.
	 * @return void
	 */
	public static function controlLockedAttrs( bool $locked ): void {
		Premium::controlLockedAttrs( $locked );
	}

	/**
	 * Render an escaped HTML attribute string.
	 *
	 * @param array $attributes Attribute map.
	 * @return string
	 */
	public static function attrs( array $attributes ): string {
		return Attrs::render( $attributes );
	}

	/**
	 * Render escaped data attributes.
	 *
	 * @param array $attributes Data attribute map without the data- prefix.
	 * @return string
	 */
	public static function dataAttrs( array $attributes ): string {
		return Attrs::data( $attributes );
	}
}
