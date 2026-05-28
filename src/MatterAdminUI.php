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
					<button class="mwp-icon-button" type="button" aria-label="Close" data-mwp-modal-close>&times;</button>
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
	 * Render a highlighted code display area.
	 *
	 * @param array $args Code area arguments.
	 * @return void
	 */
	public static function codeArea( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'value'    => '',
				'language' => 'html',
				'label'    => '',
				'name'     => '',
				'rows'     => 8,
				'class'    => '',
			)
		);

		$language = in_array( $args['language'], array( 'html', 'css' ), true ) ? $args['language'] : 'html';
		$classes  = trim( 'mwp-code-area language-' . $language . ' ' . $args['class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<?php if ( '' !== $args['label'] ) : ?>
				<div class="mwp-code-area__label"><?php echo esc_html( $args['label'] ); ?></div>
			<?php endif; ?>
			<div class="mwp-code-area__editor" data-mwp-code-area data-mwp-code-language="<?php echo esc_attr( $language ); ?>">
				<pre aria-hidden="true"><code data-mwp-code-highlight><?php echo self::highlightCode( (string) $args['value'], $language ); ?></code></pre>
				<textarea <?php echo '' !== $args['name'] ? ' name="' . esc_attr( $args['name'] ) . '"' : ''; ?> rows="<?php echo esc_attr( (string) absint( $args['rows'] ) ); ?>" spellcheck="false" data-mwp-code-input><?php echo esc_textarea( $args['value'] ); ?></textarea>
			</div>
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

	/**
	 * Highlight code snippets for codeArea().
	 *
	 * @param string $code Raw code.
	 * @param string $language Language key.
	 * @return string
	 */
	private static function highlightCode( string $code, string $language ): string {
		if ( 'css' === $language ) {
			$escaped = esc_html( $code );
			$escaped = preg_replace( '/(\/\*.*?\*\/)/s', '<span class="token-comment">$1</span>', $escaped );
			$escaped = preg_replace( '/([{};])/', '<span class="token-punctuation">$1</span>', $escaped );
			$escaped = preg_replace( '/([a-zA-Z-]+)(\s*:)/', '<span class="token-property">$1</span><span class="token-punctuation">$2</span>', $escaped );
			$escaped = preg_replace( '/(:\s*)([^;{}]+)/', '$1<span class="token-value">$2</span>', $escaped );

			return $escaped;
		}

		$parts = preg_split( '/(&lt;.*?&gt;)/', esc_html( $code ), -1, PREG_SPLIT_DELIM_CAPTURE );
		$html  = '';

		foreach ( $parts as $part ) {
			if ( 0 === strpos( $part, '&lt;' ) && false !== strpos( $part, '&gt;' ) ) {
				if ( 0 === strpos( $part, '&lt;!--' ) ) {
					$html .= '<span class="token-comment">' . $part . '</span>';
					continue;
				}

				$part = preg_replace( '/(&lt;\/?)([a-zA-Z0-9:-]+)/', '<span class="token-punctuation">$1</span><span class="token-tag">$2</span>', $part );
				$part = preg_replace( '/([a-zA-Z0-9:-]+)(=)(&quot;.*?&quot;)/', '<span class="token-attr">$1</span><span class="token-punctuation">$2</span><span class="token-value">$3</span>', $part );
				$part = str_replace( '&gt;', '<span class="token-punctuation">&gt;</span>', $part );
			}

			$html .= $part;
		}

		return $html;
	}
}
