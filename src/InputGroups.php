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

class InputGroups {

	public static function colorPicker( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'name'        => '',
				'value'       => '#059669',
				'label'       => '',
				'placeholder' => '#000000',
				'class'       => '',
				'disabled'    => false,
			)
		);

		$classes = trim( 'mwp-color-picker ' . $args['class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" data-mwp-color-picker>
			<input class="mwp-color-picker__swatch" type="color" value="<?php echo esc_attr( $args['value'] ); ?>" data-mwp-color-swatch <?php disabled( $args['disabled'] ); ?>>
			<input class="mwp-color-picker__input" <?php echo '' !== $args['name'] ? ' name="' . esc_attr( $args['name'] ) . '"' : ''; ?> type="text" value="<?php echo esc_attr( $args['value'] ); ?>" placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" data-mwp-color-input <?php disabled( $args['disabled'] ); ?>>
		</div>
		<?php
	}

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

	public static function radioGroup( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'name'    => '',
				'value'   => '',
				'options' => array(),
				'class'   => '',
			)
		);

		$has_name = '' !== $args['name'];
		$name     = $has_name ? $args['name'] : 'mwp-radio-' . wp_unique_id();
		$classes  = trim( 'mwp-radio-group ' . $args['class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" role="radiogroup">
			<?php foreach ( $args['options'] as $value => $label ) : ?>
				<label class="mwp-radio-group__item">
					<input name="<?php echo esc_attr( $name ); ?>" type="radio" value="<?php echo esc_attr( (string) $value ); ?>" <?php checked( (string) $args['value'], (string) $value ); ?> <?php echo $has_name ? '' : ' data-mwp-ignore-autosave="true"'; ?>>
					<span><?php echo esc_html( $label ); ?></span>
				</label>
			<?php endforeach; ?>
		</div>
		<?php
	}

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

	public static function mediaField( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'name'          => '',
				'value'         => 0,
				'preview_size'  => 'thumbnail',
				'button_text'   => __( 'Choose Image', 'boilerplate' ),
				'remove_text'   => __( 'Remove', 'boilerplate' ),
				'class'         => '',
			)
		);

		$attachment_id = absint( $args['value'] );
		$image_url     = $attachment_id ? wp_get_attachment_image_url( $attachment_id, $args['preview_size'] ) : '';
		$uid           = wp_unique_id( 'mwp-media-' );
		$classes       = trim( 'mwp-media-field ' . $args['class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<div class="mwp-media-field__preview" data-media-preview="<?php echo esc_attr( $uid ); ?>">
				<?php if ( $image_url ) : ?>
					<img src="<?php echo esc_url( $image_url ); ?>" alt="">
				<?php endif; ?>
			</div>
			<div class="mwp-media-field__actions">
				<input type="hidden" name="<?php echo esc_attr( $args['name'] ); ?>" value="<?php echo esc_attr( (string) $attachment_id ); ?>" data-media-input="<?php echo esc_attr( $uid ); ?>">
				<button class="mwp-button is-secondary" type="button" data-media-target="<?php echo esc_attr( $uid ); ?>">
					<?php echo esc_html( $args['button_text'] ); ?>
				</button>
				<button class="mwp-icon-button" type="button" data-media-remove="<?php echo esc_attr( $uid ); ?>" aria-label="<?php echo esc_attr( $args['remove_text'] ); ?>">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
				</button>
			</div>
		</div>
		<?php
	}
}
