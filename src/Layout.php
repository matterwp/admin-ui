<?php
/**
 * Layout rendering helpers: sections, options, cards, forms, fields.
 *
 * @package MTWP\ADMIN\V120
 */

namespace MTWP\ADMIN\V120;

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
	 * Render an admin app shell with header, navigation, optional form, and content.
	 *
	 * @param array    $args App shell arguments.
	 * @param callable $content Content callback.
	 * @return void
	 */
	public static function app( array $args, callable $content ): void {
		$args = wp_parse_args(
			$args,
			array(
				'id' => 'mwp-settings',
				'brand' => '',
				'logo' => '',
				'version' => '',
				'changelog_url' => '',
				'navigation' => array(),
				'form' => true,
				'form_attributes' => array(),
				'options_class' => '',
				'header_actions' => array(),
				'theme_toggle' => true,
				'layout' => 'standard',
				'full_width' => false,
				'class' => '',
				'attributes' => array(),
				'data_attributes' => array(),
			)
		);

		$layout = in_array( $args['layout'], array( 'standard', 'fullscreen' ), true ) ? $args['layout'] : 'standard';
		$classes = trim( 'mwp-admin-app is-layout-' . $layout . ' ' . ( $args['full_width'] ? 'is-full-width ' : '' ) . $args['class'] );
		$attributes = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), $classes, is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		$attributes['id'] = $args['id'];
		$form_attributes = is_array( $args['form_attributes'] ) ? $args['form_attributes'] : array();
		$form_attributes['class'] = trim( ( $form_attributes['class'] ?? '' ) . ' mwp-options-group mwp-settings-form' );
		$options_classes = trim( 'mwp-options ' . $args['options_class'] );
		$navigation = is_array( $args['navigation'] ) ? $args['navigation'] : array();
		?>
		<div <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<header>
				<div class="mwp-brand">
					<?php self::renderLogo( $args['logo'] ); ?>
					<?php if ( '' !== $args['brand'] ) : ?>
						<span class="mwp-title"><?php echo esc_html( $args['brand'] ); ?></span>
					<?php endif; ?>
				</div>
				<div class="mwp-header-actions">
					<?php if ( '' !== $args['version'] ) : ?>
						<?php if ( '' !== $args['changelog_url'] ) : ?>
							<a class="mwp-version" href="<?php echo esc_url( (string) $args['changelog_url'] ); ?>" target="_blank" rel="noopener noreferrer">
								<span><?php echo esc_html( $args['version'] ); ?></span>
								<span class="mwp-version-icon" aria-hidden="true"><?php echo Components::iconMarkup( 'arrow-up-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</a>
						<?php else : ?>
							<span class="mwp-version"><?php echo esc_html( $args['version'] ); ?></span>
						<?php endif; ?>
					<?php endif; ?>
					<?php foreach ( is_array( $args['header_actions'] ) ? $args['header_actions'] : array() as $action ) : ?>
						<?php Controls::button( is_array( $action ) ? $action : array() ); ?>
					<?php endforeach; ?>
					<?php if ( wp_validate_boolean( $args['theme_toggle'] ) ) : ?>
						<button class="mwp-theme-toggle" type="button" data-mwp-theme-toggle data-mwp-theme="light"
							aria-label="<?php esc_attr_e( 'Toggle theme', 'matterwp-admin-ui' ); ?>">
							<span class="mwp-theme-toggle-icon mwp-theme-toggle-icon-light"
								aria-hidden="true"><?php echo Components::iconMarkup( 'sun' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span class="mwp-theme-toggle-icon mwp-theme-toggle-icon-dark"
								aria-hidden="true"><?php echo Components::iconMarkup( 'moon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span class="mwp-theme-toggle-label mwp-theme-toggle-label-light"><?php esc_html_e( 'Light', 'matterwp-admin-ui' ); ?></span>
							<span class="mwp-theme-toggle-label mwp-theme-toggle-label-dark"><?php esc_html_e( 'Dark', 'matterwp-admin-ui' ); ?></span>
						</button>
					<?php endif; ?>
				</div>
			</header>
			<div class="<?php echo esc_attr( $options_classes ); ?>">
				<?php Navigation::render( $navigation ); ?>
				<?php if ( wp_validate_boolean( $args['form'] ) ) : ?>
					<form <?php echo Attrs::render( $form_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php $content(); ?>
					</form>
				<?php else : ?>
					<?php $content(); ?>
				<?php endif; ?>
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
		$has_section_variant = array_key_exists( 'section_variant', $args );
		$has_option_variant = array_key_exists( 'option_variant', $args );

		$args = wp_parse_args(
			$args,
			array(
				'title' => '',
				'description' => '',
				'badge' => null,
				'section_variant' => 'wrapped',
				'option_variant' => 'standard',
				'variant' => '',
				'option_box' => 'standard',
				'class' => '',
				'title_class' => '',
				'options_class' => '',
				'classes' => array(),
				'attributes' => array(),
				'data_attributes' => array(),
			)
		);

		if ( ! $has_section_variant && in_array( $args['variant'], array( 'borderless', 'table-only' ), true ) ) {
			$args['section_variant'] = 'minimal';
		}

		if ( ! $has_option_variant && in_array( $args['option_box'], array( 'minimal', 'standard' ), true ) ) {
			$args['option_variant'] = $args['option_box'];
		}

		$section_variant = in_array( $args['section_variant'], array( 'wrapped', 'minimal' ), true ) ? $args['section_variant'] : 'wrapped';
		$option_variant = in_array( $args['option_variant'], array( 'minimal', 'standard' ), true ) ? $args['option_variant'] : 'standard';
		$classes = trim( 'mwp-section-block is-section-' . $section_variant . ' is-option-variant-' . $option_variant . ' ' . ( null !== $args['badge'] ? 'mwp-section-premium ' : '' ) . $args['class'] );
		$title_classes = trim( 'mwp-section-title ' . Attrs::slotClass( $args, 'title', 'title_class' ) );
		$options_classes = trim( 'mwp-section-options ' . Attrs::slotClass( $args, 'options', 'options_class' ) );
		$title = self::isFalseFlag( $args['title'] ) ? '' : (string) $args['title'];
		$description = self::isFalseFlag( $args['description'] ) ? '' : (string) $args['description'];
		$attributes = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), $classes, is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
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
					<?php UI::premiumBadge( $args['badge'] ); ?>
				<?php endif; ?>
				<?php $content(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a generic grid wrapper.
	 *
	 * @param array    $args Grid args.
	 * @param callable $content Content callback.
	 * @return void
	 */
	public static function grid( array $args, callable $content ): void {
		$args = wp_parse_args(
			$args,
			array(
				'columns' => 'auto',
				'min_width' => '180px',
				'gap' => '',
				'row_gap' => '',
				'column_gap' => '',
				'density' => 'comfortable',
				'align_items' => 'stretch',
				'justify_items' => 'stretch',
				'class' => '',
				'attributes' => array(),
				'data_attributes' => array(),
			)
		);

		$columns = self::normalizeGridColumns( $args['columns'] );
		$density = in_array( $args['density'], array( 'compact', 'comfortable', 'spacious' ), true ) ? $args['density'] : 'comfortable';
		$align_items = in_array( $args['align_items'], array( 'start', 'center', 'end', 'stretch' ), true ) ? $args['align_items'] : 'stretch';
		$justify_items = in_array( $args['justify_items'], array( 'start', 'center', 'end', 'stretch' ), true ) ? $args['justify_items'] : 'stretch';
		$classes = trim( 'mwp-grid is-grid-' . $columns . ' is-density-' . $density . ' is-align-' . $align_items . ' is-justify-' . $justify_items . ' ' . $args['class'] );
		$attributes = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), $classes, is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		$styles = array();

		foreach ( array( 'min_width', 'gap', 'row_gap', 'column_gap' ) as $style_key ) {
			if ( '' === $args[ $style_key ] || null === $args[ $style_key ] || ! is_scalar( $args[ $style_key ] ) ) {
				continue;
			}

			$styles[] = '--mwp-grid-' . str_replace( '_', '-', $style_key ) . ': ' . esc_attr( (string) $args[ $style_key ] );
		}

		if ( ! empty( $styles ) ) {
			$attributes['style'] = trim( ( isset( $attributes['style'] ) ? (string) $attributes['style'] . '; ' : '' ) . implode( '; ', $styles ) );
		}
		?>
		<div <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php $content(); ?>
		</div>
		<?php
	}

	/**
	 * Render a section divider.
	 *
	 * @param array $args Divider args.
	 * @return void
	 */
	public static function divider( array $args = array() ): void {
		$args = wp_parse_args(
			$args,
			array(
				'height' => '1px',
				'margin' => '30px 0 0 0',
				'padding' => '0',
				'class' => '',
				'attributes' => array(),
				'data_attributes' => array(),
			)
		);

		$attributes = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), trim( 'mwp-divider ' . $args['class'] ), is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		$styles = array();

		foreach ( array( 'height', 'margin', 'padding' ) as $style_key ) {
			if ( '' === $args[ $style_key ] || null === $args[ $style_key ] || ! is_scalar( $args[ $style_key ] ) ) {
				continue;
			}

			$styles[] = '--mwp-divider-' . $style_key . ': ' . esc_attr( (string) $args[ $style_key ] );
		}

		if ( ! empty( $styles ) ) {
			$attributes['style'] = trim( ( isset( $attributes['style'] ) ? (string) $attributes['style'] . '; ' : '' ) . implode( '; ', $styles ) );
		}

		$attributes['role'] = $attributes['role'] ?? 'separator';
		?>
		<div <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>></div>
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
		$has_layout = array_key_exists( 'layout', $args ) && '' !== $args['layout'];
		$has_align = array_key_exists( 'align', $args ) && '' !== $args['align'];

		$args = wp_parse_args(
			$args,
			array(
				'title' => '',
				'description' => '',
				'count' => null,
				'class' => '',
				'wide' => false,
				'align_start' => false,
				'divider' => false,
				'input_label' => true,
				'input_id' => '',
				'label_width' => 'standard',
				'layout' => 'row',
				'style' => 'row',
				'control_width' => 'standard',
				'align' => '',
				'badge' => null,
				'help' => '',
				'actions' => array(),
				'info_class' => '',
				'title_class' => '',
				'description_class' => '',
				'count_class' => '',
				'input_class' => '',
				'help_class' => '',
				'actions_class' => '',
				'classes' => array(),
				'attributes' => array(),
				'data_attributes' => array(),
			)
		);

		$layout = $has_layout ? $args['layout'] : $args['style'];
		if ( $args['divider'] ) {
			$layout = 'divided';
		} elseif ( $args['wide'] ) {
			$layout = 'column';
		}

		$layout = in_array( $layout, array( 'divided', 'row', 'column' ), true ) ? $layout : 'row';
		$label_width = in_array( $args['label_width'], array( 'standard', 'full' ), true ) ? $args['label_width'] : 'standard';
		$control_width = in_array( $args['control_width'], array( 'narrow', 'standard', 'wide', 'full' ), true ) ? $args['control_width'] : 'standard';
		$align = $has_align ? $args['align'] : ( $args['align_start'] ? 'start' : 'center' );
		$align = in_array( $align, array( 'center', 'start', 'stretch' ), true ) ? $align : 'center';
		$input_label = wp_validate_boolean( $args['input_label'] );
		$title = self::isFalseFlag( $args['title'] ) ? '' : (string) $args['title'];
		$description = self::isFalseFlag( $args['description'] ) ? '' : (string) $args['description'];
		$has_info = $input_label && ( '' !== $title || '' !== $description || null !== $args['count'] || null !== $args['badge'] );
		$classes = trim( 'mwp-option is-layout-' . $layout . ' is-label-width-' . $label_width . ' is-control-width-' . $control_width . ' is-align-' . $align . ' ' . ( $input_label ? 'has-input-label ' : 'has-no-input-label ' ) . $args['class'] );
		$info_classes = trim( 'mwp-option-info ' . Attrs::slotClass( $args, 'info', 'info_class' ) );
		$title_classes = trim( 'mwp-option-title ' . Attrs::slotClass( $args, 'title', 'title_class' ) );
		$description_classes = trim( 'mwp-option-description ' . Attrs::slotClass( $args, 'description', 'description_class' ) );
		$count_classes = trim( 'mwp-option-count ' . Attrs::slotClass( $args, 'count', 'count_class' ) );
		$input_classes = trim( 'mwp-option-input ' . Attrs::slotClass( $args, 'input', 'input_class' ) );
		$help_classes = trim( 'mwp-option-help ' . Attrs::slotClass( $args, 'help', 'help_class' ) );
		$actions_classes = trim( 'mwp-option-actions ' . Attrs::slotClass( $args, 'actions', 'actions_class' ) );
		$attributes = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), $classes, is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		?>
		<div <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php if ( $has_info ) : ?>
				<div class="<?php echo esc_attr( $info_classes ); ?>">
					<?php if ( '' !== $title ) : ?>
						<?php if ( '' !== $args['input_id'] ) : ?>
							<label class="<?php echo esc_attr( $title_classes ); ?>" <?php echo '' !== $args['input_id'] ? ' for="' . esc_attr( $args['input_id'] ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></label>
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
		$name = (string) ( $overrides['name'] ?? ( $schema['name'] ?? ( $schema['key'] ?? '' ) ) );
		$id = (string) ( $overrides['id'] ?? ( '' !== $name ? str_replace( '_', '-', $name ) : wp_unique_id( 'mwp-field-' ) ) );
		$type = (string) ( $overrides['type'] ?? ( $schema['type'] ?? 'text' ) );
		$value = null !== $value ? $value : ( $schema['default'] ?? '' );
		$ui = is_array( $schema['ui'] ?? null ) ? $schema['ui'] : array();
		$options = array(
			'title' => $schema['label'] ?? ( $schema['title'] ?? '' ),
			'description' => $schema['description'] ?? '',
			'input_id' => $id,
			'layout' => $schema['layout'] ?? ( $ui['layout'] ?? '' ),
			'control_width' => $schema['control_width'] ?? ( $ui['control_width'] ?? 'standard' ),
			'help' => $schema['help'] ?? ( $ui['help'] ?? '' ),
			'badge' => $schema['badge'] ?? ( $ui['badge'] ?? null ),
		);

		foreach ( array( 'visible_if', 'disabled_if', 'requires' ) as $dependency_key ) {
			if ( ! isset( $schema[ $dependency_key ] ) ) {
				continue;
			}

			$options['data_attributes'] = is_array( $options['data_attributes'] ?? null ) ? $options['data_attributes'] : array();
			$options['data_attributes'][ 'mwp-' . str_replace( '_', '-', $dependency_key ) ] = wp_json_encode( $schema[ $dependency_key ] );
		}

		$options = array_merge( $options, is_array( $schema['option'] ?? null ) ? $schema['option'] : array() );
		$options = array_merge( $options, is_array( $ui['option'] ?? null ) ? $ui['option'] : array() );
		$options = array_merge( $options, is_array( $overrides['option'] ?? null ) ? $overrides['option'] : array() );

		self::option(
			$options,
			static function () use ($schema, $overrides, $name, $id, $type, $value): void {
				$ui = is_array( $schema['ui'] ?? null ) ? $schema['ui'] : array();
				$component = (string) ( $overrides['component'] ?? ( $schema['component'] ?? ( $ui['component'] ?? '' ) ) );
				$control_args = array_merge(
					array(
						'id' => $id,
						'name' => $name,
						'value' => $value,
						'placeholder' => $schema['placeholder'] ?? '',
						'disabled' => ! empty( $schema['disabled'] ),
						'required' => ! empty( $schema['required'] ),
						'readonly' => ! empty( $schema['readonly'] ),
						'min' => $schema['min'] ?? null,
						'max' => $schema['max'] ?? null,
						'step' => $schema['step'] ?? null,
						'autocomplete' => $schema['autocomplete'] ?? '',
					),
					is_array( $schema['control'] ?? null ) ? $schema['control'] : array(),
					is_array( $ui['control'] ?? null ) ? $ui['control'] : array(),
					is_array( $overrides['control'] ?? null ) ? $overrides['control'] : array()
				);

				if ( '' === $component ) {
					$component = $type;
				}

				if ( in_array( $component, array( 'color_picker', 'colorPicker' ), true ) ) {
					unset( $control_args['id'], $control_args['type'], $control_args['required'], $control_args['readonly'], $control_args['min'], $control_args['max'], $control_args['step'], $control_args['autocomplete'] );
					InputGroups::colorPicker( $control_args );
					return;
				}

				if ( in_array( $component, array( 'media', 'media_field', 'mediaField' ), true ) ) {
					$control_args = array_merge( is_array( $schema['media'] ?? null ) ? $schema['media'] : array(), $control_args );
					InputGroups::mediaField( $control_args );
					return;
				}

				if ( in_array( $type, array( 'boolean', 'bool', 'switch' ), true ) ) {
					$control_args['checked'] = wp_validate_boolean( $value );
					unset( $control_args['value'] );
					Controls::switch( $control_args );
					return;
				}

				if ( in_array( $type, array( 'multi_select', 'multi-switch', 'multi_switch' ), true ) ) {
					$control_args['value'] = is_array( $value ) ? $value : array_filter( array_map( 'trim', explode( ',', (string) $value ) ) );
					$control_args['options'] = $schema['options'] ?? ( $schema['choices'] ?? array() );
					$control_args['type'] = 'checkbox';
					Components::choiceGrid( $control_args );
					return;
				}

				if ( in_array( $type, array( 'select', 'choice' ), true ) ) {
					$control_args['options'] = $schema['options'] ?? ( $schema['choices'] ?? array() );
					if ( in_array( $schema['choices_display'] ?? '', array( 'grid', 'cards' ), true ) ) {
						Components::choiceGrid( $control_args );
						return;
					}
					Controls::select( $control_args );
					return;
				}

				if ( 'textarea' === $type ) {
					$control_args['rows'] = $schema['rows'] ?? 4;
					Controls::textarea( $control_args );
					return;
				}

				$control_args['type'] = Controls::normalizeInputType( $type );
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
				'title' => '',
				'description' => '',
				'class' => '',
				'header_class' => '',
				'content_class' => '',
			)
		);

		$classes = trim( 'mwp-card ' . $args['class'] );
		$header_classes = trim( 'mwp-card-header ' . $args['header_class'] );
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
				'title' => '',
				'description' => '',
				'class' => '',
				'header_class' => '',
				'fields_class' => '',
				'variant' => 'standard',
				'columns' => 2,
				'actions_align' => 'right',
				'fields' => array(),
				'actions' => array(),
				'classes' => array(),
				'attributes' => array(),
				'data_attributes' => array(),
			)
		);

		$variant = in_array( $args['variant'], array( 'standard', 'plain' ), true ) ? $args['variant'] : 'standard';
		$columns = in_array( $args['columns'], array( 1, 2, 3, '1', '2', '3', 'auto' ), true ) ? (string) $args['columns'] : '2';
		$actions_align = in_array( $args['actions_align'], array( 'left', 'right', 'between' ), true ) ? $args['actions_align'] : 'right';
		$classes = trim( 'mwp-form is-' . $variant . ' has-' . $columns . '-columns has-actions-' . $actions_align . ' ' . $args['class'] );
		$header_classes = trim( 'mwp-form-header ' . Attrs::slotClass( $args, 'header', 'header_class' ) );
		$fields_classes = trim( 'mwp-form-fields ' . Attrs::slotClass( $args, 'fields', 'fields_class' ) );
		$attributes = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), $classes, is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
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
				<?php foreach ( is_array( $args['fields'] ) ? $args['fields'] : array() as $field ) : ?>
					<?php self::generatedField( is_array( $field ) ? $field : array() ); ?>
				<?php endforeach; ?>
				<?php $content(); ?>
				<?php if ( ! empty( $args['actions'] ) && is_array( $args['actions'] ) ) : ?>
					<div class="mwp-form-actions">
						<?php foreach ( $args['actions'] as $action ) : ?>
							<?php Controls::button( is_array( $action ) ? $action : array() ); ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a labeled field wrapper.
	 *
	 * @param string   $label Field label.
	 * @param callable $control Control callback.
	 * @param array    $args Field arguments.
	 * @return void
	 */
	public static function field( string $label, callable $control, array $args = array() ): void {
		$args = wp_parse_args(
			$args,
			array(
				'class' => '',
				'label_class' => '',
				'wide' => false,
				'classes' => array(),
				'attributes' => array(),
				'data_attributes' => array(),
			)
		);

		$classes = trim( 'mwp-field ' . ( $args['wide'] ? 'is-wide mwp-field-grid__wide ' : '' ) . $args['class'] );
		$label_classes = trim( 'mwp-field-label ' . Attrs::slotClass( $args, 'label', 'label_class' ) );
		$attributes = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), $classes, is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		?>
		<label <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<span class="<?php echo esc_attr( $label_classes ); ?>"><?php echo esc_html( $label ); ?></span>
			<?php $control(); ?>
		</label>
		<?php
	}

	/**
	 * Render a field grid wrapper.
	 *
	 * @param array    $args Field grid args.
	 * @param callable $content Content callback.
	 * @return void
	 */
	public static function fieldGrid( array $args, callable $content ): void {
		$args = wp_parse_args(
			$args,
			array(
				'columns' => 2,
				'density' => 'comfortable',
				'class' => '',
				'attributes' => array(),
				'data_attributes' => array(),
			)
		);

		$columns = in_array( $args['columns'], array( 1, 2, 3, 4, '1', '2', '3', '4', 'auto' ), true ) ? (string) $args['columns'] : '2';
		$map = array(
			'1' => 'one',
			'2' => 'two',
			'3' => 'three',
			'4' => 'four',
			'auto' => 'auto',
		);
		$density = in_array( $args['density'], array( 'compact', 'comfortable' ), true ) ? $args['density'] : 'comfortable';
		$classes = trim( 'mwp-field-grid mwp-field-grid--' . $map[ $columns ] . ' mwp-field-grid--' . $density . ' ' . $args['class'] );
		$attributes = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), $classes, is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		?>
		<div <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php $content(); ?>
		</div>
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
	 * Normalize grid column args.
	 *
	 * @param mixed $columns Column count or mode.
	 * @return string
	 */
	private static function normalizeGridColumns( $columns ): string {
		if ( ! is_scalar( $columns ) ) {
			return 'auto';
		}

		if ( is_int( $columns ) || ctype_digit( (string) $columns ) ) {
			$count = max( 1, min( 6, (int) $columns ) );

			return (string) $count;
		}

		$columns = (string) $columns;

		return in_array( $columns, array( 'auto', 'fit', 'fill' ), true ) ? $columns : 'auto';
	}

	/**
	 * Render logo URL or SVG.
	 *
	 * @param mixed $logo Logo data.
	 * @return void
	 */
	private static function renderLogo( $logo ): void {
		if ( empty( $logo ) ) {
			return;
		}

		if ( is_string( $logo ) && false !== strpos( $logo, '<svg' ) ) {
			echo Components::iconMarkup( $logo ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}

		?>
		<img src="<?php echo esc_url( (string) $logo ); ?>" alt="">
		<?php
	}

	/**
	 * Render one generated form field.
	 *
	 * @param array<string, mixed> $field Field args.
	 * @return void
	 */
	private static function generatedField( array $field ): void {
		$label = (string) ( $field['label'] ?? ( $field['title'] ?? '' ) );

		self::field(
			$label,
			static function () use ($field): void {
				$type = (string) ( $field['type'] ?? 'text' );

				if ( in_array( $type, array( 'textarea' ), true ) ) {
					Controls::textarea( $field );
					return;
				}

				if ( in_array( $type, array( 'select', 'choice' ), true ) ) {
					Controls::select( $field );
					return;
				}

				if ( in_array( $type, array( 'switch', 'boolean', 'bool' ), true ) ) {
					Controls::switch( $field );
					return;
				}

				if ( in_array( $type, array( 'color_picker', 'colorPicker' ), true ) ) {
					InputGroups::colorPicker( $field );
					return;
				}

				if ( in_array( $type, array( 'media', 'media_field', 'mediaField' ), true ) ) {
					InputGroups::mediaField( $field );
					return;
				}

				$field['type'] = Controls::normalizeInputType( $type );
				Controls::input( $field );
			},
			$field
		);
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
