<?php
/**
 * Segmented tab component renderer.
 *
 * @package MTWP\ADMIN\V120
 */

namespace MTWP\ADMIN\V120;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render segmented tabs and optional tab panels.
 */
class Tabs {

	/**
	 * Render a segmented tab control and optional tab panels.
	 *
	 * @param array<string, mixed> $args Tabs arguments.
	 * @return void
	 */
	public static function render( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'items'           => array(),
				'active'          => '',
				'label'           => __( 'Sections', 'matterwp-admin-ui' ),
				'variant'         => 'segmented',
				'size'            => 'normal',
				'grow'            => false,
				'class'           => '',
				'attributes'      => array(),
				'data_attributes' => array(),
			)
		);

		$items      = self::normalizeItems( is_array( $args['items'] ) ? $args['items'] : array() );
		$has_panels = self::hasPanels( $items );
		$active     = self::resolveActive( $items, (string) $args['active'] );
		$variant    = in_array( $args['variant'], array( 'segmented', 'plain' ), true ) ? $args['variant'] : 'segmented';
		$size       = in_array( $args['size'], array( 'compact', 'normal', 'large' ), true ) ? $args['size'] : 'normal';
		$classes    = trim( 'mwp-tabs is-' . $variant . ' is-size-' . $size . ( wp_validate_boolean( $args['grow'] ) ? ' is-grow' : '' ) . ' ' . $args['class'] );
		$set_id     = 'mwp-tabs-' . wp_unique_id();

		$tablist_attributes               = is_array( $args['attributes'] ) ? $args['attributes'] : array();
		$tablist_attributes['role']       = $tablist_attributes['role'] ?? 'tablist';
		$tablist_attributes['aria-label'] = $tablist_attributes['aria-label'] ?? $args['label'];
		$tablist_attributes               = Attrs::merge( $tablist_attributes, $classes, is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );

		if ( $has_panels ) {
			?>
			<div class="mwp-tabs-set" data-mwp-tabs data-mwp-tabs-active="<?php echo esc_attr( $active ); ?>">
			<?php
		}
		?>
		<div <?php echo Attrs::render( $tablist_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php foreach ( $items as $item ) : ?>
				<?php self::renderItem( $item, $active, $set_id, $has_panels ); ?>
			<?php endforeach; ?>
		</div>
		<?php if ( $has_panels ) : ?>
			<div class="mwp-tabs__panels">
				<?php foreach ( $items as $item ) : ?>
					<?php self::renderPanel( $item, $active, $set_id ); ?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Normalize tab items.
	 *
	 * @param array<mixed> $items Raw tab items.
	 * @return array<int, array<string, mixed>>
	 */
	private static function normalizeItems( array $items ): array {
		$normalized = array();

		foreach ( $items as $key => $item ) {
			$item = is_array( $item ) ? $item : array( 'label' => $item );
			$id   = (string) ( $item['id'] ?? ( is_int( $key ) ? sanitize_title( (string) ( $item['label'] ?? '' ) ) : $key ) );

			if ( '' === $id ) {
				$id = 'tab-' . count( $normalized );
			}

			$item['id'] = $id;

			if ( ! isset( $item['label'] ) ) {
				$item['label'] = ucfirst( str_replace( '-', ' ', $id ) );
			}

			$normalized[] = $item;
		}

		return $normalized;
	}

	/**
	 * Determine if tabs include panel content.
	 *
	 * @param array<int, array<string, mixed>> $items Tab items.
	 * @return bool
	 */
	private static function hasPanels( array $items ): bool {
		foreach ( $items as $item ) {
			if ( array_key_exists( 'content', $item ) || is_callable( $item['content_callback'] ?? null ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Resolve active tab.
	 *
	 * @param array<int, array<string, mixed>> $items Tab items.
	 * @param string                           $active Requested active tab.
	 * @return string
	 */
	private static function resolveActive( array $items, string $active ): string {
		foreach ( $items as $item ) {
			if ( $active === (string) $item['id'] && ! wp_validate_boolean( $item['disabled'] ?? false ) ) {
				return $active;
			}
		}

		foreach ( $items as $item ) {
			if ( wp_validate_boolean( $item['active'] ?? false ) && ! wp_validate_boolean( $item['disabled'] ?? false ) ) {
				return (string) $item['id'];
			}
		}

		foreach ( $items as $item ) {
			if ( ! wp_validate_boolean( $item['disabled'] ?? false ) ) {
				return (string) $item['id'];
			}
		}

		return '';
	}

	/**
	 * Render one tab item.
	 *
	 * @param array<string, mixed> $item Tab item.
	 * @param string               $active Active tab ID.
	 * @param string               $set_id Tab set ID.
	 * @param bool                 $has_panels Whether panels exist.
	 * @return void
	 */
	private static function renderItem( array $item, string $active, string $set_id, bool $has_panels ): void {
		$id       = (string) $item['id'];
		$disabled = wp_validate_boolean( $item['disabled'] ?? false );
		$selected = ! $disabled && $id === $active;
		$classes  = trim( 'mwp-tabs__item' . ( $selected ? ' is-active' : '' ) . ( $disabled ? ' is-disabled' : '' ) . ' ' . ( $item['class'] ?? '' ) );

		$attributes                  = is_array( $item['attributes'] ?? null ) ? $item['attributes'] : array();
		$attributes['id']            = $has_panels ? $set_id . '-tab-' . sanitize_html_class( $id ) : null;
		$attributes['class']         = $classes;
		$attributes['type']          = 'button';
		$attributes['role']          = 'tab';
		$attributes['aria-selected'] = $selected ? 'true' : 'false';
		$attributes['aria-disabled'] = $disabled ? 'true' : null;
		$attributes['aria-controls'] = $has_panels ? $set_id . '-' . sanitize_html_class( $id ) : null;
		$attributes['tabindex']      = $selected ? '0' : '-1';
		$attributes['data-mwp-tab']  = $id;
		?>
		<button <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<span class="mwp-tabs__label"><?php echo esc_html( (string) $item['label'] ); ?></span>
			<?php if ( isset( $item['badge'] ) && '' !== (string) $item['badge'] ) : ?>
				<span class="mwp-tabs__badge"><?php echo esc_html( (string) $item['badge'] ); ?></span>
			<?php endif; ?>
		</button>
		<?php
	}

	/**
	 * Render one tab panel.
	 *
	 * @param array<string, mixed> $item Tab item.
	 * @param string               $active Active tab ID.
	 * @param string               $set_id Tab set ID.
	 * @return void
	 */
	private static function renderPanel( array $item, string $active, string $set_id ): void {
		$id       = (string) $item['id'];
		$selected = $id === $active;
		?>
		<div
			id="<?php echo esc_attr( $set_id . '-' . sanitize_html_class( $id ) ); ?>"
			class="<?php echo esc_attr( trim( 'mwp-tabs__panel ' . ( $selected ? 'is-active ' : '' ) . ( $item['panel_class'] ?? '' ) ) ); ?>"
			role="tabpanel"
			data-mwp-panel="<?php echo esc_attr( $id ); ?>"
			aria-labelledby="<?php echo esc_attr( $set_id . '-tab-' . sanitize_html_class( $id ) ); ?>"
			<?php echo $selected ? '' : 'hidden'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		>
			<?php
			if ( is_callable( $item['content_callback'] ?? null ) ) {
				call_user_func( $item['content_callback'], $item );
			} elseif ( isset( $item['content'] ) ) {
				echo wp_kses_post( (string) $item['content'] );
			}
			?>
		</div>
		<?php
	}
}
