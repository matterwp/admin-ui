<?php
/**
 * Compound input rendering helpers: colorPicker, inputButton, radioGroup, buttonGroup, mediaField.
 *
 * @package MatterWP\AdminUI
 */

namespace MatterWP\AdminUI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compound input rendering helpers.
 */
class InputGroups {

	/**
	 * Render a color picker synced with a text input.
	 *
	 * @param array $args Color picker arguments.
	 * @return void
	 */
	public static function colorPicker( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'name'         => '',
				'value'        => '#059669',
				'label'        => '',
				'placeholder'  => '#000000',
				'class'        => '',
				'swatch_class' => '',
				'input_class'  => '',
				'disabled'     => false,
			)
		);

		$classes        = trim( 'mwp-color-picker ' . $args['class'] );
		$swatch_classes = trim( 'mwp-color-picker__swatch ' . $args['swatch_class'] );
		$input_classes  = trim( 'mwp-color-picker__input ' . $args['input_class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" data-mwp-color-picker>
			<input class="<?php echo esc_attr( $swatch_classes ); ?>" type="color" value="<?php echo esc_attr( $args['value'] ); ?>" data-mwp-color-swatch <?php disabled( $args['disabled'] ); ?>>
			<input class="<?php echo esc_attr( $input_classes ); ?>" <?php echo '' !== $args['name'] ? ' name="' . esc_attr( $args['name'] ) . '"' : ''; ?> type="text" value="<?php echo esc_attr( $args['value'] ); ?>" placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" data-mwp-color-input <?php disabled( $args['disabled'] ); ?>>
		</div>
		<?php
	}

	/**
	 * Render an input and button pair.
	 *
	 * @param array $args Input button arguments.
	 * @return void
	 */
	public static function inputButton( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'input'  => array(),
				'button' => array(),
				'class'  => '',
				'wide'   => false,
			)
		);

		$classes = trim( 'mwp-input-button ' . ( $args['wide'] ? 'is-full-width ' : '' ) . $args['class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<?php
			MatterAdminUI::input( $args['input'] );
			MatterAdminUI::button( $args['button'] );
			?>
		</div>
		<?php
	}

	/**
	 * Render a radio group.
	 *
	 * @param array $args Radio group arguments.
	 * @return void
	 */
	public static function radioGroup( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'name'        => '',
				'value'       => '',
				'options'     => array(),
				'class'       => '',
				'item_class'  => '',
				'input_class' => '',
				'label_class' => '',
			)
		);

		$has_name      = '' !== $args['name'];
		$name          = $has_name ? $args['name'] : 'mwp-radio-' . wp_unique_id();
		$classes       = trim( 'mwp-radio-group ' . $args['class'] );
		$item_classes  = trim( 'mwp-radio-group__item ' . $args['item_class'] );
		$input_classes = trim( $args['input_class'] );
		$label_classes = trim( $args['label_class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" role="radiogroup">
			<?php foreach ( $args['options'] as $value => $label ) : ?>
				<label class="<?php echo esc_attr( $item_classes ); ?>">
					<input class="<?php echo esc_attr( $input_classes ); ?>" name="<?php echo esc_attr( $name ); ?>" type="radio" value="<?php echo esc_attr( (string) $value ); ?>" <?php checked( (string) $args['value'], (string) $value ); ?> <?php echo $has_name ? '' : ' data-mwp-ignore-autosave="true"'; ?>>
					<span class="<?php echo esc_attr( $label_classes ); ?>"><?php echo esc_html( $label ); ?></span>
				</label>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render a group of buttons.
	 *
	 * @param array $args Button group arguments.
	 * @return void
	 */
	public static function buttonGroup( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'buttons' => array(),
				'class'   => '',
			)
		);

		$classes = trim( 'mwp-button-group ' . $args['class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" role="group">
			<?php foreach ( $args['buttons'] as $button ) : ?>
				<?php MatterAdminUI::button( $button ); ?>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render a WordPress media picker field.
	 *
	 * @param array $args Media field arguments.
	 * @return void
	 */
	public static function mediaField( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'name'            => '',
				'value'           => 0,
				'mode'            => 'compact',
				'preview_size'    => 'thumbnail',
				'preview_height'  => '',
				'preview_ratio'   => '',
				'library_type'    => 'image',
				'button_text'     => __( 'Select Image', 'matterwp-admin-ui' ),
				'remove_text'     => __( 'Remove Image', 'matterwp-admin-ui' ),
				'media_title'     => __( 'Select Image', 'matterwp-admin-ui' ),
				'media_button'    => __( 'Use Image', 'matterwp-admin-ui' ),
				'placeholder'     => __( 'No image selected', 'matterwp-admin-ui' ),
				'class'           => '',
				'preview_class'   => '',
				'actions_class'   => '',
				'input_class'     => '',
				'choose_class'    => '',
				'remove_class'    => '',
				'choose_icon'     => 'image',
				'remove_icon'     => 'trash',
				'data_attributes' => array(),
				'attributes'      => array(),
				'slots'           => array(),
				'before'          => null,
				'after'           => null,
				'preview'         => null,
				'actions'         => null,
			)
		);

		/**
		 * Filters media field arguments before rendering.
		 *
		 * @param array $args Media field arguments.
		 */
		$args = apply_filters( 'matterwp_admin_ui_media_field_args', $args );

		$attachment_id = absint( $args['value'] );
		$image_url     = $attachment_id ? wp_get_attachment_image_url( $attachment_id, $args['preview_size'] ) : '';
		$uid           = wp_unique_id( 'mwp-media-' );
		$mode          = in_array( $args['mode'], array( 'compact', 'logo', 'wide', 'button_only' ), true ) ? $args['mode'] : 'compact';
		$mode_class    = str_replace( '_', '-', $mode );
		$classes       = trim( 'mwp-media-field is-' . $mode_class . ' ' . ( $image_url ? 'has-image ' : 'is-empty ' ) . $args['class'] );
		$styles        = array();
		$slots         = is_array( $args['slots'] ) ? $args['slots'] : array();
		$before        = $args['before'] ?? ( $slots['before'] ?? null );
		$after         = $args['after'] ?? ( $slots['after'] ?? null );
		$preview       = $args['preview'] ?? ( $slots['preview'] ?? null );
		$actions       = $args['actions'] ?? ( $slots['actions'] ?? null );

		if ( '' !== $args['preview_height'] ) {
			$styles[] = '--mwp-media-preview-height: ' . esc_attr( (string) $args['preview_height'] );
		}

		if ( '' !== $args['preview_ratio'] ) {
			$styles[] = '--mwp-media-preview-ratio: ' . esc_attr( (string) $args['preview_ratio'] );
		}

		$attributes = array_merge(
			is_array( $args['attributes'] ) ? $args['attributes'] : array(),
			array(
				'class'            => $classes,
				'data-media-field' => $uid,
			)
		);

		if ( ! empty( $styles ) ) {
			$attributes['style'] = implode( '; ', $styles );
		}

		if ( is_array( $args['data_attributes'] ) ) {
			foreach ( $args['data_attributes'] as $key => $value ) {
				$key = (string) $key;
				if ( 'data-' !== substr( $key, 0, 5 ) ) {
					$key = 'data-' . ltrim( $key, '-' );
				}
				$attributes[ $key ] = $value;
			}
		}
		?>
		<div <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php self::renderSlot( $before, $args, $uid, $image_url ); ?>
			<?php
			if ( is_callable( $preview ) ) {
				self::renderSlot( $preview, $args, $uid, $image_url );
			} else {
				$preview_markup = self::getDefaultMediaPreview( $args, $uid, $image_url );
				echo apply_filters( 'matterwp_admin_ui_media_field_preview', $preview_markup, $args, $uid, $image_url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
			<?php
			if ( is_callable( $actions ) ) {
				self::renderSlot( $actions, $args, $uid, $image_url );
			} else {
				$actions_markup = self::getDefaultMediaActions( $args, $uid, $attachment_id, $image_url );
				echo apply_filters( 'matterwp_admin_ui_media_field_actions', $actions_markup, $args, $uid, $attachment_id, (bool) $image_url, $image_url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
			<?php self::renderSlot( $after, $args, $uid, $image_url ); ?>
		</div>
		<?php
	}

	/**
	 * Render a media field slot.
	 *
	 * @param callable|string|null $slot Slot callback or safe markup.
	 * @param array                $args Media field arguments.
	 * @param string               $uid Media field unique identifier.
	 * @param string               $image_url Current image URL.
	 * @return void
	 */
	private static function renderSlot( $slot, array $args, string $uid, string $image_url ): void {
		if ( is_callable( $slot ) ) {
			$slot( $args, $uid, $image_url );
			return;
		}

		if ( is_string( $slot ) && '' !== $slot ) {
			echo wp_kses_post( $slot );
		}
	}

	/**
	 * Build the default media preview markup.
	 *
	 * @param array  $args Media field arguments.
	 * @param string $uid Media field unique identifier.
	 * @param string $image_url Current image URL.
	 * @return string Preview markup.
	 */
	private static function getDefaultMediaPreview( array $args, string $uid, string $image_url ): string {
		$classes = trim( 'mwp-media-field__preview ' . ( $image_url ? 'has-image ' : '' ) . $args['preview_class'] );
		ob_start();
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" data-media-preview="<?php echo esc_attr( $uid ); ?>">
			<?php if ( $image_url ) : ?>
				<img src="<?php echo esc_url( $image_url ); ?>" alt="">
			<?php endif; ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Build the default media action markup.
	 *
	 * @param array  $args Media field arguments.
	 * @param string $uid Media field unique identifier.
	 * @param int    $attachment_id Current attachment ID.
	 * @param string $image_url Current image URL.
	 * @return string Action markup.
	 */
	private static function getDefaultMediaActions( array $args, string $uid, int $attachment_id, string $image_url ): string {
		$has_image       = '' !== $image_url;
		$actions_classes = trim( 'mwp-media-field__actions ' . $args['actions_class'] );
		$input_classes   = trim( 'mwp-media-field__input ' . $args['input_class'] );
		$choose_classes  = trim( 'mwp-button is-secondary mwp-media-field__choose ' . $args['choose_class'] );
		$remove_classes  = trim( 'mwp-button is-danger mwp-media-field__remove ' . $args['remove_class'] );
		$choose_icon     = Components::iconMarkup( (string) $args['choose_icon'] );
		$remove_icon     = Components::iconMarkup( (string) $args['remove_icon'] );
		ob_start();
		?>
		<div class="<?php echo esc_attr( $actions_classes ); ?>">
			<input type="hidden" name="<?php echo esc_attr( $args['name'] ); ?>" value="<?php echo esc_attr( (string) $attachment_id ); ?>" data-media-input="<?php echo esc_attr( $uid ); ?>" data-autosave-hidden>
			<button class="<?php echo esc_attr( $choose_classes ); ?>" type="button" aria-label="<?php echo esc_attr( $args['button_text'] ); ?>" title="<?php echo esc_attr( $args['button_text'] ); ?>" data-media-target="<?php echo esc_attr( $uid ); ?>" data-media-library-type="<?php echo esc_attr( (string) $args['library_type'] ); ?>" data-media-preview-size="<?php echo esc_attr( (string) $args['preview_size'] ); ?>" data-media-title="<?php echo esc_attr( (string) $args['media_title'] ); ?>" data-media-button="<?php echo esc_attr( (string) $args['media_button'] ); ?>">
				<?php if ( '' !== $choose_icon ) : ?>
					<span class="mwp-button__icon" aria-hidden="true"><?php echo $choose_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<?php endif; ?>
				<span class="mwp-media-field__button-label"><?php echo esc_html( $args['button_text'] ); ?></span>
			</button>
			<button class="<?php echo esc_attr( $remove_classes ); ?>" type="button" aria-label="<?php echo esc_attr( $args['remove_text'] ); ?>" title="<?php echo esc_attr( $args['remove_text'] ); ?>" data-media-remove="<?php echo esc_attr( $uid ); ?>"<?php echo $has_image ? '' : ' hidden'; ?>>
				<?php if ( '' !== $remove_icon ) : ?>
					<span class="mwp-button__icon" aria-hidden="true"><?php echo $remove_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<?php endif; ?>
				<span class="mwp-media-field__button-label"><?php echo esc_html( $args['remove_text'] ); ?></span>
			</button>
		</div>
		<?php
		return (string) ob_get_clean();
	}
}
