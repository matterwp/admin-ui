<?php
/**
 * Admin UI rendering helpers.
 *
 * @package MatterWP\AdminUI
 */

namespace MatterWP\AdminUI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders reusable admin UI primitives.
 */
class MatterAdminUI {
	/**
	 * Render a tab panel wrapper.
	 *
	 * @param string   $id Panel ID.
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
	 * Render a reusable settings section.
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
				'input_class' => '',
			)
		);

		$classes       = trim( 'mwp-option ' . ( $args['wide'] ? 'full-width ' : '' ) . $args['class'] );
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
	 * Render a toggle switch.
	 *
	 * @param array $args Switch arguments.
	 * @return void
	 */
	public static function switch( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'name'     => '',
				'value'    => null,
				'checked'  => false,
				'disabled' => false,
			)
		);
		?>
		<label class="mwp-switch">
			<input name="<?php echo esc_attr( $args['name'] ); ?>" type="checkbox" <?php echo null !== $args['value'] ? ' value="' . esc_attr( $args['value'] ) . '"' : ''; ?> <?php checked( $args['checked'] ); ?> <?php disabled( $args['disabled'] ); ?>>
			<span class="mwp-switch__slider"></span>
		</label>
		<?php
	}
}
