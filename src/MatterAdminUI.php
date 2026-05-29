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
					<?php self::premiumBadge( $args['badge'] ); ?>
				<?php endif; ?>
				<?php $content(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a premium upsell badge bar.
	 *
	 * @param array $args Badge arguments.
	 * @return void
	 */
	public static function premiumBadge( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'label'      => __( 'Pro feature', 'boilerplate' ),
				'url'        => '',
				'link_label' => __( 'Upgrade', 'boilerplate' ),
			)
		);
		?>
		<div class="mwp-premium-badge">
			<span class="badge-label">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/></svg>
				<?php echo esc_html( $args['label'] ); ?>
			</span>
			<?php if ( '' !== $args['url'] ) : ?>
				<a href="<?php echo esc_url( $args['url'] ); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo esc_html( $args['link_label'] ); ?>
					<span class="mwp-upgrade-icon" aria-hidden="true">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
					</span>
				</a>
			<?php endif; ?>
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
				'name'        => '',
				'value'       => '',
				'placeholder' => '',
				'class'       => '',
				'disabled'    => false,
			)
		);

		$type = in_array( $args['type'], array( 'text', 'number', 'url', 'email', 'password', 'search', 'color' ), true ) ? $args['type'] : 'text';
		?>
		<input class="<?php echo esc_attr( $args['class'] ); ?>" <?php echo '' !== $args['name'] ? ' name="' . esc_attr( $args['name'] ) . '"' : ''; ?> type="<?php echo esc_attr( $type ); ?>" value="<?php echo esc_attr( $args['value'] ); ?>" placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" <?php disabled( $args['disabled'] ); ?>>
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
				'value'       => '',
				'placeholder' => '',
				'class'       => '',
				'rows'        => 4,
				'disabled'    => false,
			)
		);
		?>
		<textarea class="<?php echo esc_attr( $args['class'] ); ?>" <?php echo '' !== $args['name'] ? ' name="' . esc_attr( $args['name'] ) . '"' : ''; ?> rows="<?php echo esc_attr( (string) absint( $args['rows'] ) ); ?>" placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" <?php disabled( $args['disabled'] ); ?>><?php echo esc_textarea( $args['value'] ); ?></textarea>
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
				'name'     => '',
				'value'    => '',
				'options'  => array(),
				'class'    => '',
				'disabled' => false,
			)
		);
		?>
		<select class="<?php echo esc_attr( $args['class'] ); ?>" <?php echo '' !== $args['name'] ? ' name="' . esc_attr( $args['name'] ) . '"' : ''; ?> <?php disabled( $args['disabled'] ); ?>>
			<?php foreach ( $args['options'] as $value => $label ) : ?>
				<option value="<?php echo esc_attr( (string) $value ); ?>" <?php selected( (string) $args['value'], (string) $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Render a button.
	 *
	 * @param array $args Button arguments.
	 * @return void
	 */
	public static function button( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'label'    => '',
				'type'     => 'button',
				'variant'  => 'secondary',
				'class'    => '',
				'disabled' => false,
			)
		);

		$type    = in_array( $args['type'], array( 'button', 'submit', 'reset' ), true ) ? $args['type'] : 'button';
		$variant = in_array( $args['variant'], array( 'primary', 'secondary', 'ghost', 'danger' ), true ) ? $args['variant'] : 'secondary';
		$classes = trim( 'mwp-button is-' . $variant . ' ' . $args['class'] );
		?>
		<button class="<?php echo esc_attr( $classes ); ?>" type="<?php echo esc_attr( $type ); ?>" <?php disabled( $args['disabled'] ); ?>>
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

	/**
	 * Render a card.
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
	 * Render a table.
	 *
	 * @param array $args Table arguments.
	 * @return void
	 */
	public static function table( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'columns' => array(),
				'rows'    => array(),
				'class'   => '',
			)
		);

		$classes = trim( 'mwp-table-wrap ' . $args['class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<table class="mwp-table">
				<thead>
					<tr>
						<?php foreach ( $args['columns'] as $column_label ) : ?>
							<th scope="col"><?php echo esc_html( $column_label ); ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $args['rows'] as $row ) : ?>
						<tr>
							<?php foreach ( array_keys( $args['columns'] ) as $column_key ) : ?>
								<td><?php echo wp_kses_post( $row[ $column_key ] ?? '' ); ?></td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render a color picker control.
	 *
	 * @param array $args Color picker arguments.
	 * @return void
	 */
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

	/**
	 * Render an input with an attached button.
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
			self::input( $args['input'] );
			self::button( $args['button'] );
			?>
		</div>
		<?php
	}

	/**
	 * Render a compact form layout.
	 *
	 * This intentionally renders a grouped field container instead of a nested
	 * form element, so it can be used inside WordPress settings forms.
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
	 * Render a mini-labeled field wrapper.
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
	 * Render a modal with a button trigger.
	 *
	 * @param array    $args Modal arguments.
	 * @param callable $content Content callback.
	 * @return void
	 */
	public static function modal( array $args, callable $content ): void {
		$args = wp_parse_args(
			$args,
			array(
				'id'          => '',
				'title'       => '',
				'description' => '',
				'trigger'     => 'Open Modal',
				'class'       => '',
			)
		);

		$id      = '' !== $args['id'] ? $args['id'] : 'mwp-modal-' . wp_unique_id();
		$classes = trim( 'mwp-modal ' . $args['class'] );
		self::button(
			array(
				'label'   => $args['trigger'],
				'variant' => 'primary',
				'class'   => 'mwp-modal-trigger',
			)
		);
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $id ); ?>" data-mwp-modal hidden>
			<div class="mwp-modal__overlay" data-mwp-modal-close></div>
			<div class="mwp-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $id . '-title' ); ?>">
				<div class="mwp-modal__header">
					<div>
						<h4 id="<?php echo esc_attr( $id . '-title' ); ?>"><?php echo esc_html( $args['title'] ); ?></h4>
						<?php if ( '' !== $args['description'] ) : ?>
							<p><?php echo esc_html( $args['description'] ); ?></p>
						<?php endif; ?>
					</div>
					<button class="mwp-icon-button" type="button" aria-label="Close" data-mwp-modal-close><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
				</div>
				<div class="mwp-modal__content">
					<?php $content(); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a lightbox image.
	 *
	 * @param array $args Lightbox arguments.
	 * @return void
	 */
	public static function lightbox( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'src'     => '',
				'alt'     => '',
				'caption' => '',
				'class'   => '',
			)
		);

		$classes = trim( 'mwp-lightbox-trigger ' . $args['class'] );
		?>
		<button class="<?php echo esc_attr( $classes ); ?>" type="button" data-mwp-lightbox-trigger data-mwp-lightbox-src="<?php echo esc_url( $args['src'] ); ?>" data-mwp-lightbox-alt="<?php echo esc_attr( $args['alt'] ); ?>" data-mwp-lightbox-caption="<?php echo esc_attr( $args['caption'] ); ?>">
			<img src="<?php echo esc_url( $args['src'] ); ?>" alt="<?php echo esc_attr( $args['alt'] ); ?>">
		</button>
		<?php
	}

	/**
	 * Render an accordion.
	 *
	 * @param array $args Accordion arguments.
	 * @return void
	 */
	public static function accordion( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'items' => array(),
				'class' => '',
			)
		);

		$classes = trim( 'mwp-accordion ' . $args['class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" data-mwp-accordion>
			<?php foreach ( $args['items'] as $index => $item ) : ?>
				<?php $item_id = 'mwp-accordion-' . wp_unique_id() . '-' . absint( $index ); ?>
				<div class="mwp-accordion__item">
					<button class="mwp-accordion__trigger" type="button" aria-expanded="<?php echo ! empty( $item['open'] ) ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $item_id ); ?>" data-mwp-accordion-trigger>
						<span><?php echo esc_html( $item['title'] ?? '' ); ?></span>
						<span class="mwp-accordion__icon" aria-hidden="true">+</span>
					</button>
					<div class="mwp-accordion__content" id="<?php echo esc_attr( $item_id ); ?>" <?php echo empty( $item['open'] ) ? ' hidden' : ''; ?> data-mwp-accordion-content>
						<?php echo wp_kses_post( $item['content'] ?? '' ); ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render a button group.
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
				<?php self::button( $button ); ?>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render a segmented radio group.
	 *
	 * @param array $args Radio group arguments.
	 * @return void
	 */
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

	/**
	 * Render a media library field (image picker).
	 *
	 * @param array $args Media field arguments.
	 * @return void
	 */
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

	/**
	 * Output disabled and data-pro-locked attributes for a premium control.
	 *
	 * @param bool $locked Whether the control is locked.
	 * @return void
	 */
	public static function controlLockedAttrs( bool $locked ): void {
		if ( $locked ) {
			echo ' disabled data-pro-locked="true"';
		}
	}

	/**
	 * Render a compact stat card.
	 *
	 * @param array $args Stat card arguments.
	 * @return void
	 */
	public static function statCard( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'label' => '',
				'value' => '',
				'class' => '',
			)
		);

		$classes = trim( 'mwp-stat ' . $args['class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<span class="mwp-stat__label"><?php echo esc_html( $args['label'] ); ?></span>
			<strong class="mwp-stat__value"><?php echo esc_html( $args['value'] ); ?></strong>
		</div>
		<?php
	}

	/**
	 * Render a confirmation dialog (danger variant of modal).
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
				'description'     => '',
				'trigger'         => __( 'Delete', 'boilerplate' ),
				'trigger_variant' => 'danger',
				'confirm_label'   => __( 'Confirm', 'boilerplate' ),
				'confirm_variant' => 'danger',
				'cancel_label'    => __( 'Cancel', 'boilerplate' ),
				'class'           => '',
			)
		);

		$id              = '' !== $args['id'] ? $args['id'] : 'mwp-confirm-' . wp_unique_id();
		$trigger_variant = in_array( $args['trigger_variant'], array( 'primary', 'secondary', 'ghost', 'danger' ), true ) ? $args['trigger_variant'] : 'danger';
		$confirm_variant = in_array( $args['confirm_variant'], array( 'primary', 'secondary', 'ghost', 'danger' ), true ) ? $args['confirm_variant'] : 'danger';
		$classes         = trim( 'mwp-modal is-danger ' . $args['class'] );

		self::button(
			array(
				'label'   => $args['trigger'],
				'variant' => $trigger_variant,
				'class'   => 'mwp-modal-trigger',
			)
		);
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $id ); ?>" data-mwp-modal hidden>
			<div class="mwp-modal__overlay" data-mwp-modal-close></div>
			<div class="mwp-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $id . '-title' ); ?>">
				<div class="mwp-modal__header">
					<div>
						<h4 id="<?php echo esc_attr( $id . '-title' ); ?>"><?php echo esc_html( $args['title'] ); ?></h4>
						<?php if ( '' !== $args['description'] ) : ?>
							<p><?php echo esc_html( $args['description'] ); ?></p>
						<?php endif; ?>
					</div>
					<button class="mwp-icon-button" type="button" aria-label="Close" data-mwp-modal-close><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
				</div>
				<div class="mwp-modal__content">
					<div class="mwp-inline-stack mwp-confirm-actions">
						<?php
						self::button(
							array(
								'label'   => $args['cancel_label'],
								'variant' => 'ghost',
								'class'   => 'mwp-confirm-cancel',
							)
						);
						self::button(
							array(
								'label'   => $args['confirm_label'],
								'variant' => $confirm_variant,
								'class'   => 'mwp-confirm-ok',
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
	 * Render a paginated table.
	 *
	 * @param array $args Paginated table arguments.
	 * @return void
	 */
	public static function paginatedTable( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'columns'      => array(),
				'rows'         => array(),
				'per_page'     => 10,
				'current_page' => 1,
				'total'        => 0,
				'class'        => '',
			)
		);

		$total_pages  = max( 1, (int) ceil( $args['total'] / max( 1, $args['per_page'] ) ) );
		$current_page = max( 1, min( $total_pages, absint( $args['current_page'] ) ) );
		$prev_page    = max( 1, $current_page - 1 );
		$next_page    = min( $total_pages, $current_page + 1 );
		$classes      = trim( 'mwp-table-wrap ' . $args['class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" data-mwp-paginated-table data-total="<?php echo esc_attr( (string) $args['total'] ); ?>" data-per-page="<?php echo esc_attr( (string) $args['per_page'] ); ?>" data-current-page="<?php echo esc_attr( (string) $current_page ); ?>">
			<table class="mwp-table">
				<thead>
					<tr>
						<?php foreach ( $args['columns'] as $column_label ) : ?>
							<th scope="col"><?php echo esc_html( $column_label ); ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $args['rows'] as $row ) : ?>
						<tr>
							<?php foreach ( array_keys( $args['columns'] ) as $column_key ) : ?>
								<td><?php echo wp_kses_post( $row[ $column_key ] ?? '' ); ?></td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php if ( $total_pages > 1 ) : ?>
				<div class="mwp-pagination">
					<button class="mwp-button is-ghost" type="button" data-mwp-page="prev" <?php disabled( $current_page <= 1 ); ?>><?php esc_html_e( 'Previous', 'boilerplate' ); ?></button>
					<span class="mwp-pagination__info"><?php echo esc_html( sprintf( __( 'Page %d of %d', 'boilerplate' ), $current_page, $total_pages ) ); ?></span>
					<button class="mwp-button is-ghost" type="button" data-mwp-page="next" <?php disabled( $current_page >= $total_pages ); ?>><?php esc_html_e( 'Next', 'boilerplate' ); ?></button>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render an empty state placeholder.
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

	/**
	 * Render a progress bar.
	 *
	 * @param array $args Progress arguments.
	 * @return void
	 */
	public static function progress( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'value' => 0,
				'label' => '',
				'class' => '',
			)
		);

		$value   = max( 0, min( 100, absint( $args['value'] ) ) );
		$classes = trim( 'mwp-progress ' . $args['class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( (string) $value ); ?>">
			<?php if ( '' !== $args['label'] ) : ?>
				<div class="mwp-progress__header">
					<span><?php echo esc_html( $args['label'] ); ?></span>
					<strong><?php echo esc_html( (string) $value ); ?>%</strong>
				</div>
			<?php endif; ?>
			<div class="mwp-progress__track">
				<div class="mwp-progress__bar" style="width: <?php echo esc_attr( (string) $value ); ?>%;"></div>
			</div>
		</div>
		<?php
	}

}
