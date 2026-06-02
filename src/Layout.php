<?php
/**
 * Layout rendering helpers: sections, options, cards, forms, fields.
 *
 * @package MatterWP\AdminUI
 */

namespace MatterWP\AdminUI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Layout rendering helpers for settings screens.
 */
class Layout {

	/**
	 * Render a tab panel wrapper.
	 *
	 * @param string   $id Panel identifier.
	 * @param callable $content Content callback.
	 * @return void
	 */
	public static function panel( string $id, callable $content ): void {
		?>
		<div class="mwp-option-group" data-ui-panel="<?php echo esc_attr( $id ); ?>">
			<div class="mwp-options-holder">
				<?php $content(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a settings section.
	 *
	 * @param array    $args Section arguments.
	 * @param callable $content Content callback.
	 * @return void
	 */
	public static function section( array $args, callable $content ): void {
		$args = wp_parse_args(
			$args,
			array(
				'title'           => '',
				'description'     => '',
				'badge'           => null,
				'variant'         => '',
				'option_box'      => 'standard',
				'class'           => '',
				'title_class'     => '',
				'options_class'   => '',
				'classes'         => array(),
				'attributes'      => array(),
				'data_attributes' => array(),
			)
		);

		$variant         = in_array( $args['variant'], array( 'borderless', 'table-only' ), true ) ? $args['variant'] : '';
		$option_box      = in_array( $args['option_box'], array( 'minimal', 'standard' ), true ) ? $args['option_box'] : 'standard';
		$variant_class   = '' !== $variant ? ' is-' . $variant : '';
		$classes         = trim( 'mwp-section-block' . $variant_class . ' is-option-box-' . $option_box . ' ' . $args['class'] );
		$title_classes   = trim( 'mwp-section-title ' . Attrs::slotClass( $args, 'title', 'title_class' ) );
		$options_classes = trim( 'mwp-section-options ' . Attrs::slotClass( $args, 'options', 'options_class' ) );
		$title           = self::isFalseFlag( $args['title'] ) ? '' : (string) $args['title'];
		$description     = self::isFalseFlag( $args['description'] ) ? '' : (string) $args['description'];
		$attributes      = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), $classes, is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		?>
		<div <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php if ( '' !== $title || '' !== $description ) : ?>
				<div class="<?php echo esc_attr( $title_classes ); ?>">
					<?php if ( '' !== $title ) : ?>
						<h3><?php echo esc_html( $title ); ?></h3>
					<?php endif; ?>
					<?php if ( '' !== $description ) : ?>
						<p><?php echo esc_html( $description ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="<?php echo esc_attr( $options_classes ); ?>">
				<?php if ( null !== $args['badge'] ) : ?>
					<?php MatterAdminUI::premiumBadge( $args['badge'] ); ?>
				<?php endif; ?>
				<?php $content(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a settings option row.
	 *
	 * @param array    $args Option arguments.
	 * @param callable $control Control callback.
	 * @return void
	 */
	public static function option( array $args, callable $control ): void {
		$args = wp_parse_args(
			$args,
			array(
				'title'             => '',
				'description'       => '',
				'count'             => null,
				'class'             => '',
				'wide'              => false,
				'align_start'       => false,
				'divider'           => false,
				'input_label'       => true,
				'input_id'          => '',
				'label_width'       => 'standard',
				'layout'            => '',
				'style'             => 'row',
				'control_width'     => 'standard',
				'align'             => '',
				'badge'             => null,
				'help'              => '',
				'actions'           => array(),
				'info_class'        => '',
				'title_class'       => '',
				'description_class' => '',
				'count_class'       => '',
				'input_class'       => '',
				'help_class'        => '',
				'actions_class'     => '',
				'classes'           => array(),
				'attributes'        => array(),
				'data_attributes'   => array(),
			)
		);

		$layout = '' !== $args['layout'] ? $args['layout'] : $args['style'];
		$style  = in_array( $layout, array( 'divided', 'row', 'column' ), true ) ? $layout : 'row';
		if ( $args['divider'] ) {
			$style = 'divided';
		} elseif ( $args['wide'] ) {
			$style = 'column';
		}

		$label_width         = in_array( $args['label_width'], array( 'standard', 'full' ), true ) ? $args['label_width'] : 'standard';
		$control_width       = in_array( $args['control_width'], array( 'narrow', 'standard', 'wide', 'full' ), true ) ? $args['control_width'] : 'standard';
		$align               = in_array( $args['align'], array( 'center', 'start', 'stretch' ), true ) ? $args['align'] : ( $args['align_start'] ? 'start' : 'center' );
		$input_label         = wp_validate_boolean( $args['input_label'] );
		$title               = self::isFalseFlag( $args['title'] ) ? '' : (string) $args['title'];
		$description         = self::isFalseFlag( $args['description'] ) ? '' : (string) $args['description'];
		$has_info            = $input_label && ( '' !== $title || '' !== $description || null !== $args['count'] || null !== $args['badge'] );
		$classes             = trim( 'mwp-option is-style-' . $style . ' is-layout-' . $style . ' is-label-width-' . $label_width . ' is-control-width-' . $control_width . ' is-align-' . $align . ' ' . ( $input_label ? 'has-input-label ' : 'has-no-input-label ' ) . $args['class'] );
		$info_classes        = trim( 'mwp-option-info ' . Attrs::slotClass( $args, 'info', 'info_class' ) );
		$title_classes       = trim( 'mwp-option-title ' . Attrs::slotClass( $args, 'title', 'title_class' ) );
		$description_classes = trim( 'mwp-option-description ' . Attrs::slotClass( $args, 'description', 'description_class' ) );
		$count_classes       = trim( 'mwp-option-count ' . Attrs::slotClass( $args, 'count', 'count_class' ) );
		$input_classes       = trim( 'mwp-option-input ' . Attrs::slotClass( $args, 'input', 'input_class' ) );
		$help_classes        = trim( 'mwp-option-help ' . Attrs::slotClass( $args, 'help', 'help_class' ) );
		$actions_classes     = trim( 'mwp-option-actions ' . Attrs::slotClass( $args, 'actions', 'actions_class' ) );
		$attributes          = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), $classes, is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		?>
		<div <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php if ( $has_info ) : ?>
				<div class="<?php echo esc_attr( $info_classes ); ?>">
					<?php if ( '' !== $title ) : ?>
						<?php if ( '' !== $args['input_id'] ) : ?>
							<label class="<?php echo esc_attr( $title_classes ); ?>"<?php echo '' !== $args['input_id'] ? ' for="' . esc_attr( $args['input_id'] ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></label>
						<?php else : ?>
							<div class="<?php echo esc_attr( $title_classes ); ?>"><?php echo esc_html( $title ); ?></div>
						<?php endif; ?>
					<?php endif; ?>
					<?php if ( null !== $args['badge'] ) : ?>
						<?php self::renderBadge( $args['badge'] ); ?>
					<?php endif; ?>
					<?php if ( '' !== $description ) : ?>
						<div class="<?php echo esc_attr( $description_classes ); ?>"><?php echo wp_kses_post( $description ); ?></div>
					<?php endif; ?>
					<?php if ( null !== $args['count'] ) : ?>
						<div class="<?php echo esc_attr( $count_classes ); ?>"><?php echo esc_html( $args['count'] ); ?></div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="<?php echo esc_attr( $input_classes ); ?>">
				<?php $control(); ?>
				<?php if ( ! empty( $args['actions'] ) && is_array( $args['actions'] ) ) : ?>
					<div class="<?php echo esc_attr( $actions_classes ); ?>">
						<?php foreach ( $args['actions'] as $action ) : ?>
							<?php Controls::button( is_array( $action ) ? $action : array() ); ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<?php if ( '' !== $args['help'] ) : ?>
					<div class="<?php echo esc_attr( $help_classes ); ?>"><?php echo wp_kses_post( $args['help'] ); ?></div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render an option row from a schema field definition.
	 *
	 * @param array<string, mixed> $schema Field schema.
	 * @param mixed                $value Current value.
	 * @param array<string, mixed> $overrides Option/control overrides.
	 * @return void
	 */
	public static function schemaOption( array $schema, $value = null, array $overrides = array() ): void {
		$name    = (string) ( $overrides['name'] ?? ( $schema['name'] ?? ( $schema['key'] ?? '' ) ) );
		$id      = (string) ( $overrides['id'] ?? ( '' !== $name ? str_replace( '_', '-', $name ) : wp_unique_id( 'mwp-field-' ) ) );
		$type    = (string) ( $overrides['type'] ?? ( $schema['type'] ?? 'text' ) );
		$value   = null !== $value ? $value : ( $schema['default'] ?? '' );
		$options = array(
			'title'       => $schema['label'] ?? ( $schema['title'] ?? '' ),
			'description' => $schema['description'] ?? '',
			'input_id'    => $id,
		);
		$options = array_merge( $options, is_array( $overrides['option'] ?? null ) ? $overrides['option'] : array() );

		self::option(
			$options,
			static function () use ( $schema, $overrides, $name, $id, $type, $value ): void {
				$control_args = array_merge(
					array(
						'id'           => $id,
						'name'         => $name,
						'value'        => $value,
						'disabled'     => ! empty( $schema['disabled'] ),
						'required'     => ! empty( $schema['required'] ),
						'readonly'     => ! empty( $schema['readonly'] ),
						'min'          => $schema['min'] ?? null,
						'max'          => $schema['max'] ?? null,
						'step'         => $schema['step'] ?? null,
						'autocomplete' => $schema['autocomplete'] ?? '',
					),
					is_array( $overrides['control'] ?? null ) ? $overrides['control'] : array()
				);

				if ( in_array( $type, array( 'boolean', 'bool', 'switch' ), true ) ) {
					$control_args['checked'] = wp_validate_boolean( $value );
					unset( $control_args['value'] );
					Controls::switch( $control_args );
					return;
				}

				if ( in_array( $type, array( 'select', 'choice' ), true ) ) {
					$control_args['options'] = $schema['options'] ?? ( $schema['choices'] ?? array() );
					Controls::select( $control_args );
					return;
				}

				if ( 'textarea' === $type ) {
					$control_args['rows'] = $schema['rows'] ?? 4;
					Controls::textarea( $control_args );
					return;
				}

				$control_args['type'] = in_array( $type, array( 'number', 'url', 'email', 'password', 'search', 'color', 'tel' ), true ) ? $type : 'text';
				Controls::input( $control_args );
			}
		);
	}

	/**
	 * Render a card container.
	 *
	 * @param array    $args Card arguments.
	 * @param callable $content Content callback.
	 * @return void
	 */
	public static function card( array $args, callable $content ): void {
		$args = wp_parse_args(
			$args,
			array(
				'title'         => '',
				'description'   => '',
				'class'         => '',
				'header_class'  => '',
				'content_class' => '',
			)
		);

		$classes         = trim( 'mwp-card ' . $args['class'] );
		$header_classes  = trim( 'mwp-card-header ' . $args['header_class'] );
		$content_classes = trim( 'mwp-card-content ' . $args['content_class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<?php if ( '' !== $args['title'] || '' !== $args['description'] ) : ?>
				<div class="<?php echo esc_attr( $header_classes ); ?>">
					<?php if ( '' !== $args['title'] ) : ?>
						<h4><?php echo esc_html( $args['title'] ); ?></h4>
					<?php endif; ?>
					<?php if ( '' !== $args['description'] ) : ?>
						<p><?php echo esc_html( $args['description'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="<?php echo esc_attr( $content_classes ); ?>">
				<?php $content(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a grouped form container.
	 *
	 * @param array    $args Form arguments.
	 * @param callable $content Content callback.
	 * @return void
	 */
	public static function form( array $args, callable $content ): void {
		$args = wp_parse_args(
			$args,
			array(
				'title'           => '',
				'description'     => '',
				'class'           => '',
				'header_class'    => '',
				'fields_class'    => '',
				'variant'         => 'standard',
				'columns'         => 2,
				'actions_align'   => 'right',
				'classes'         => array(),
				'attributes'      => array(),
				'data_attributes' => array(),
			)
		);

		$variant            = in_array( $args['variant'], array( 'standard', 'plain' ), true ) ? $args['variant'] : 'standard';
		$columns            = in_array( $args['columns'], array( 1, 2, 3, '1', '2', '3', 'auto' ), true ) ? (string) $args['columns'] : '2';
		$actions_align      = in_array( $args['actions_align'], array( 'left', 'right', 'between' ), true ) ? $args['actions_align'] : 'right';
		$classes            = trim( 'mwp-form is-' . $variant . ' has-' . $columns . '-columns has-actions-' . $actions_align . ' ' . $args['class'] );
		$header_classes     = trim( 'mwp-form-header ' . Attrs::slotClass( $args, 'header', 'header_class' ) );
		$fields_classes     = trim( 'mwp-form-fields ' . Attrs::slotClass( $args, 'fields', 'fields_class' ) );
		$attributes         = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), $classes, is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		$attributes['role'] = $attributes['role'] ?? 'group';
		?>
		<div <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php if ( '' !== $args['title'] || '' !== $args['description'] ) : ?>
				<div class="<?php echo esc_attr( $header_classes ); ?>">
					<?php if ( '' !== $args['title'] ) : ?>
						<h4><?php echo esc_html( $args['title'] ); ?></h4>
					<?php endif; ?>
					<?php if ( '' !== $args['description'] ) : ?>
						<p><?php echo esc_html( $args['description'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="<?php echo esc_attr( $fields_classes ); ?>">
				<?php $content(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a labeled field wrapper.
	 *
	 * @param string   $label Field label.
	 * @param callable $control Control callback.
	 * @return void
	 */
	public static function field( string $label, callable $control, array $args = array() ): void {
		$args = wp_parse_args(
			$args,
			array(
				'class'           => '',
				'label_class'     => '',
				'classes'         => array(),
				'attributes'      => array(),
				'data_attributes' => array(),
			)
		);

		$classes       = trim( 'mwp-field ' . $args['class'] );
		$label_classes = trim( 'mwp-field-label ' . Attrs::slotClass( $args, 'label', 'label_class' ) );
		$attributes    = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), $classes, is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		?>
		<label <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<span class="<?php echo esc_attr( $label_classes ); ?>"><?php echo esc_html( $label ); ?></span>
			<?php $control(); ?>
		</label>
		<?php
	}

	/**
	 * Check whether a mixed option value is an explicit false-like flag.
	 *
	 * @param mixed $value Value to check.
	 * @return bool
	 */
	private static function isFalseFlag( $value ): bool {
		return false === $value || 0 === $value || '0' === $value || 'false' === strtolower( trim( (string) $value ) );
	}

	/**
	 * Render option badge shorthand.
	 *
	 * @param mixed $badge Badge args or label.
	 * @return void
	 */
	private static function renderBadge( $badge ): void {
		$badge_args = is_array( $badge ) ? $badge : array( 'label' => (string) $badge );
		$badge_args = wp_parse_args(
			$badge_args,
			array(
				'size' => 'compact',
			)
		);

		Controls::badge( $badge_args );
	}
}
