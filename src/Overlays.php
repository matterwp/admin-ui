<?php
/**
 * Overlay component rendering helpers: modal, confirmDialog.
 *
 * @package MatterWP\AdminUI
 */

namespace MatterWP\AdminUI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Overlays {

	public static function modal( array $args, callable $content ): void {
		$args = wp_parse_args(
			$args,
			array(
				'id'          => '',
				'title'       => '',
				'description' => '',
				'trigger'     => 'Open Modal',
				'class'       => '',
				'footer'      => '',
			)
		);

		$id      = '' !== $args['id'] ? $args['id'] : 'mwp-modal-' . wp_unique_id();
		$classes = trim( 'mwp-modal ' . $args['class'] );
		MatterAdminUI::button(
			array(
				'label'   => $args['trigger'],
				'variant' => 'primary',
				'class'   => 'mwp-modal-trigger',
			)
		);
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $id ); ?>" data-mwp-modal hidden>
			<div class="mwp-modal__overlay" data-mwp-modal-close></div>
			<div class="mwp-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $id . '-title' ); ?>">
				<div class="mwp-modal__header">
					<div>
						<h4 id="<?php echo esc_attr( $id . '-title' ); ?>"><?php echo esc_html( $args['title'] ); ?></h4>
						<?php if ( '' !== $args['description'] ) : ?>
							<p><?php echo esc_html( $args['description'] ); ?></p>
						<?php endif; ?>
					</div>
					<button class="mwp-icon-button mwp-modal__close" type="button" aria-label="Close" data-mwp-modal-close><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
				</div>
				<div class="mwp-modal__content">
					<?php $content(); ?>
				</div>
				<?php if ( '' !== $args['footer'] ) : ?>
					<div class="mwp-modal__footer">
						<?php echo wp_kses_post( $args['footer'] ); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	public static function confirmDialog( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'id'              => '',
				'title'           => '',
				'description'     => '',
				'trigger'         => __( 'Delete', 'boilerplate' ),
				'trigger_variant' => 'danger',
				'confirm_label'   => __( 'Confirm', 'boilerplate' ),
				'confirm_variant' => 'danger',
				'cancel_label'    => __( 'Cancel', 'boilerplate' ),
				'class'           => '',
			)
		);

		$id              = '' !== $args['id'] ? $args['id'] : 'mwp-confirm-' . wp_unique_id();
		$trigger_variant = in_array( $args['trigger_variant'], array( 'primary', 'secondary', 'ghost', 'danger' ), true ) ? $args['trigger_variant'] : 'danger';
		$confirm_variant = in_array( $args['confirm_variant'], array( 'primary', 'secondary', 'ghost', 'danger' ), true ) ? $args['confirm_variant'] : 'danger';
		$classes         = trim( 'mwp-modal is-danger ' . $args['class'] );

		MatterAdminUI::button(
			array(
				'label'   => $args['trigger'],
				'variant' => $trigger_variant,
				'class'   => 'mwp-modal-trigger',
			)
		);
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $id ); ?>" data-mwp-modal hidden>
			<div class="mwp-modal__overlay" data-mwp-modal-close></div>
			<div class="mwp-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $id . '-title' ); ?>">
				<div class="mwp-modal__header">
					<div>
						<h4 id="<?php echo esc_attr( $id . '-title' ); ?>"><?php echo esc_html( $args['title'] ); ?></h4>
						<?php if ( '' !== $args['description'] ) : ?>
							<p><?php echo esc_html( $args['description'] ); ?></p>
						<?php endif; ?>
					</div>
					<button class="mwp-icon-button mwp-modal__close" type="button" aria-label="Close" data-mwp-modal-close><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
				</div>
				<div class="mwp-modal__content">
					<p><?php echo esc_html( $args['description'] ); ?></p>
				</div>
				<div class="mwp-modal__footer">
					<div class="mwp-confirm-footer">
						<?php
						MatterAdminUI::button(
							array(
								'label'   => $args['cancel_label'],
								'variant' => 'ghost',
								'class'   => 'mwp-confirm-cancel',
							)
						);
						MatterAdminUI::button(
							array(
								'label'   => $args['confirm_label'],
								'variant' => $confirm_variant,
								'class'   => 'mwp-confirm-ok',
							)
						);
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
