<?php
/**
 * Form control rendering helpers: switch, input, textarea, select, button, badge, notice.
 *
 * @package MatterWP\AdminUI
 */

namespace MatterWP\AdminUI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form control rendering helpers.
 */
class Controls {

	/**
	 * Render a toggle switch control.
	 *
	 * @param array $args Switch arguments.
	 * @return void
	 */
	public static function switch( array $args ): void {
		$args           = wp_parse_args(
			$args,
			array(
				'id'           => '',
				'name'         => '',
				'value'        => null,
				'checked'      => false,
				'disabled'     => false,
				'class'        => '',
				'input_class'  => '',
				'slider_class' => '',
			)
		);
		$classes        = trim( 'mwp-switch ' . $args['class'] );
		$input_classes  = trim( $args['input_class'] );
		$slider_classes = trim( 'mwp-switch__slider ' . $args['slider_class'] );
		?>
		<label class="<?php echo esc_attr( $classes ); ?>">
			<input class="<?php echo esc_attr( $input_classes ); ?>" <?php echo '' !== $args['id'] ? ' id="' . esc_attr( $args['id'] ) . '"' : ''; ?> name="<?php echo esc_attr( $args['name'] ); ?>" type="checkbox" <?php echo null !== $args['value'] ? ' value="' . esc_attr( $args['value'] ) . '"' : ''; ?> <?php checked( $args['checked'] ); ?> <?php disabled( $args['disabled'] ); ?>>
			<span class="<?php echo esc_attr( $slider_classes ); ?>"></span>
		</label>
		<?php
	}

	/**
	 * Render an input control.
	 *
	 * @param array $args Input arguments.
	 * @return void
	 */
	public static function input( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'type'        => 'text',
				'id'          => '',
				'name'        => '',
				'value'       => '',
				'placeholder' => '',
				'class'       => '',
				'disabled'    => false,
			)
		);

		$type = in_array( $args['type'], array( 'text', 'number', 'url', 'email', 'password', 'search', 'color' ), true ) ? $args['type'] : 'text';
		?>
		<input class="<?php echo esc_attr( $args['class'] ); ?>" <?php echo '' !== $args['id'] ? ' id="' . esc_attr( $args['id'] ) . '"' : ''; ?> <?php echo '' !== $args['name'] ? ' name="' . esc_attr( $args['name'] ) . '"' : ''; ?> type="<?php echo esc_attr( $type ); ?>" value="<?php echo esc_attr( $args['value'] ); ?>" placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" <?php disabled( $args['disabled'] ); ?>>
		<?php
	}

	/**
	 * Render a textarea control.
	 *
	 * @param array $args Textarea arguments.
	 * @return void
	 */
	public static function textarea( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'name'        => '',
				'id'          => '',
				'value'       => '',
				'placeholder' => '',
				'class'       => '',
				'rows'        => 4,
				'disabled'    => false,
			)
		);
		?>
		<textarea class="<?php echo esc_attr( $args['class'] ); ?>" <?php echo '' !== $args['id'] ? ' id="' . esc_attr( $args['id'] ) . '"' : ''; ?> <?php echo '' !== $args['name'] ? ' name="' . esc_attr( $args['name'] ) . '"' : ''; ?> rows="<?php echo esc_attr( (string) absint( $args['rows'] ) ); ?>" placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" <?php disabled( $args['disabled'] ); ?>><?php echo esc_textarea( $args['value'] ); ?></textarea>
		<?php
	}

	/**
	 * Render a select control.
	 *
	 * @param array $args Select arguments.
	 * @return void
	 */
	public static function select( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'name'           => '',
				'id'             => '',
				'value'          => '',
				'options'        => array(),
				'class'          => '',
				'option_class'   => '',
				'option_classes' => array(),
				'disabled'       => false,
			)
		);
		?>
		<select class="<?php echo esc_attr( $args['class'] ); ?>" <?php echo '' !== $args['id'] ? ' id="' . esc_attr( $args['id'] ) . '"' : ''; ?> <?php echo '' !== $args['name'] ? ' name="' . esc_attr( $args['name'] ) . '"' : ''; ?> <?php disabled( $args['disabled'] ); ?>>
			<?php foreach ( $args['options'] as $value => $label ) : ?>
				<?php $option_classes = trim( $args['option_class'] . ' ' . ( is_array( $args['option_classes'] ) ? ( $args['option_classes'][ $value ] ?? '' ) : '' ) ); ?>
				<option class="<?php echo esc_attr( $option_classes ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" <?php selected( (string) $args['value'], (string) $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Render a button control.
	 *
	 * @param array $args Button arguments.
	 * @return void
	 */
	public static function button( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'label'      => '',
				'type'       => 'button',
				'variant'    => 'secondary',
				'class'      => '',
				'disabled'   => false,
				'attributes' => array(),
			)
		);

		$type    = in_array( $args['type'], array( 'button', 'submit', 'reset' ), true ) ? $args['type'] : 'button';
		$variant = in_array( $args['variant'], array( 'primary', 'secondary', 'ghost', 'danger' ), true ) ? $args['variant'] : 'secondary';
		$classes = trim( 'mwp-button is-' . $variant . ' ' . $args['class'] );

		$data_attrs = '';
		if ( ! empty( $args['data_attributes'] ) && is_array( $args['data_attributes'] ) ) {
			foreach ( $args['data_attributes'] as $key => $value ) {
				$data_attrs .= ' data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
			}
		}

		$key_attr = '';
		if ( ! empty( $args['key'] ) ) {
			$key_attr = ' key="' . esc_attr( $args['key'] ) . '"';
		}
		$attributes  = is_array( $args['attributes'] ) ? $args['attributes'] : array();
		$attr_string = Attrs::render( $attributes );
		?>
		<button class="<?php echo esc_attr( $classes ); ?>" type="<?php echo esc_attr( $type ); ?>" <?php disabled( $args['disabled'] ); ?><?php echo $data_attrs; ?><?php echo $key_attr; ?><?php echo '' !== $attr_string ? ' ' . $attr_string : ''; ?>>
			<?php echo esc_html( $args['label'] ); ?>
		</button>
		<?php
	}

	/**
	 * Render a badge.
	 *
	 * @param array $args Badge arguments.
	 * @return void
	 */
	public static function badge( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'label'   => '',
				'variant' => 'neutral',
				'class'   => '',
			)
		);

		$variant = in_array( $args['variant'], array( 'neutral', 'primary', 'warning', 'success' ), true ) ? $args['variant'] : 'neutral';
		$classes = trim( 'mwp-badge is-' . $variant . ' ' . $args['class'] );
		?>
		<span class="<?php echo esc_attr( $classes ); ?>"><?php echo esc_html( $args['label'] ); ?></span>
		<?php
	}

	/**
	 * Render an inline notice.
	 *
	 * @param array $args Notice arguments.
	 * @return void
	 */
	public static function notice( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'message' => '',
				'variant' => 'success',
				'class'   => '',
			)
		);

		$variant = in_array( $args['variant'], array( 'success', 'error', 'warning', 'info' ), true ) ? $args['variant'] : 'success';
		$classes = trim( 'mwp-inline-notice is-' . $variant . ' ' . $args['class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<span class="notice-text"><?php echo esc_html( $args['message'] ); ?></span>
		</div>
		<?php
	}
}
