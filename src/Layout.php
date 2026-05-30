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
				'title'       => '',
				'description' => '',
				'badge'       => null,
				'class'       => '',
			)
		);

		$classes = trim( 'mwp-section-block ' . $args['class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<?php if ( '' !== $args['title'] || '' !== $args['description'] ) : ?>
				<div class="mwp-section-title">
					<?php if ( '' !== $args['title'] ) : ?>
						<h3><?php echo esc_html( $args['title'] ); ?></h3>
					<?php endif; ?>
					<?php if ( '' !== $args['description'] ) : ?>
						<p><?php echo esc_html( $args['description'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="mwp-section-options">
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
				'title'       => '',
				'description' => '',
				'count'       => null,
				'class'       => '',
				'wide'        => false,
				'align_start' => false,
				'input_class' => '',
			)
		);

		$classes       = trim( 'mwp-option ' . ( $args['wide'] ? 'full-width ' : '' ) . ( $args['align_start'] ? 'is-align-start ' : '' ) . $args['class'] );
		$input_classes = trim( 'mwp-option-input ' . $args['input_class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<div class="mwp-option-info">
				<div class="mwp-option-title"><?php echo esc_html( $args['title'] ); ?></div>
				<?php if ( '' !== $args['description'] ) : ?>
					<div class="mwp-option-description"><?php echo wp_kses_post( $args['description'] ); ?></div>
				<?php endif; ?>
				<?php if ( null !== $args['count'] ) : ?>
					<div class="mwp-option-count"><?php echo esc_html( $args['count'] ); ?></div>
				<?php endif; ?>
			</div>
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
				'title'       => '',
				'description' => '',
				'class'       => '',
			)
		);

		$classes = trim( 'mwp-card ' . $args['class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<?php if ( '' !== $args['title'] || '' !== $args['description'] ) : ?>
				<div class="mwp-card-header">
					<?php if ( '' !== $args['title'] ) : ?>
						<h4><?php echo esc_html( $args['title'] ); ?></h4>
					<?php endif; ?>
					<?php if ( '' !== $args['description'] ) : ?>
						<p><?php echo esc_html( $args['description'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="mwp-card-content">
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
				'title'       => '',
				'description' => '',
				'class'       => '',
			)
		);

		$classes = trim( 'mwp-form ' . $args['class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" role="group">
			<?php if ( '' !== $args['title'] || '' !== $args['description'] ) : ?>
				<div class="mwp-form-header">
					<?php if ( '' !== $args['title'] ) : ?>
						<h4><?php echo esc_html( $args['title'] ); ?></h4>
					<?php endif; ?>
					<?php if ( '' !== $args['description'] ) : ?>
						<p><?php echo esc_html( $args['description'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="mwp-form-fields">
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
	public static function field( string $label, callable $control ): void {
		?>
		<label class="mwp-field">
			<span class="mwp-field-label"><?php echo esc_html( $label ); ?></span>
			<?php $control(); ?>
		</label>
		<?php
	}

	/**
	 * Render an empty state.
	 *
	 * @param array $args Empty state arguments.
	 * @return void
	 */
	public static function emptyState( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'title'       => '',
				'description' => '',
				'action'      => '',
				'class'       => '',
			)
		);

		$classes = trim( 'mwp-empty-state ' . $args['class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<?php if ( '' !== $args['title'] ) : ?>
				<h2><?php echo esc_html( $args['title'] ); ?></h2>
			<?php endif; ?>
			<?php if ( '' !== $args['description'] ) : ?>
				<p><?php echo wp_kses_post( $args['description'] ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== $args['action'] ) : ?>
				<div class="mwp-empty-state__action">
					<?php echo wp_kses_post( $args['action'] ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
