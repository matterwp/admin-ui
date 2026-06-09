<?php
/**
 * Admin navigation component.
 *
 * @package MTWP\ADMIN\V120
 */

namespace MTWP\ADMIN\V120;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render grouped admin navigation with an animated active indicator.
 */
class Navigation {

	/**
	 * Render navigation.
	 *
	 * @param array $args Navigation arguments.
	 * @return void
	 */
	public static function render( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'groups'          => array(),
				'items'           => array(),
				'active_item'     => '',
				'aria_label'      => __( 'Admin sections', 'matterwp-admin-ui' ),
				'class'           => '',
				'attributes'      => array(),
				'data_attributes' => array(),
			)
		);

		$source = ! empty( $args['groups'] ) && is_array( $args['groups'] ) ? $args['groups'] : $args['items'];
		$groups = self::normalizeGroups( is_array( $source ) ? $source : array() );

		if ( empty( $groups ) ) {
			return;
		}

		$top_groups    = array();
		$bottom_groups = array();

		foreach ( $groups as $group ) {
			if ( 'bottom' === $group['alignment'] ) {
				$bottom_groups[] = $group;
			} else {
				$top_groups[] = $group;
			}
		}

		$attributes               = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), trim( 'mwp-option-nav ' . $args['class'] ), is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		$attributes['aria-label'] = (string) $args['aria_label'];
		?>
		<nav <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php if ( ! empty( $top_groups ) ) : ?>
				<div class="mwp-nav-sections">
					<?php foreach ( $top_groups as $group ) : ?>
						<?php self::group( $group, (string) $args['active_item'] ); ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $bottom_groups ) ) : ?>
				<div class="mwp-nav-bottom">
					<?php foreach ( $bottom_groups as $group ) : ?>
						<?php self::group( $group, (string) $args['active_item'] ); ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</nav>
		<?php
	}

	/**
	 * Normalize flat items and grouped items into navigation groups.
	 *
	 * @param array<mixed> $source Navigation groups or items.
	 * @return array<int, array<string, mixed>>
	 */
	private static function normalizeGroups( array $source ): array {
		$has_groups = false;

		foreach ( $source as $entry ) {
			if ( is_array( $entry ) && array_key_exists( 'items', $entry ) ) {
				$has_groups = true;
				break;
			}
		}

		if ( ! $has_groups ) {
			return array(
				array(
					'label'     => '',
					'alignment' => 'top',
					'items'     => $source,
				),
			);
		}

		$groups = array();

		foreach ( $source as $group ) {
			if ( ! is_array( $group ) || empty( $group['items'] ) || ! is_array( $group['items'] ) ) {
				continue;
			}

			$alignment = (string) ( $group['alignment'] ?? 'top' );
			$groups[]  = array(
				'label'     => (string) ( $group['label'] ?? '' ),
				'alignment' => in_array( $alignment, array( 'top', 'bottom' ), true ) ? $alignment : 'top',
				'items'     => $group['items'],
			);
		}

		return $groups;
	}

	/**
	 * Render one navigation group.
	 *
	 * @param array<string, mixed> $group Navigation group.
	 * @param string               $active_item Active item ID.
	 * @return void
	 */
	private static function group( array $group, string $active_item ): void {
		$alignment = (string) $group['alignment'];
		?>
		<div class="mwp-nav-group mwp-nav-items is-<?php echo esc_attr( $alignment ); ?>">
			<?php if ( '' !== $group['label'] ) : ?>
				<div class="mwp-nav-label"><?php echo esc_html( $group['label'] ); ?></div>
			<?php endif; ?>
			<?php foreach ( $group['items'] as $key => $item ) : ?>
				<?php self::item( $key, $item, $active_item ); ?>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render one navigation item.
	 *
	 * @param mixed  $key Item key.
	 * @param mixed  $item Item configuration.
	 * @param string $active_item Active item ID.
	 * @return void
	 */
	private static function item( $key, $item, string $active_item ): void {
		$item   = is_array( $item ) ? $item : array( 'label' => $item );
		$id     = (string) ( $item['id'] ?? ( is_int( $key ) ? ( $item['label'] ?? $key ) : $key ) );
		$label  = (string) ( $item['label'] ?? ucfirst( str_replace( '-', ' ', $id ) ) );
		$url    = (string) ( $item['url'] ?? '#' );
		$active = '' !== $active_item ? $id === $active_item : ! empty( $item['active'] );
		?>
		<div class="mwp-nav-item <?php echo $active ? 'active' : ''; ?>" data-ui-tab="<?php echo esc_attr( $id ); ?>">
			<?php if ( ! empty( $item['icon'] ) ) : ?>
				<span class="icon" aria-hidden="true"><?php echo Components::iconMarkup( (string) $item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php endif; ?>
			<span><?php echo esc_html( $label ); ?></span>
		</div>
		<?php
	}
}
