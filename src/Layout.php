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
				'title'         => '',
				'description'   => '',
				'badge'         => null,
				'variant'       => '',
				'option_box'    => 'standard',
				'class'         => '',
				'title_class'   => '',
				'options_class' => '',
			)
		);

		$variant         = in_array( $args['variant'], array( 'borderless', 'table-only' ), true ) ? $args['variant'] : '';
		$option_box      = in_array( $args['option_box'], array( 'minimal', 'standard' ), true ) ? $args['option_box'] : 'standard';
		$variant_class   = '' !== $variant ? ' is-' . $variant : '';
		$classes         = trim( 'mwp-section-block' . $variant_class . ' is-option-box-' . $option_box . ' ' . $args['class'] );
		$title_classes   = trim( 'mwp-section-title ' . $args['title_class'] );
		$options_classes = trim( 'mwp-section-options ' . $args['options_class'] );
		$title           = self::isFalseFlag( $args['title'] ) ? '' : (string) $args['title'];
		$description     = self::isFalseFlag( $args['description'] ) ? '' : (string) $args['description'];
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
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
				'style'             => 'row',
				'info_class'        => '',
				'title_class'       => '',
				'description_class' => '',
				'count_class'       => '',
				'input_class'       => '',
			)
		);

		$style = in_array( $args['style'], array( 'divided', 'row', 'column' ), true ) ? $args['style'] : 'row';
		if ( $args['divider'] ) {
			$style = 'divided';
		} elseif ( $args['wide'] ) {
			$style = 'column';
		}

		$label_width         = in_array( $args['label_width'], array( 'standard', 'full' ), true ) ? $args['label_width'] : 'standard';
		$input_label         = wp_validate_boolean( $args['input_label'] );
		$title               = self::isFalseFlag( $args['title'] ) ? '' : (string) $args['title'];
		$description         = self::isFalseFlag( $args['description'] ) ? '' : (string) $args['description'];
		$has_info            = $input_label && ( '' !== $title || '' !== $description || null !== $args['count'] );
		$classes             = trim( 'mwp-option is-style-' . $style . ' is-label-width-' . $label_width . ' ' . ( $input_label ? 'has-input-label ' : 'has-no-input-label ' ) . ( $args['align_start'] ? 'is-align-start ' : '' ) . $args['class'] );
		$info_classes        = trim( 'mwp-option-info ' . $args['info_class'] );
		$title_classes       = trim( 'mwp-option-title ' . $args['title_class'] );
		$description_classes = trim( 'mwp-option-description ' . $args['description_class'] );
		$count_classes       = trim( 'mwp-option-count ' . $args['count_class'] );
		$input_classes       = trim( 'mwp-option-input ' . $args['input_class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<?php if ( $has_info ) : ?>
				<div class="<?php echo esc_attr( $info_classes ); ?>">
					<?php if ( '' !== $title ) : ?>
						<?php if ( '' !== $args['input_id'] ) : ?>
							<label class="<?php echo esc_attr( $title_classes ); ?>"<?php echo '' !== $args['input_id'] ? ' for="' . esc_attr( $args['input_id'] ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></label>
						<?php else : ?>
							<div class="<?php echo esc_attr( $title_classes ); ?>"><?php echo esc_html( $title ); ?></div>
						<?php endif; ?>
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
			</div>
		</div>
		<?php
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
				'title'        => '',
				'description'  => '',
				'class'        => '',
				'header_class' => '',
				'fields_class' => '',
			)
		);

		$classes        = trim( 'mwp-form ' . $args['class'] );
		$header_classes = trim( 'mwp-form-header ' . $args['header_class'] );
		$fields_classes = trim( 'mwp-form-fields ' . $args['fields_class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" role="group">
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
				'class'       => '',
				'label_class' => '',
			)
		);

		$classes       = trim( 'mwp-field ' . $args['class'] );
		$label_classes = trim( 'mwp-field-label ' . $args['label_class'] );
		?>
		<label class="<?php echo esc_attr( $classes ); ?>">
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
}
