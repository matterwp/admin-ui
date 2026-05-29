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

class Components {

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
