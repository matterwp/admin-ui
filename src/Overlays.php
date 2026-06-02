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

/**
 * Overlay components such as modals and confirmation dialogs.
 */
class Overlays {

	/**
	 * Render a modal and its trigger.
	 *
	 * @param array    $args Modal arguments.
	 * @param callable $content Modal content callback.
	 * @return void
	 */
	public static function modal( array $args, callable $content ): void {
		$args = wp_parse_args(
			$args,
			array(
				'id'              => '',
				'title'           => '',
				'description'     => '',
				'trigger'         => 'Open Modal',
				'trigger_variant' => 'primary',
				'class'           => '',
				'trigger_class'   => '',
				'dialog_class'    => '',
				'header_class'    => '',
				'content_class'   => '',
				'footer_class'    => '',
				'footer'          => '',
				'footer_actions'  => array(),
			)
		);

		$id             = '' !== $args['id'] ? $args['id'] : 'mwp-modal-' . wp_unique_id();
		$title_id       = $id . '-title';
		$description_id = $id . '-description';
		$classes        = trim( 'mwp-modal ' . $args['class'] );
		$has_footer     = '' !== $args['footer'] || ! empty( $args['footer_actions'] );
		self::modalTrigger( $id, (string) $args['trigger'], (string) $args['trigger_variant'], (string) $args['trigger_class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $id ); ?>" data-mwp-modal aria-hidden="true" hidden>
			<div class="mwp-modal__overlay" data-mwp-modal-close></div>
			<div class="<?php echo esc_attr( trim( 'mwp-modal__dialog ' . $args['dialog_class'] ) ); ?>" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $title_id ); ?>"<?php echo '' !== $args['description'] ? ' aria-describedby="' . esc_attr( $description_id ) . '"' : ''; ?> tabindex="-1">
				<div class="<?php echo esc_attr( trim( 'mwp-modal__header ' . $args['header_class'] ) ); ?>">
					<div class="mwp-modal__heading">
						<h4 id="<?php echo esc_attr( $title_id ); ?>"><?php echo esc_html( $args['title'] ); ?></h4>
						<?php if ( '' !== $args['description'] ) : ?>
							<p id="<?php echo esc_attr( $description_id ); ?>"><?php echo esc_html( $args['description'] ); ?></p>
						<?php endif; ?>
					</div>
					<button class="mwp-icon-button mwp-modal__close" type="button" aria-label="Close" data-mwp-modal-close><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
				</div>
				<div class="<?php echo esc_attr( trim( 'mwp-modal__content ' . $args['content_class'] ) ); ?>">
					<?php $content(); ?>
				</div>
				<?php if ( $has_footer ) : ?>
					<div class="<?php echo esc_attr( trim( 'mwp-modal__footer ' . $args['footer_class'] ) ); ?>">
						<?php
						if ( ! empty( $args['footer_actions'] ) && is_array( $args['footer_actions'] ) ) {
							foreach ( $args['footer_actions'] as $action ) {
								self::footerAction( is_array( $action ) ? $action : array() );
							}
						} else {
							echo wp_kses_post( $args['footer'] );
						}
						?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a confirmation dialog.
	 *
	 * @param array $args Confirmation dialog arguments.
	 * @return void
	 */
	public static function confirmDialog( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'id'              => '',
				'title'           => '',
				'description'     => '',
				'trigger'         => __( 'Delete', 'matterwp-admin-ui' ),
				'trigger_variant' => 'danger',
				'confirm_label'   => __( 'Confirm', 'matterwp-admin-ui' ),
				'confirm_variant' => 'danger',
				'cancel_label'    => __( 'Cancel', 'matterwp-admin-ui' ),
				'class'           => '',
				'trigger_class'   => '',
				'dialog_class'    => '',
				'header_class'    => '',
				'footer_class'    => '',
			)
		);

		$id              = '' !== $args['id'] ? $args['id'] : 'mwp-confirm-' . wp_unique_id();
		$title_id        = $id . '-title';
		$description_id  = $id . '-description';
		$trigger_variant = in_array( $args['trigger_variant'], array( 'primary', 'secondary', 'ghost', 'danger' ), true ) ? $args['trigger_variant'] : 'danger';
		$confirm_variant = in_array( $args['confirm_variant'], array( 'primary', 'secondary', 'ghost', 'danger' ), true ) ? $args['confirm_variant'] : 'danger';
		$classes         = trim( 'mwp-modal is-danger ' . $args['class'] );

		self::modalTrigger( $id, (string) $args['trigger'], $trigger_variant, (string) $args['trigger_class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $id ); ?>" data-mwp-modal aria-hidden="true" hidden>
			<div class="mwp-modal__overlay" data-mwp-modal-close></div>
			<div class="<?php echo esc_attr( trim( 'mwp-modal__dialog ' . $args['dialog_class'] ) ); ?>" role="alertdialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $title_id ); ?>"<?php echo '' !== $args['description'] ? ' aria-describedby="' . esc_attr( $description_id ) . '"' : ''; ?> tabindex="-1">
				<div class="<?php echo esc_attr( trim( 'mwp-modal__header ' . $args['header_class'] ) ); ?>">
					<div class="mwp-modal__heading">
						<h4 id="<?php echo esc_attr( $title_id ); ?>"><?php echo esc_html( $args['title'] ); ?></h4>
						<?php if ( '' !== $args['description'] ) : ?>
							<p id="<?php echo esc_attr( $description_id ); ?>"><?php echo esc_html( $args['description'] ); ?></p>
						<?php endif; ?>
					</div>
					<button class="mwp-icon-button mwp-modal__close" type="button" aria-label="Close" data-mwp-modal-close><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
				</div>
				<div class="<?php echo esc_attr( trim( 'mwp-modal__footer ' . $args['footer_class'] ) ); ?>">
					<div class="mwp-confirm-footer">
						<?php
						self::footerAction(
							array(
								'label'           => $args['cancel_label'],
								'variant'         => 'ghost',
								'class'           => 'mwp-confirm-cancel',
								'data_attributes' => array( 'mwp-modal-close' => '' ),
								'autofocus'       => true,
							)
						);
						?>
						<?php
						self::footerAction(
							array(
								'label'   => $args['confirm_label'],
								'variant' => $confirm_variant,
								'type'    => 'submit',
							)
						);
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a modal trigger button.
	 *
	 * @param string $modal_id Target modal ID.
	 * @param string $label Trigger label.
	 * @param string $variant Button variant.
	 * @return void
	 */
	private static function modalTrigger( string $modal_id, string $label, string $variant, string $trigger_class = '' ): void {
		$variant = in_array( $variant, array( 'primary', 'secondary', 'ghost', 'danger' ), true ) ? $variant : 'primary';
		$classes = trim( 'mwp-button is-' . $variant . ' mwp-modal-trigger ' . $trigger_class );
		?>
		<button class="<?php echo esc_attr( $classes ); ?>" type="button" data-mwp-modal-trigger aria-haspopup="dialog" aria-controls="<?php echo esc_attr( $modal_id ); ?>" aria-expanded="false">
			<?php echo esc_html( $label ); ?>
		</button>
		<?php
	}

	/**
	 * Render a modal footer action.
	 *
	 * @param array $args Footer action arguments.
	 * @return void
	 */
	private static function footerAction( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'label'           => '',
				'type'            => 'button',
				'variant'         => 'secondary',
				'class'           => '',
				'disabled'        => false,
				'autofocus'       => false,
				'data_attributes' => array(),
			)
		);

		$type       = in_array( $args['type'], array( 'button', 'submit', 'reset' ), true ) ? $args['type'] : 'button';
		$variant    = in_array( $args['variant'], array( 'primary', 'secondary', 'ghost', 'danger' ), true ) ? $args['variant'] : 'secondary';
		$classes    = trim( 'mwp-button is-' . $variant . ' ' . $args['class'] );
		$data_attrs = '';

		if ( ! empty( $args['data_attributes'] ) && is_array( $args['data_attributes'] ) ) {
			foreach ( $args['data_attributes'] as $key => $value ) {
				$data_attrs .= ' data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
			}
		}
		?>
		<button class="<?php echo esc_attr( $classes ); ?>" type="<?php echo esc_attr( $type ); ?>"<?php disabled( $args['disabled'] ); ?><?php echo $data_attrs; ?><?php echo $args['autofocus'] ? ' data-mwp-autofocus' : ''; ?>>
			<?php echo esc_html( $args['label'] ); ?>
		</button>
		<?php
	}
}
