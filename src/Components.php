<?php
/**
 * Data display and misc component rendering helpers.
 *
 * @package MatterWP\AdminUI
 */

namespace MatterWP\AdminUI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data display and miscellaneous component rendering helpers.
 */
class Components {

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
				'columns'     => array(),
				'rows'        => array(),
				'class'       => '',
				'table_class' => '',
			)
		);

		$classes       = trim( 'mwp-table-wrap ' . $args['class'] );
		$table_classes = trim( 'mwp-table ' . $args['table_class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<table class="<?php echo esc_attr( $table_classes ); ?>">
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
								<td><?php echo self::ksesCell( $row[ $column_key ] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render a client-side paginated table.
	 *
	 * @param array $args Paginated table arguments.
	 * @return void
	 */
	public static function paginatedTable( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'columns'          => array(),
				'rows'             => array(),
				'per_page'         => 10,
				'current_page'     => 1,
				'total'            => 0,
				'class'            => '',
				'table_class'      => '',
				'pagination_class' => '',
				'prev_class'       => '',
				'next_class'       => '',
				'info_class'       => '',
			)
		);

		$total              = $args['total'] ? absint( $args['total'] ) : count( $args['rows'] );
		$total_pages        = max( 1, (int) ceil( $total / max( 1, absint( $args['per_page'] ) ) ) );
		$current_page       = max( 1, min( $total_pages, absint( $args['current_page'] ) ) );
		$classes            = trim( 'mwp-table-wrap ' . $args['class'] );
		$table_classes      = trim( 'mwp-table ' . $args['table_class'] );
		$pagination_classes = trim( 'mwp-pagination ' . $args['pagination_class'] );
		$prev_classes       = trim( 'mwp-button is-ghost ' . $args['prev_class'] );
		$next_classes       = trim( 'mwp-button is-ghost ' . $args['next_class'] );
		$info_classes       = trim( 'mwp-pagination__info ' . $args['info_class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" data-mwp-paginated-table data-per-page="<?php echo esc_attr( (string) max( 1, absint( $args['per_page'] ) ) ); ?>" data-current-page="<?php echo esc_attr( (string) $current_page ); ?>">
			<table class="<?php echo esc_attr( $table_classes ); ?>">
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
								<td><?php echo self::ksesCell( $row[ $column_key ] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<div class="<?php echo esc_attr( $pagination_classes ); ?>" data-mwp-pagination>
				<button class="<?php echo esc_attr( $prev_classes ); ?>" type="button" data-mwp-page="prev" <?php disabled( $current_page <= 1 ); ?>><?php esc_html_e( 'Previous', 'matterwp-admin-ui' ); ?></button>
				<span class="<?php echo esc_attr( $info_classes ); ?>"><?php echo esc_html( sprintf( __( 'Page %1$d of %2$d', 'matterwp-admin-ui' ), $current_page, $total_pages ) ); ?></span>
				<button class="<?php echo esc_attr( $next_classes ); ?>" type="button" data-mwp-page="next" <?php disabled( $current_page >= $total_pages ); ?>><?php esc_html_e( 'Next', 'matterwp-admin-ui' ); ?></button>
			</div>
		</div>
		<?php
	}

	/**
	 * Render compact action buttons for table action columns.
	 *
	 * @param array $args Action group arguments.
	 * @return void
	 */
	public static function actionGroup( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'actions' => array(),
				'class'   => '',
			)
		);

		$classes = trim( 'mwp-table-actions ' . $args['class'] );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<?php foreach ( $args['actions'] as $action ) : ?>
				<?php self::actionButton( is_array( $action ) ? $action : array() ); ?>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render one compact table action.
	 *
	 * @param array $args Action arguments.
	 * @return void
	 */
	private static function actionButton( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'label'           => '',
				'icon'            => '',
				'url'             => '',
				'variant'         => 'normal',
				'class'           => '',
				'type'            => 'button',
				'disabled'        => false,
				'data_attributes' => array(),
				'attributes'      => array(),
			)
		);

		$variant                  = in_array( $args['variant'], array( 'normal', 'danger' ), true ) ? $args['variant'] : 'normal';
		$classes                  = trim( 'mwp-table-action is-' . $variant . ' ' . $args['class'] );
		$attributes               = is_array( $args['attributes'] ) ? $args['attributes'] : array();
		$attributes['class']      = $classes;
		$attributes['aria-label'] = $args['label'];

		if ( is_array( $args['data_attributes'] ) ) {
			foreach ( $args['data_attributes'] as $key => $value ) {
				$key = (string) $key;
				$attributes[ 0 === strpos( $key, 'data-' ) ? $key : 'data-' . ltrim( $key, '-' ) ] = $value;
			}
		}

		$tag = '' !== $args['url'] ? 'a' : 'button';
		if ( 'a' === $tag ) {
			$attributes['href'] = $args['url'];
		} else {
			$attributes['type']     = in_array( $args['type'], array( 'button', 'submit', 'reset' ), true ) ? $args['type'] : 'button';
			$attributes['disabled'] = (bool) $args['disabled'];
		}
		?>
		<<?php echo tag_escape( $tag ); ?> <?php echo Attrs::render( $attributes ); ?>>
			<?php echo self::ksesCell( $args['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</<?php echo tag_escape( $tag ); ?>>
		<?php
	}

	/**
	 * Allow safe table cell markup, including inline lucide-style SVG icons.
	 *
	 * @param mixed $html Cell markup.
	 * @return string
	 */
	private static function ksesCell( $html ): string {
		$allowed = wp_kses_allowed_html( 'post' );

		$allowed['svg'] = array(
			'aria-hidden'     => true,
			'class'           => true,
			'fill'            => true,
			'height'          => true,
			'role'            => true,
			'stroke'          => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
			'stroke-width'    => true,
			'viewbox'         => true,
			'width'           => true,
			'xmlns'           => true,
		);

		$allowed['path'] = array(
			'd'               => true,
			'fill'            => true,
			'stroke'          => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
			'stroke-width'    => true,
		);

		return wp_kses( (string) $html, $allowed );
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
	 * Render a progress indicator.
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
	 * Render a statistic card.
	 *
	 * @param array $args Statistic card arguments.
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
	 * Render a lightbox trigger.
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
}
