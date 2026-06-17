<?php
/**
 * Premium/gating rendering helpers: premiumBadge, controlLockedAttrs.
 *
 * @package MTWP\ADMIN\V130
 */

namespace MTWP\ADMIN\V130;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Premium and locked-control rendering helpers.
 */
class Premium {

	/**
	 * Render a premium feature badge.
	 *
	 * @param array $args Premium badge arguments.
	 * @return void
	 */
	public static function premiumBadge( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'label'      => __( 'Pro feature', 'matterwp-admin-ui' ),
				'url'        => '',
				'link_label' => __( 'Upgrade', 'matterwp-admin-ui' ),
			)
		);
		?>
		<div class="mwp-premium-badge">
			<span class="badge-label">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/></svg>
				<?php echo esc_html( $args['label'] ); ?>
			</span>
			<?php if ( '' !== $args['url'] ) : ?>
				<a href="<?php echo esc_url( $args['url'] ); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo esc_html( $args['link_label'] ); ?>
					<span class="mwp-upgrade-icon" aria-hidden="true">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
					</span>
				</a>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render disabled attributes for locked controls.
	 *
	 * @param bool $locked Whether the control is locked.
	 * @return void
	 */
	public static function controlLockedAttrs( bool $locked ): void {
		if ( $locked ) {
			echo ' disabled data-pro-locked="true"';
		}
	}
}
