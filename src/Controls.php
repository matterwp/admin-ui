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
	 * Return supported HTML input types.
	 *
	 * @return array<int, string>
	 */
	public static function inputTypes(): array {
		return array( 'text', 'number', 'url', 'email', 'password', 'search', 'color', 'tel', 'hidden', 'date', 'time', 'datetime-local', 'month', 'week' );
	}

	/**
	 * Normalize an HTML input type.
	 *
	 * @param string $type Requested input type.
	 * @return string
	 */
	public static function normalizeInputType( string $type ): string {
		return in_array( $type, self::inputTypes(), true ) ? $type : 'text';
	}

	/**
	 * Render a toggle switch control.
	 *
	 * @param array $args Switch arguments.
	 * @return void
	 */
	public static function switch( array $args ): void {
		$args                    = wp_parse_args(
			$args,
			array(
				'id'              => '',
				'name'            => '',
				'value'           => null,
				'checked'         => false,
				'disabled'        => false,
				'class'           => '',
				'input_class'     => '',
				'slider_class'    => '',
				'attributes'      => array(),
				'input_attrs'     => array(),
				'data_attributes' => array(),
			)
		);
		$classes                 = trim( 'mwp-switch ' . $args['class'] );
		$input_classes           = trim( $args['input_class'] );
		$slider_classes          = trim( 'mwp-switch__slider ' . $args['slider_class'] );
		$attributes              = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), $classes, is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		$input_attrs             = Attrs::merge( is_array( $args['input_attrs'] ) ? $args['input_attrs'] : array(), $input_classes );
		$input_attrs['id']       = '' !== $args['id'] ? $args['id'] : null;
		$input_attrs['name']     = '' !== $args['name'] ? $args['name'] : null;
		$input_attrs['type']     = 'checkbox';
		$input_attrs['value']    = null !== $args['value'] ? $args['value'] : null;
		$disabled                = wp_validate_boolean( $args['disabled'] );
		$input_attrs['checked']  = $disabled ? false : wp_validate_boolean( $args['checked'] );
		$input_attrs['disabled'] = $disabled;
		?>
		<label <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<input <?php echo Attrs::render( $input_attrs ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
				'type'            => 'text',
				'id'              => '',
				'name'            => '',
				'value'           => '',
				'placeholder'     => '',
				'class'           => '',
				'disabled'        => false,
				'readonly'        => false,
				'required'        => false,
				'min'             => null,
				'max'             => null,
				'step'            => null,
				'autocomplete'    => '',
				'attributes'      => array(),
				'data_attributes' => array(),
			)
		);

		$type                       = self::normalizeInputType( (string) $args['type'] );
		$attributes                 = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), (string) $args['class'], is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		$attributes['id']           = '' !== $args['id'] ? $args['id'] : null;
		$attributes['name']         = '' !== $args['name'] ? $args['name'] : null;
		$attributes['type']         = $type;
		$attributes['value']        = $args['value'];
		$attributes['placeholder']  = '' !== $args['placeholder'] ? $args['placeholder'] : null;
		$attributes['disabled']     = wp_validate_boolean( $args['disabled'] );
		$attributes['readonly']     = wp_validate_boolean( $args['readonly'] );
		$attributes['required']     = wp_validate_boolean( $args['required'] );
		$attributes['min']          = null !== $args['min'] ? $args['min'] : null;
		$attributes['max']          = null !== $args['max'] ? $args['max'] : null;
		$attributes['step']         = null !== $args['step'] ? $args['step'] : null;
		$attributes['autocomplete'] = '' !== $args['autocomplete'] ? $args['autocomplete'] : null;
		?>
		<input <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php
	}

	/**
	 * Render a textarea control.
	 *
	 * @param array $args Textarea arguments.
	 * @return void
	 */
	public static function textarea( array $args ): void {
		$args                       = wp_parse_args(
			$args,
			array(
				'name'            => '',
				'id'              => '',
				'value'           => '',
				'placeholder'     => '',
				'class'           => '',
				'rows'            => 4,
				'disabled'        => false,
				'readonly'        => false,
				'required'        => false,
				'autocomplete'    => '',
				'attributes'      => array(),
				'data_attributes' => array(),
			)
		);
		$attributes                 = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), (string) $args['class'], is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		$attributes['id']           = '' !== $args['id'] ? $args['id'] : null;
		$attributes['name']         = '' !== $args['name'] ? $args['name'] : null;
		$attributes['rows']         = max( 1, absint( $args['rows'] ) );
		$attributes['placeholder']  = '' !== $args['placeholder'] ? $args['placeholder'] : null;
		$attributes['disabled']     = wp_validate_boolean( $args['disabled'] );
		$attributes['readonly']     = wp_validate_boolean( $args['readonly'] );
		$attributes['required']     = wp_validate_boolean( $args['required'] );
		$attributes['autocomplete'] = '' !== $args['autocomplete'] ? $args['autocomplete'] : null;
		?>
		<textarea <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_textarea( $args['value'] ); ?></textarea>
		<?php
	}

	/**
	 * Render a select control.
	 *
	 * @param array $args Select arguments.
	 * @return void
	 */
	public static function select( array $args ): void {
		$args                   = wp_parse_args(
			$args,
			array(
				'name'            => '',
				'id'              => '',
				'value'           => '',
				'options'         => array(),
				'class'           => '',
				'option_class'    => '',
				'option_classes'  => array(),
				'disabled'        => false,
				'required'        => false,
				'attributes'      => array(),
				'data_attributes' => array(),
			)
		);
		$attributes             = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), (string) $args['class'], is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		$attributes['id']       = '' !== $args['id'] ? $args['id'] : null;
		$attributes['name']     = '' !== $args['name'] ? $args['name'] : null;
		$attributes['disabled'] = wp_validate_boolean( $args['disabled'] );
		$attributes['required'] = wp_validate_boolean( $args['required'] );
		?>
		<select <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
				'label'           => '',
				'type'            => 'button',
				'variant'         => 'secondary',
				'size'            => 'standard',
				'icon'            => '',
				'icon_position'   => 'before',
				'class'           => '',
				'disabled'        => false,
				'loading'         => false,
				'full_width'      => false,
				'attributes'      => array(),
				'data_attributes' => array(),
			)
		);

		$type                           = in_array( $args['type'], array( 'button', 'submit', 'reset' ), true ) ? $args['type'] : 'button';
		$variant                        = in_array( $args['variant'], array( 'primary', 'secondary', 'ghost', 'danger' ), true ) ? $args['variant'] : 'secondary';
		$size                           = in_array( $args['size'], array( 'standard', 'compact' ), true ) ? $args['size'] : 'standard';
		$icon_position                  = in_array( $args['icon_position'], array( 'before', 'after' ), true ) ? $args['icon_position'] : 'before';
		$icon                           = Components::iconMarkup( (string) $args['icon'] );
		$classes                        = trim( 'mwp-button is-' . $variant . ' ' . ( '' !== $icon ? 'has-icon is-icon-' . $icon_position . ' ' : '' ) . ( 'compact' === $size ? 'is-compact ' : '' ) . ( wp_validate_boolean( $args['full_width'] ) ? 'is-full-width ' : '' ) . ( $args['loading'] ? 'is-loading ' : '' ) . $args['class'] );
		$attributes                     = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), $classes, is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		$attributes['type']             = $type;
		$attributes['disabled']         = wp_validate_boolean( $args['disabled'] ) || wp_validate_boolean( $args['loading'] );
		$attributes['key']              = ! empty( $args['key'] ) ? $args['key'] : null;
		$attributes['data-mwp-loading'] = wp_validate_boolean( $args['loading'] ) ? 'true' : null;
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
	 * Render a badge.
	 *
	 * @param array $args Badge arguments.
	 * @return void
	 */
	public static function badge( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'label'           => '',
				'variant'         => 'neutral',
				'size'            => 'standard',
				'class'           => '',
				'attributes'      => array(),
				'data_attributes' => array(),
			)
		);

		$variant    = in_array( $args['variant'], array( 'neutral', 'primary', 'warning', 'success' ), true ) ? $args['variant'] : 'neutral';
		$size       = in_array( $args['size'], array( 'standard', 'compact' ), true ) ? $args['size'] : 'standard';
		$classes    = trim( 'mwp-badge is-' . $variant . ' ' . ( 'compact' === $size ? 'is-compact ' : '' ) . $args['class'] );
		$attributes = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), $classes, is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		?>
		<span <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $args['label'] ); ?></span>
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
				'message'         => '',
				'variant'         => 'success',
				'class'           => '',
				'attributes'      => array(),
				'data_attributes' => array(),
			)
		);

		$variant    = in_array( $args['variant'], array( 'success', 'error', 'warning', 'info' ), true ) ? $args['variant'] : 'success';
		$classes    = trim( 'mwp-inline-notice is-' . $variant . ' ' . $args['class'] );
		$attributes = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), $classes, is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		?>
		<div <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<span class="notice-text"><?php echo esc_html( $args['message'] ); ?></span>
		</div>
		<?php
	}
}
