<?php
/**
 * Overlay component rendering helpers: modal, confirmDialog.
 *
 * @package MTWP\ADMIN\V110
 */

namespace MTWP\ADMIN\V110;

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
				'trigger'         => 'Open Modal',
				'render_trigger'  => true,
				'trigger_variant' => 'primary',
				'class'           => '',
				'trigger_class'   => '',
				'dialog_class'    => '',
				'header_class'    => '',
				'content_class'   => '',
				'footer_class'    => '',
				'fields_class'    => '',
				'footer'          => '',
				'footer_actions'  => array(),
				'fields'          => array(),
				'classes'         => array(),
				'attributes'      => array(),
				'data_attributes' => array(),
			)
		);

		$id                           = '' !== $args['id'] ? $args['id'] : 'mwp-modal-' . wp_unique_id();
		$title_id                     = $id . '-title';
		$classes                      = trim( 'mwp-modal ' . $args['class'] );
		$attributes                   = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), $classes, is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		$attributes['id']             = $id;
		$attributes['data-mwp-modal'] = '';
		$attributes['aria-hidden']    = 'true';
		$attributes['hidden']         = true;
		$has_footer                   = '' !== $args['footer'] || ! empty( $args['footer_actions'] );

		if ( wp_validate_boolean( $args['render_trigger'] ) && false !== $args['trigger'] ) {
			self::modalTrigger( $id, (string) $args['trigger'], (string) $args['trigger_variant'], (string) $args['trigger_class'] );
		}
		?>
		<div <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<div class="mwp-modal__overlay" data-mwp-modal-close></div>
			<div class="<?php echo esc_attr( trim( 'mwp-modal__dialog ' . $args['dialog_class'] ) ); ?>" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $title_id ); ?>" tabindex="-1">
				<div class="<?php echo esc_attr( trim( 'mwp-modal__header ' . $args['header_class'] ) ); ?>">
					<div class="mwp-modal__heading">
						<h4 id="<?php echo esc_attr( $title_id ); ?>"><?php echo esc_html( $args['title'] ); ?></h4>
					</div>
					<button class="mwp-icon-button mwp-modal__close" type="button" aria-label="Close" data-mwp-modal-close><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
				</div>
				<div class="<?php echo esc_attr( trim( 'mwp-modal__content ' . $args['content_class'] ) ); ?>">
					<?php if ( ! empty( $args['fields'] ) && is_array( $args['fields'] ) ) : ?>
						<div class="<?php echo esc_attr( trim( 'mwp-modal__fields mwp-form-fields ' . $args['fields_class'] ) ); ?>">
							<?php foreach ( $args['fields'] as $field ) : ?>
								<?php self::modalField( is_array( $field ) ? $field : array() ); ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
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
				'trigger'         => __( 'Delete', 'matterwp-admin-ui' ),
				'render_trigger'  => true,
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
		$trigger_variant = in_array( $args['trigger_variant'], array( 'primary', 'secondary', 'ghost', 'danger' ), true ) ? $args['trigger_variant'] : 'danger';
		$confirm_variant = in_array( $args['confirm_variant'], array( 'primary', 'secondary', 'ghost', 'danger' ), true ) ? $args['confirm_variant'] : 'danger';
		$classes         = trim( 'mwp-modal is-danger ' . $args['class'] );

		if ( wp_validate_boolean( $args['render_trigger'] ) && false !== $args['trigger'] ) {
			self::modalTrigger( $id, (string) $args['trigger'], $trigger_variant, (string) $args['trigger_class'] );
		}
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $id ); ?>" data-mwp-modal aria-hidden="true" hidden>
			<div class="mwp-modal__overlay" data-mwp-modal-close></div>
			<div class="<?php echo esc_attr( trim( 'mwp-modal__dialog ' . $args['dialog_class'] ) ); ?>" role="alertdialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $title_id ); ?>" tabindex="-1">
				<div class="<?php echo esc_attr( trim( 'mwp-modal__header ' . $args['header_class'] ) ); ?>">
					<div class="mwp-modal__heading">
						<h4 id="<?php echo esc_attr( $title_id ); ?>"><?php echo esc_html( $args['title'] ); ?></h4>
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
	 * @param string $trigger_class Trigger class.
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
				'id'              => '',
				'type'            => 'button',
				'variant'         => 'secondary',
				'size'            => 'standard',
				'icon'            => '',
				'icon_position'   => 'before',
				'class'           => '',
				'disabled'        => false,
				'loading'         => false,
				'autofocus'       => false,
				'data_attributes' => array(),
				'attributes'      => array(),
			)
		);

		$type                             = in_array( $args['type'], array( 'button', 'submit', 'reset' ), true ) ? $args['type'] : 'button';
		$variant                          = in_array( $args['variant'], array( 'primary', 'secondary', 'ghost', 'danger' ), true ) ? $args['variant'] : 'secondary';
		$size                             = in_array( $args['size'], array( 'standard', 'compact' ), true ) ? $args['size'] : 'standard';
		$icon_position                    = in_array( $args['icon_position'], array( 'before', 'after' ), true ) ? $args['icon_position'] : 'before';
		$icon                             = Components::iconMarkup( (string) $args['icon'] );
		$classes                          = trim( 'mwp-button is-' . $variant . ' ' . ( '' !== $icon ? 'has-icon is-icon-' . $icon_position . ' ' : '' ) . ( 'compact' === $size ? 'is-compact ' : '' ) . ( $args['loading'] ? 'is-loading ' : '' ) . $args['class'] );
		$attributes                       = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), $classes, is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		$attributes['id']                 = '' !== $args['id'] ? $args['id'] : null;
		$attributes['type']               = $type;
		$attributes['disabled']           = wp_validate_boolean( $args['disabled'] ) || wp_validate_boolean( $args['loading'] );
		$attributes['data-mwp-autofocus'] = $args['autofocus'] ? '' : null;
		$attributes['data-mwp-loading']   = $args['loading'] ? 'true' : null;
		?>
		<button <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php if ( '' !== $icon && 'before' === $icon_position ) : ?>
				<span class="mwp-button__icon" aria-hidden="true"><?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php endif; ?>
			<?php echo esc_html( $args['label'] ); ?>
			<?php if ( '' !== $icon && 'after' === $icon_position ) : ?>
				<span class="mwp-button__icon" aria-hidden="true"><?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php endif; ?>
		</button>
		<?php
	}

	/**
	 * Render a generated modal field.
	 *
	 * @param array<string, mixed> $field Field schema.
	 * @return void
	 */
	private static function modalField( array $field ): void {
		$name       = (string) ( $field['name'] ?? ( $field['key'] ?? '' ) );
		$id         = (string) ( $field['id'] ?? ( '' !== $name ? str_replace( '_', '-', $name ) : wp_unique_id( 'mwp-modal-field-' ) ) );
		$type       = (string) ( $field['type'] ?? 'text' );
		$label      = (string) ( $field['label'] ?? ( $field['title'] ?? $name ) );
		$field_args = array(
			'class'           => trim( ( $field['field_class'] ?? '' ) . ' ' . ( ! empty( $field['wide'] ) ? 'is-wide ' : '' ) . ( ! empty( $field['layout'] ) ? 'is-layout-' . sanitize_html_class( (string) $field['layout'] ) : '' ) ),
			'attributes'      => is_array( $field['field_attributes'] ?? null ) ? $field['field_attributes'] : array(),
			'data_attributes' => is_array( $field['field_data_attributes'] ?? null ) ? $field['field_data_attributes'] : array(),
		);

		Layout::field(
			$label,
			static function () use ( $field, $name, $id, $type ): void {
				$attributes                   = is_array( $field['attributes'] ?? null ) ? $field['attributes'] : array();
				$attributes['data-mwp-field'] = $name;
				$control_args                 = array_merge(
					$field,
					array(
						'id'              => $id,
						'name'            => $name,
						'value'           => $field['value'] ?? '',
						'placeholder'     => $field['placeholder'] ?? '',
						'autocomplete'    => $field['autocomplete'] ?? '',
						'min'             => $field['min'] ?? null,
						'max'             => $field['max'] ?? null,
						'step'            => $field['step'] ?? null,
						'readonly'        => ! empty( $field['readonly'] ),
						'required'        => ! empty( $field['required'] ),
						'disabled'        => ! empty( $field['disabled'] ),
						'attributes'      => $attributes,
						'data_attributes' => is_array( $field['data_attributes'] ?? null ) ? $field['data_attributes'] : array(),
					)
				);

				if ( in_array( $type, array( 'boolean', 'bool', 'switch' ), true ) ) {
					$input_attrs                   = is_array( $control_args['input_attrs'] ?? null ) ? $control_args['input_attrs'] : array();
					$input_attrs['data-mwp-field'] = $name;
					$control_args['input_attrs']   = $input_attrs;
					$control_args['attributes']    = is_array( $field['switch_attributes'] ?? null ) ? $field['switch_attributes'] : array();
					$control_args['checked']       = ! empty( $field['checked'] );
					Controls::switch( $control_args );
					return;
				}

				if ( in_array( $type, array( 'select', 'choice' ), true ) ) {
					$control_args['options'] = $field['options'] ?? ( $field['choices'] ?? array() );
					Controls::select( $control_args );
					return;
				}

				if ( 'textarea' === $type ) {
					Controls::textarea( $control_args );
					return;
				}

				$control_args['type'] = Controls::normalizeInputType( $type );
				Controls::input( $control_args );
			},
			$field_args
		);
	}
}
