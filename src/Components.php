<?php
/**
 * Data display and misc component rendering helpers.
 *
 * @package MTWP\ADMIN\V130
 */

namespace MTWP\ADMIN\V130;

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
				'columns'             => array(),
				'rows'                => array(),
				'filters'             => array(),
				'filter_mode'         => 'client',
				'search'              => false,
				'orderby'             => '',
				'order'               => 'asc',
				'empty_selector'      => '',
				'empty_target'        => '',
				'empty_state'         => null,
				'class'               => '',
				'table_class'         => '',
				'classes'             => array(),
				'attributes'          => array(),
				'table_attributes'    => array(),
				'data_attributes'     => array(),
				'row_id'              => '',
				'row_key'             => '',
				'row_class'           => '',
				'row_attributes'      => array(),
				'row_data_attributes' => array(),
			)
		);

		$columns            = self::normalizeColumns( $args['columns'] );
		$filter_definitions = self::normalizeTableFilters( is_array( $args['filters'] ) ? $args['filters'] : array() );
		$has_search         = ! empty( self::normalizeTableSearch( $args['search'] ) );
		$has_sorting        = ! empty( array_filter( $columns, static fn( array $column ): bool => ! empty( $column['sortable'] ) ) );
		$has_behavior       = ! empty( $filter_definitions ) || $has_search || $has_sorting;
		$classes            = trim( 'mwp-table-wrap ' . $args['class'] );
		$table_classes      = trim( 'mwp-table ' . Attrs::slotClass( $args, 'table', 'table_class' ) );
		$empty_id           = '';
		$data_attributes    = is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array();

		if ( is_array( $args['empty_state'] ) && '' === $args['empty_target'] && '' === $args['empty_selector'] ) {
			$empty_id             = 'mwp-empty-' . wp_unique_id();
			$args['empty_target'] = $empty_id;
		}

		if ( $has_behavior ) {
			$data_attributes['mwp-paginated-table'] = '';
			$data_attributes['pagination-mode']     = 'client';
			$data_attributes['filter-mode']         = 'client';
			$data_attributes['per-page']            = max( 1, count( $args['rows'] ) );
			$data_attributes['current-page']        = 1;
			$data_attributes['total']               = count( $args['rows'] );
			$data_attributes['total-pages']         = 1;
			$data_attributes['empty-selector']      = '' !== $args['empty_selector'] ? $args['empty_selector'] : null;
			$data_attributes['empty-target']        = '' !== $args['empty_target'] ? $args['empty_target'] : null;
			$data_attributes['mwp-orderby']         = '' !== $args['orderby'] ? sanitize_key( (string) $args['orderby'] ) : null;
			$data_attributes['mwp-order']           = 'desc' === strtolower( (string) $args['order'] ) ? 'desc' : 'asc';
		}

		$attributes       = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), $classes, $data_attributes );
		$table_attributes = Attrs::merge( is_array( $args['table_attributes'] ) ? $args['table_attributes'] : array(), $table_classes );
		?>
		<div <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<table <?php echo Attrs::render( $table_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<thead>
					<tr>
						<?php foreach ( $columns as $column ) : ?>
							<?php echo self::renderHeaderCell( $column, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php echo self::dataTableRows( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</tbody>
			</table>
			<?php if ( is_array( $args['empty_state'] ) ) : ?>
				<?php
				$empty_state = $args['empty_state'];
				if ( '' !== $empty_id ) {
					$empty_state['attributes']       = is_array( $empty_state['attributes'] ?? null ) ? $empty_state['attributes'] : array();
					$empty_state['attributes']['id'] = $empty_id;
				}
				$empty_state['attributes']           = is_array( $empty_state['attributes'] ?? null ) ? $empty_state['attributes'] : array();
				$empty_state['attributes']['hidden'] = count( $args['rows'] ) > 0;
				self::emptyState( $empty_state );
				?>
			<?php endif; ?>
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
				'columns'             => array(),
				'rows'                => array(),
				'pagination_mode'     => 'client',
				'per_page'            => 10,
				'current_page'        => 1,
				'total'               => 0,
				'total_pages'         => 0,
				'endpoint'            => '',
				'action'              => '',
				'nonce'               => '',
				'filters'             => array(),
				'filter_params'       => array(),
				'filter_mode'         => '',
				'search'              => '',
				'orderby'             => '',
				'order'               => 'asc',
				'loading_label'       => __( 'Loading rows...', 'matterwp-admin-ui' ),
				'error_label'         => __( 'Unable to load table rows.', 'matterwp-admin-ui' ),
				'class'               => '',
				'table_class'         => '',
				'pagination_class'    => '',
				'prev_class'          => '',
				'next_class'          => '',
				'info_class'          => '',
				'empty_selector'      => '',
				'empty_target'        => '',
				'initial_page'        => '',
				'classes'             => array(),
				'attributes'          => array(),
				'table_attributes'    => array(),
				'data_attributes'     => array(),
				'empty_state'         => null,
				'pagination'          => true,
				'row_id'              => '',
				'row_key'             => '',
				'row_class'           => '',
				'row_attributes'      => array(),
				'row_data_attributes' => array(),
			)
		);

		$columns            = self::normalizeColumns( $args['columns'] );
		$pagination_mode    = 'server' === $args['pagination_mode'] ? 'server' : 'client';
		$filter_mode        = in_array( $args['filter_mode'], array( 'client', 'server' ), true ) ? $args['filter_mode'] : $pagination_mode;
		$filter_definitions = self::normalizeTableFilters( is_array( $args['filters'] ) ? $args['filters'] : array() );
		$legacy_filters     = empty( $filter_definitions ) && is_array( $args['filters'] ) ? $args['filters'] : array();
		$filter_params      = array_merge( $legacy_filters, is_array( $args['filter_params'] ) ? $args['filter_params'] : array() );
		$per_page           = max( 1, absint( $args['per_page'] ) );
		$total              = $args['total'] ? absint( $args['total'] ) : count( $args['rows'] );
		$total_pages        = $args['total_pages'] ? absint( $args['total_pages'] ) : (int) ceil( $total / $per_page );
		$total_pages        = max( 1, $total_pages );
		$current_page       = self::resolveInitialPage( $args['initial_page'], $args['current_page'], $total_pages );
		$classes            = trim( 'mwp-table-wrap ' . $args['class'] );
		$table_classes      = trim( 'mwp-table ' . Attrs::slotClass( $args, 'table', 'table_class' ) );
		$pagination_classes = trim( 'mwp-pagination ' . Attrs::slotClass( $args, 'pagination', 'pagination_class' ) );
		$empty_id           = '';
		$prev_classes       = trim( 'mwp-button is-ghost ' . Attrs::slotClass( $args, 'prev', 'prev_class' ) );
		$next_classes       = trim( 'mwp-button is-ghost ' . Attrs::slotClass( $args, 'next', 'next_class' ) );
		$info_classes       = trim( 'mwp-pagination__info ' . Attrs::slotClass( $args, 'info', 'info_class' ) );

		if ( is_array( $args['empty_state'] ) && '' === $args['empty_target'] && '' === $args['empty_selector'] ) {
			$empty_id             = 'mwp-empty-' . wp_unique_id();
			$args['empty_target'] = $empty_id;
		}

		$data_attributes                        = is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array();
		$data_attributes['mwp-paginated-table'] = '';
		$data_attributes['pagination-mode']     = $pagination_mode;
		$data_attributes['filter-mode']         = $filter_mode;
		$data_attributes['per-page']            = $per_page;
		$data_attributes['current-page']        = $current_page;
		$data_attributes['total']               = $total;
		$data_attributes['total-pages']         = $total_pages;
		$data_attributes['initial-page']        = '' !== $args['initial_page'] ? $args['initial_page'] : $current_page;
		$data_attributes['empty-selector']      = '' !== $args['empty_selector'] ? $args['empty_selector'] : null;
		$data_attributes['empty-target']        = '' !== $args['empty_target'] ? $args['empty_target'] : null;
		$data_attributes['mwp-endpoint']        = '' !== $args['endpoint'] ? $args['endpoint'] : null;
		$data_attributes['mwp-action']          = '' !== $args['action'] ? $args['action'] : null;
		$data_attributes['mwp-nonce']           = '' !== $args['nonce'] ? $args['nonce'] : null;
		$data_attributes['mwp-search']          = is_scalar( $args['search'] ) && ! is_bool( $args['search'] ) ? (string) $args['search'] : null;
		$data_attributes['mwp-filters']         = ! empty( $filter_params ) ? wp_json_encode( $filter_params ) : null;
		$data_attributes['mwp-orderby']         = '' !== $args['orderby'] ? sanitize_key( (string) $args['orderby'] ) : null;
		$data_attributes['mwp-order']           = 'desc' === strtolower( (string) $args['order'] ) ? 'desc' : 'asc';
		$attributes                             = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), $classes, $data_attributes );
		$table_attributes                       = Attrs::merge( is_array( $args['table_attributes'] ) ? $args['table_attributes'] : array(), $table_classes );
		$page_label                             = sprintf(
			/* translators: 1: current page number, 2: total pages. */
			__( 'Page %1$d of %2$d', 'matterwp-admin-ui' ),
			$current_page,
			$total_pages
		);
		?>
		<div <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<table <?php echo Attrs::render( $table_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<thead>
					<tr>
						<?php foreach ( $columns as $column ) : ?>
							<?php echo self::renderHeaderCell( $column, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php echo self::dataTableRows( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</tbody>
			</table>
			<?php if ( wp_validate_boolean( $args['pagination'] ) ) : ?>
				<div class="<?php echo esc_attr( $pagination_classes ); ?>" data-mwp-pagination>
					<button class="<?php echo esc_attr( $prev_classes ); ?>" type="button" data-mwp-page="prev" <?php disabled( $current_page <= 1 ); ?>><?php esc_html_e( 'Previous', 'matterwp-admin-ui' ); ?></button>
					<span class="<?php echo esc_attr( $info_classes ); ?>"><?php echo esc_html( $page_label ); ?></span>
					<button class="<?php echo esc_attr( $next_classes ); ?>" type="button" data-mwp-page="next" <?php disabled( $current_page >= $total_pages ); ?>><?php esc_html_e( 'Next', 'matterwp-admin-ui' ); ?></button>
				</div>
			<?php endif; ?>
			<?php if ( is_array( $args['empty_state'] ) ) : ?>
				<?php
				$empty_state = $args['empty_state'];
				if ( '' !== $empty_id ) {
					$empty_state['attributes']       = is_array( $empty_state['attributes'] ?? null ) ? $empty_state['attributes'] : array();
					$empty_state['attributes']['id'] = $empty_id;
				}
				$empty_state['attributes']           = is_array( $empty_state['attributes'] ?? null ) ? $empty_state['attributes'] : array();
				$empty_state['attributes']['hidden'] = count( $args['rows'] ) > 0;
				self::emptyState( $empty_state );
				?>
			<?php endif; ?>
			<div class="mwp-table-status is-loading" data-mwp-table-loading role="status" aria-live="polite" hidden>
				<span class="mwp-table-status__spinner" aria-hidden="true"></span>
				<span><?php echo esc_html( (string) $args['loading_label'] ); ?></span>
			</div>
			<div class="mwp-table-status is-error" data-mwp-table-error role="alert" hidden>
				<span><?php echo esc_html( (string) $args['error_label'] ); ?></span>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a richer data table.
	 *
	 * @param array $args Data table arguments.
	 * @return void
	 */
	public static function dataTable( array $args ): void {
		$has_pagination = array_key_exists( 'pagination', $args ) ? wp_validate_boolean( $args['pagination'] ) : true;
		$filters        = self::normalizeTableFilters( is_array( $args['filters'] ?? null ) ? $args['filters'] : array() );
		$has_search     = ! empty( self::normalizeTableSearch( $args['search'] ?? false ) );

		if ( ! empty( $filters ) || $has_search ) {
			$filter_mode = in_array( $args['filter_mode'] ?? '', array( 'client', 'server' ), true ) ? $args['filter_mode'] : ( ( $args['pagination_mode'] ?? 'client' ) === 'server' ? 'server' : 'client' );
			?>
			<div class="mwp-data-table" data-mwp-data-table data-mwp-filter-mode="<?php echo esc_attr( $filter_mode ); ?>">
				<?php
				self::tableFilters(
					array(
						'filters' => $filters,
						'search'  => $args['search'] ?? false,
					)
				);
				?>
				<?php
				if ( $has_pagination ) {
					self::paginatedTable( $args );
				} else {
					self::table( $args );
				}
				?>
			</div>
			<?php
			return;
		}

		if ( $has_pagination ) {
			self::paginatedTable( $args );
			return;
		}

		self::table( $args );
	}

	/**
	 * Render DataTable filter buttons and search input.
	 *
	 * @param array $args Filter toolbar arguments.
	 * @return void
	 */
	public static function tableFilters( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'filters'         => array(),
				'search'          => false,
				'class'           => '',
				'attributes'      => array(),
				'data_attributes' => array(),
			)
		);

		$filters    = self::normalizeTableFilters( is_array( $args['filters'] ) ? $args['filters'] : array() );
		$search     = self::normalizeTableSearch( $args['search'] );
		$attributes = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), trim( 'mwp-table-filters ' . $args['class'] ), is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );

		if ( empty( $filters ) && empty( $search ) ) {
			return;
		}
		?>
		<div <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> data-mwp-table-filters>
			<div class="mwp-table-filters__toolbar">
				<?php if ( ! empty( $filters ) ) : ?>
					<div class="mwp-table-filters__groups">
						<?php foreach ( $filters as $filter ) : ?>
							<div class="mwp-table-filters__buttons" role="group" aria-label="<?php echo esc_attr( $filter['label'] ); ?>">
								<?php foreach ( $filter['options'] as $value => $label ) : ?>
									<button class="<?php echo esc_attr( (string) $value === $filter['value'] ? 'active' : '' ); ?>" type="button" data-mwp-table-filter="<?php echo esc_attr( $filter['name'] ); ?>" data-mwp-filter-value="<?php echo esc_attr( (string) $value ); ?>" data-mwp-filter-empty="<?php echo (string) $value === (string) $filter['all_value'] ? 'true' : 'false'; ?>" aria-pressed="<?php echo (string) $value === $filter['value'] ? 'true' : 'false'; ?>"><?php echo esc_html( (string) $label ); ?></button>
								<?php endforeach; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<?php if ( ! empty( $search ) ) : ?>
					<label class="mwp-table-filters__search">
						<span class="screen-reader-text"><?php echo esc_html( $search['label'] ); ?></span>
						<input type="search" name="<?php echo esc_attr( $search['name'] ); ?>" value="<?php echo esc_attr( $search['value'] ); ?>" placeholder="<?php echo esc_attr( $search['placeholder'] ); ?>" data-mwp-table-search>
					</label>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render typed data table rows as HTML.
	 *
	 * @param array $args Data table row arguments.
	 * @return string
	 */
	public static function dataTableRows( array $args ): string {
		$args = wp_parse_args(
			$args,
			array(
				'columns'             => array(),
				'rows'                => array(),
				'row_id'              => '',
				'row_key'             => '',
				'row_class'           => '',
				'row_attributes'      => array(),
				'row_data_attributes' => array(),
			)
		);

		$columns = self::normalizeColumns( $args['columns'] );
		$output  = '';

		foreach ( $args['rows'] as $row ) {
			$output .= self::renderRow( is_array( $row ) ? $row : array(), $columns, $args );
		}

		return $output;
	}

	/**
	 * Render one typed data table row as HTML.
	 *
	 * @param array<string, mixed>             $row Row data.
	 * @param array<int, array<string, mixed>> $columns Columns.
	 * @param array<string, mixed>             $args Table args.
	 * @return string
	 */
	public static function dataTableRow( array $row, array $columns, array $args = array() ): string {
		return self::renderRow( $row, self::normalizeColumns( $columns ), $args );
	}

	/**
	 * Build the supported DataTable AJAX response payload.
	 *
	 * @param array $args Response arguments.
	 * @return array<string, mixed>
	 */
	public static function dataTableResponse( array $args ): array {
		$args = wp_parse_args(
			$args,
			array(
				'columns'     => array(),
				'rows'        => array(),
				'page'        => 1,
				'per_page'    => 10,
				'total'       => 0,
				'total_pages' => 0,
				'empty_state' => null,
			)
		);

		$per_page   = max( 1, absint( $args['per_page'] ) );
		$total      = $args['total'] ? absint( $args['total'] ) : count( $args['rows'] );
		$total_page = $args['total_pages'] ? absint( $args['total_pages'] ) : (int) ceil( $total / $per_page );
		$page       = max( 1, min( max( 1, $total_page ), absint( $args['page'] ) ) );
		$response   = array(
			'rows_html'   => self::dataTableRows( $args ),
			'page'        => $page,
			'per_page'    => $per_page,
			'total'       => $total,
			'total_pages' => max( 1, $total_page ),
		);

		if ( empty( $args['rows'] ) && is_array( $args['empty_state'] ) ) {
			ob_start();
			self::emptyState( $args['empty_state'] );
			$response['empty_html'] = (string) ob_get_clean();
		}

		return $response;
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
				'actions'         => array(),
				'class'           => '',
				'attributes'      => array(),
				'data_attributes' => array(),
			)
		);

		$classes    = trim( 'mwp-table-actions ' . $args['class'] );
		$attributes = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), $classes, is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		?>
		<div <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
				'confirm'         => '',
			)
		);

		$variant                  = in_array( $args['variant'], array( 'normal', 'danger' ), true ) ? $args['variant'] : 'normal';
		$classes                  = trim( 'mwp-table-action is-' . $variant . ' ' . $args['class'] );
		$attributes               = is_array( $args['attributes'] ) ? $args['attributes'] : array();
		$attributes['class']      = $classes;
		$attributes['aria-label'] = $args['label'];
		$icon                     = self::iconMarkup( (string) $args['icon'] );

		if ( '' === $icon && '' !== $args['label'] ) {
			$icon = self::iconMarkup( strtolower( (string) $args['label'] ) );
		}

		if ( '' !== $args['confirm'] ) {
			$attributes['data-mwp-confirm'] = is_array( $args['confirm'] ) ? ( $args['confirm']['message'] ?? '' ) : $args['confirm'];
		}

		if ( is_array( $args['data_attributes'] ) ) {
			foreach ( $args['data_attributes'] as $key => $value ) {
				$key = (string) $key;
				if ( 'data-' !== substr( $key, 0, 5 ) ) {
					$key = 'data-' . ltrim( $key, '-' );
				}
				$attributes[ $key ] = $value;
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
		<<?php echo tag_escape( $tag ); ?> <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php echo self::ksesCell( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</<?php echo tag_escape( $tag ); ?>>
		<?php
	}

	/**
	 * Render an empty state block.
	 *
	 * @param array<string, mixed> $args Empty state args.
	 * @param callable|null        $content Optional custom content callback.
	 * @return void
	 */
	public static function emptyState( array $args, callable $content = null ): void {
		$args = wp_parse_args(
			$args,
			array(
				'title'           => '',
				'description'     => '',
				'class'           => '',
				'attributes'      => array(),
				'data_attributes' => array(),
			)
		);

		$attributes = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), trim( 'mwp-empty-state ' . $args['class'] ), is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		?>
		<div <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php if ( '' !== $args['title'] ) : ?>
				<div class="mwp-empty-state__title"><?php echo esc_html( $args['title'] ); ?></div>
			<?php endif; ?>
			<?php if ( '' !== $args['description'] ) : ?>
				<div class="mwp-empty-state__description"><?php echo wp_kses_post( $args['description'] ); ?></div>
			<?php endif; ?>
			<?php if ( null !== $content ) : ?>
				<div class="mwp-empty-state__content">
					<?php $content(); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Return a badge cell.
	 *
	 * @param string              $label Badge label.
	 * @param string|array<mixed> $args Variant string or badge args.
	 * @return string
	 */
	public static function badgeCell( string $label, $args = array() ): string {
		$badge_args          = is_array( $args ) ? $args : array( 'variant' => (string) $args );
		$badge_args['label'] = $label;
		ob_start();
		Controls::badge( $badge_args );
		return (string) ob_get_clean();
	}

	/**
	 * Return a link cell.
	 *
	 * @param string $label Link label.
	 * @param string $url Link URL.
	 * @param array  $args Extra attributes.
	 * @return string
	 */
	public static function linkCell( string $label, string $url, array $args = array() ): string {
		$attributes          = is_array( $args['attributes'] ?? null ) ? $args['attributes'] : array();
		$display_label       = self::formatDisplayValue( $label, $args );
		$attributes['href']  = esc_url( $url );
		$attributes['class'] = trim( 'mwp-table-link ' . ( ! empty( $args['external'] ) ? 'is-external ' : '' ) . ( $args['class'] ?? '' ) );

		if ( ! empty( $args['external'] ) ) {
			$attributes['target'] = $attributes['target'] ?? '_blank';
			$attributes['rel']    = $attributes['rel'] ?? 'noopener noreferrer';
		}

		return '<a ' . Attrs::render( $attributes ) . '>' . esc_html( $display_label ) . '</a>';
	}

	/**
	 * Return a code cell.
	 *
	 * @param mixed $value Cell value.
	 * @return string
	 */
	public static function codeCell( $value ): string {
		return '<code class="mwp-table-code">' . esc_html( (string) $value ) . '</code>';
	}

	/**
	 * Return an image cell.
	 *
	 * @param string $src Image source URL.
	 * @param string $alt Image alt text.
	 * @param array  $args Extra args.
	 * @return string
	 */
	public static function imageCell( string $src, string $alt = '', array $args = array() ): string {
		if ( '' === $src ) {
			return '';
		}

		$attributes        = Attrs::merge( is_array( $args['attributes'] ?? null ) ? $args['attributes'] : array(), trim( 'mwp-table-image ' . ( $args['class'] ?? '' ) ) );
		$attributes['src'] = esc_url( $src );
		$attributes['alt'] = $alt;
		return '<img ' . Attrs::render( $attributes ) . '>';
	}

	/**
	 * Return a formatted date cell.
	 *
	 * @param mixed  $value Date value.
	 * @param string $format Date format.
	 * @return string
	 */
	public static function dateCell( $value, string $format = '' ): string {
		$timestamp = is_numeric( $value ) ? (int) $value : strtotime( (string) $value );

		if ( ! $timestamp ) {
			return esc_html( (string) $value );
		}

		$format = '' !== $format ? $format : get_option( 'date_format' );
		return '<time class="mwp-table-date" datetime="' . esc_attr( gmdate( 'c', $timestamp ) ) . '">' . esc_html( wp_date( $format, $timestamp ) ) . '</time>';
	}

	/**
	 * Return an action group cell.
	 *
	 * @param array<int, array<string, mixed>> $actions Actions.
	 * @return string
	 */
	public static function actionsCell( array $actions ): string {
		ob_start();
		self::actionGroup( array( 'actions' => $actions ) );
		return (string) ob_get_clean();
	}

	/**
	 * Render a result/preview card.
	 *
	 * @param array<string, mixed> $args Result card args.
	 * @return void
	 */
	public static function resultCard( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'title'           => '',
				'description'     => '',
				'image'           => '',
				'image_alt'       => '',
				'status'          => '',
				'variant'         => 'standard',
				'meta'            => array(),
				'values'          => array(),
				'actions'         => array(),
				'primary_link'    => array(),
				'class'           => '',
				'attributes'      => array(),
				'data_attributes' => array(),
			)
		);

		$variant    = in_array( $args['variant'], array( 'standard', 'success', 'warning', 'danger' ), true ) ? $args['variant'] : 'standard';
		$attributes = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), trim( 'mwp-result-card is-' . $variant . ' ' . $args['class'] ), is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		?>
		<div <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php if ( '' !== $args['image'] ) : ?>
				<div class="mwp-result-card__preview">
					<img src="<?php echo esc_url( $args['image'] ); ?>" alt="<?php echo esc_attr( $args['image_alt'] ); ?>">
				</div>
			<?php endif; ?>
			<div class="mwp-result-card__body">
				<div class="mwp-result-card__header">
					<?php if ( '' !== $args['title'] ) : ?>
						<h4><?php echo esc_html( $args['title'] ); ?></h4>
					<?php endif; ?>
					<?php if ( '' !== $args['status'] ) : ?>
						<?php
						Controls::badge(
							array(
								'label'   => $args['status'],
								'variant' => 'success',
								'size'    => 'compact',
							)
						);
						?>
					<?php endif; ?>
				</div>
				<?php if ( '' !== $args['description'] ) : ?>
					<p><?php echo esc_html( $args['description'] ); ?></p>
				<?php endif; ?>
				<?php self::renderMetaRows( 'mwp-result-card__meta', $args['meta'] ); ?>
				<?php self::renderMetaRows( 'mwp-result-card__values', $args['values'] ); ?>
				<?php if ( ! empty( $args['primary_link'] ) || ! empty( $args['actions'] ) ) : ?>
					<div class="mwp-result-card__actions">
						<?php
						if ( is_array( $args['primary_link'] ) && ! empty( $args['primary_link']['url'] ) ) {
							$primary_attributes           = is_array( $args['primary_link']['attributes'] ?? null ) ? $args['primary_link']['attributes'] : array();
							$primary_attributes['class']  = trim( 'mwp-button is-primary is-compact ' . ( $args['primary_link']['class'] ?? '' ) );
							$primary_attributes['href']   = esc_url( $args['primary_link']['url'] );
							$primary_attributes['target'] = $primary_attributes['target'] ?? '_blank';
							$primary_attributes['rel']    = $primary_attributes['rel'] ?? 'noopener noreferrer';
							?>
							<a <?php echo Attrs::render( $primary_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $args['primary_link']['label'] ?? __( 'Open', 'matterwp-admin-ui' ) ); ?></a>
							<?php
						}

						foreach ( is_array( $args['actions'] ) ? $args['actions'] : array() as $action ) {
							$action         = is_array( $action ) ? $action : array();
							$action['size'] = $action['size'] ?? 'compact';
							Controls::button( $action );
						}
						?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a key/value list.
	 *
	 * @param array<string, mixed> $args List args.
	 * @return void
	 */
	public static function keyValueList( array $args ): void {
		$args       = wp_parse_args(
			$args,
			array(
				'items'           => array(),
				'class'           => '',
				'attributes'      => array(),
				'data_attributes' => array(),
			)
		);
		$attributes = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), trim( 'mwp-key-value-list ' . $args['class'] ), is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		?>
		<dl <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php foreach ( $args['items'] as $label => $value ) : ?>
				<div class="mwp-key-value-list__item">
					<dt><?php echo esc_html( (string) $label ); ?></dt>
					<dd><?php echo self::ksesCell( $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
				</div>
			<?php endforeach; ?>
		</dl>
		<?php
	}

	/**
	 * Render an action bar.
	 *
	 * @param array<string, mixed> $args Action bar args.
	 * @return void
	 */
	public static function actionBar( array $args ): void {
		$args       = wp_parse_args(
			$args,
			array(
				'actions'         => array(),
				'align'           => 'right',
				'item_size'       => '',
				'class'           => '',
				'attributes'      => array(),
				'data_attributes' => array(),
			)
		);
		$align      = in_array( $args['align'], array( 'left', 'right', 'between' ), true ) ? $args['align'] : 'right';
		$item_size  = in_array( $args['item_size'], array( 'auto', 'grow' ), true ) ? $args['item_size'] : '';
		$attributes = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), trim( 'mwp-action-bar is-align-' . $align . ( '' !== $item_size ? ' has-item-' . $item_size : '' ) . ' ' . $args['class'] ), is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		?>
		<div <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php foreach ( $args['actions'] as $action ) : ?>
				<?php Controls::button( is_array( $action ) ? $action : array() ); ?>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render a switch grid.
	 *
	 * @param array<string, mixed> $args Switch grid args.
	 * @return void
	 */
	public static function switchGrid( array $args ): void {
		$args       = wp_parse_args(
			$args,
			array(
				'items'           => array(),
				'class'           => '',
				'attributes'      => array(),
				'data_attributes' => array(),
			)
		);
		$attributes = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), trim( 'mwp-switch-grid ' . $args['class'] ), is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		?>
		<div <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php foreach ( $args['items'] as $item ) : ?>
				<?php $item = is_array( $item ) ? $item : array(); ?>
				<div class="mwp-switch-grid__item" role="button" tabindex="0">
					<span class="mwp-switch-grid__text">
						<strong><?php echo esc_html( $item['title'] ?? '' ); ?></strong>
						<?php if ( ! empty( $item['description'] ) ) : ?>
							<span><?php echo esc_html( $item['description'] ); ?></span>
						<?php endif; ?>
					</span>
					<?php Controls::switch( $item['switch'] ?? $item ); ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render a radio/checkbox choice grid.
	 *
	 * @param array<string, mixed> $args Choice grid args.
	 * @return void
	 */
	public static function choiceGrid( array $args ): void {
		$args       = wp_parse_args(
			$args,
			array(
				'name'            => '',
				'value'           => '',
				'options'         => array(),
				'type'            => 'radio',
				'class'           => '',
				'attributes'      => array(),
				'data_attributes' => array(),
			)
		);
		$type       = in_array( $args['type'], array( 'radio', 'checkbox' ), true ) ? $args['type'] : 'radio';
		$attributes = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), trim( 'mwp-choice-grid is-' . $type . ' ' . $args['class'] ), is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		$values     = is_array( $args['value'] ) ? array_map( 'strval', $args['value'] ) : array( (string) $args['value'] );
		?>
		<div <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php foreach ( $args['options'] as $value => $option ) : ?>
				<?php
				$option      = is_array( $option ) ? $option : array( 'label' => $option );
				$input_value = (string) $value;
				?>
				<label class="mwp-choice-grid__item">
					<input name="<?php echo esc_attr( (string) $args['name'] ); ?>" type="<?php echo esc_attr( $type ); ?>" value="<?php echo esc_attr( $input_value ); ?>" <?php checked( in_array( $input_value, $values, true ) ); ?>>
					<span>
						<strong><?php echo esc_html( $option['label'] ?? $input_value ); ?></strong>
						<?php if ( ! empty( $option['description'] ) ) : ?>
							<small><?php echo esc_html( $option['description'] ); ?></small>
						<?php endif; ?>
					</span>
				</label>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Normalize table column definitions.
	 *
	 * @param array<mixed> $columns Raw columns.
	 * @return array<int, array<string, mixed>>
	 */
	private static function normalizeColumns( array $columns ): array {
		$normalized = array();

		foreach ( $columns as $key => $column ) {
			if ( is_array( $column ) ) {
				$column_key   = (string) ( $column['key'] ?? $key );
				$normalized[] = wp_parse_args(
					$column,
					array(
						'key'      => $column_key,
						'label'    => $column_key,
						'type'     => 'text',
						'wrap'     => false,
						'overflow' => '',
						'sortable' => false,
						'sort_key' => $column_key,
					)
				);
				continue;
			}

			$normalized[] = array(
				'key'      => (string) $key,
				'label'    => (string) $column,
				'type'     => 'text',
				'wrap'     => false,
				'overflow' => '',
				'sortable' => false,
				'sort_key' => (string) $key,
			);
		}

		return $normalized;
	}

	/**
	 * Normalize DataTable filter definitions.
	 *
	 * Scalar maps are retained as legacy request params and return no controls.
	 *
	 * @param array<mixed> $filters Raw filter definitions.
	 * @return array<int, array<string, mixed>>
	 */
	private static function normalizeTableFilters( array $filters ): array {
		$normalized = array();

		foreach ( $filters as $key => $filter ) {
			if ( ! is_array( $filter ) || ! is_array( $filter['options'] ?? null ) ) {
				continue;
			}

			$name = sanitize_key( (string) ( $filter['name'] ?? ( is_string( $key ) ? $key : '' ) ) );
			if ( '' === $name ) {
				continue;
			}

			$options = array();
			foreach ( $filter['options'] as $value => $label ) {
				$options[ (string) $value ] = is_array( $label ) ? (string) ( $label['label'] ?? $value ) : (string) $label;
			}

			if ( empty( $options ) ) {
				continue;
			}

			$value             = (string) ( $filter['value'] ?? array_key_first( $options ) );
			$filter['name']    = $name;
			$filter['options'] = $options;
			$filter['value']   = $value;
			$normalized[]      = wp_parse_args(
				$filter,
				array(
					'name'      => $name,
					'label'     => ucwords( str_replace( array( '-', '_' ), ' ', $name ) ),
					'options'   => $options,
					'value'     => $value,
					'row_key'   => $name,
					'all_value' => 'all',
				)
			);
		}

		return $normalized;
	}

	/**
	 * Normalize DataTable search configuration.
	 *
	 * @param mixed $search Search argument.
	 * @return array<string, string>
	 */
	private static function normalizeTableSearch( $search ): array {
		if ( false === $search || null === $search || '' === $search ) {
			return array();
		}

		$search = is_array( $search ) ? $search : array( 'value' => true === $search ? '' : (string) $search );

		return array(
			'name'        => sanitize_key( (string) ( $search['name'] ?? 'search' ) ),
			'label'       => (string) ( $search['label'] ?? __( 'Search table', 'matterwp-admin-ui' ) ),
			'placeholder' => (string) ( $search['placeholder'] ?? __( 'Search rows...', 'matterwp-admin-ui' ) ),
			'value'       => (string) ( $search['value'] ?? '' ),
		);
	}

	/**
	 * Render a DataTable header cell.
	 *
	 * @param array<string, mixed> $column Column definition.
	 * @param array<string, mixed> $args Table arguments.
	 * @return string
	 */
	private static function renderHeaderCell( array $column, array $args ): string {
		$sort_key = sanitize_key( (string) ( $column['sort_key'] ?? $column['key'] ) );
		$orderby  = sanitize_key( (string) ( $args['orderby'] ?? '' ) );
		$order    = 'desc' === strtolower( (string) ( $args['order'] ?? 'asc' ) ) ? 'desc' : 'asc';
		$active   = ! empty( $column['sortable'] ) && $sort_key === $orderby;
		$attrs    = array(
			'scope'     => 'col',
			'class'     => ! empty( $column['sortable'] ) ? 'is-sortable' : null,
			'aria-sort' => $active ? ( 'desc' === $order ? 'descending' : 'ascending' ) : null,
		);

		ob_start();
		?>
		<th <?php echo Attrs::render( $attrs ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php if ( ! empty( $column['sortable'] ) ) : ?>
				<button type="button" class="mwp-table-sort" data-mwp-table-sort="<?php echo esc_attr( $sort_key ); ?>" data-mwp-sort-order="<?php echo esc_attr( $active ? $order : 'asc' ); ?>">
					<span><?php echo esc_html( $column['label'] ); ?></span>
					<span class="mwp-table-sort__indicator" aria-hidden="true"></span>
				</button>
			<?php else : ?>
				<?php echo esc_html( $column['label'] ); ?>
			<?php endif; ?>
		</th>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Resolve initial/current page.
	 *
	 * @param mixed $initial_page Initial page arg.
	 * @param mixed $current_page Current page arg.
	 * @param int   $total_pages Total pages.
	 * @return int
	 */
	private static function resolveInitialPage( $initial_page, $current_page, int $total_pages ): int {
		if ( 'last' === $initial_page ) {
			return $total_pages;
		}

		if ( 'first' === $initial_page ) {
			return 1;
		}

		$page = '' !== $initial_page ? absint( $initial_page ) : absint( $current_page );
		return max( 1, min( $total_pages, $page ) );
	}

	/**
	 * Render a table cell from a typed column.
	 *
	 * @param array<string, mixed> $row Row data.
	 * @param array<string, mixed> $column Column definition.
	 * @return string
	 */
	private static function renderCell( array $row, array $column ): string {
		$key   = (string) $column['key'];
		$value = $row[ $key ] ?? '';

		if ( is_callable( $column['callback'] ?? null ) ) {
			return self::ksesCell( call_user_func( $column['callback'], $value, $row, $column ) );
		}

		switch ( $column['type'] ?? 'text' ) {
			case 'title':
				return '<strong class="mwp-table-title">' . esc_html( self::formatDisplayValue( (string) $value, $column ) ) . '</strong>';
			case 'badge':
				return self::ksesCell( self::badgeCell( (string) $value, $column['badge'] ?? array() ) );
			case 'external_link':
				$column['external'] = true;
				$url                = (string) ( $column['url'] ?? ( $row[ (string) ( $column['url_key'] ?? 'url' ) ] ?? $value ) );
				return self::ksesCell( self::linkCell( (string) $value, $url, $column ) );
			case 'link':
				$url = (string) ( $column['url'] ?? ( $row[ (string) ( $column['url_key'] ?? 'url' ) ] ?? $value ) );
				return self::ksesCell( self::linkCell( (string) $value, $url, $column ) );
			case 'code':
				return self::ksesCell( self::codeCell( $value ) );
			case 'image':
				return self::ksesCell( self::imageCell( (string) $value, (string) ( $column['alt'] ?? '' ), $column ) );
			case 'date':
				return self::ksesCell( self::dateCell( $value, (string) ( $column['format'] ?? '' ) ) );
			case 'actions':
				return self::ksesCell( self::actionsCell( is_array( $value ) ? $value : array() ) );
			case 'text':
				return esc_html( self::formatDisplayValue( (string) $value, $column ) );
			default:
				return self::ksesCell( $value );
		}
	}

	/**
	 * Render a typed table row.
	 *
	 * @param array<string, mixed>             $row Row data.
	 * @param array<int, array<string, mixed>> $columns Columns.
	 * @param array<string, mixed>             $args Table args.
	 * @return string
	 */
	private static function renderRow( array $row, array $columns, array $args ): string {
		$row_attributes = is_array( $args['row_attributes'] ?? null ) ? $args['row_attributes'] : array();
		$row_data       = is_array( $args['row_data_attributes'] ?? null ) ? $args['row_data_attributes'] : array();
		$row_key        = (string) ( $args['row_key'] ?? '' );
		$row_id_key     = (string) ( $args['row_id'] ?? '' );
		$row_classes    = trim( 'mwp-table-row ' . ( $args['row_class'] ?? '' ) . ' ' . ( $row['row_class'] ?? '' ) );

		if ( is_array( $row['row_attributes'] ?? null ) ) {
			$row_attributes = array_merge( $row_attributes, $row['row_attributes'] );
		}

		if ( is_array( $row['row_data_attributes'] ?? null ) ) {
			$row_data = array_merge( $row_data, $row['row_data_attributes'] );
		}

		if ( '' !== $row_id_key && isset( $row[ $row_id_key ] ) ) {
			$row_attributes['id'] = $row[ $row_id_key ];
		}

		if ( '' !== $row_key && isset( $row[ $row_key ] ) ) {
			$row_data['row-key']    = $row[ $row_key ];
			$row_data['mwp-row-id'] = $row[ $row_key ];
		}

		$filter_definitions = self::normalizeTableFilters( is_array( $args['filters'] ?? null ) ? $args['filters'] : array() );
		foreach ( $filter_definitions as $filter ) {
			$filter_row_key = (string) ( $filter['row_key'] ?? $filter['name'] );
			if ( isset( $row[ $filter_row_key ] ) ) {
				$row_data[ 'mwp-filter-' . sanitize_key( (string) $filter['name'] ) ] = $row[ $filter_row_key ];
			}
		}

		$attributes = Attrs::merge( $row_attributes, $row_classes, $row_data );

		ob_start();
		?>
		<tr <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php foreach ( $columns as $column ) : ?>
				<td <?php echo Attrs::render( self::cellAttributes( $column, $row ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo self::renderCell( $row, $column ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
			<?php endforeach; ?>
		</tr>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Build table cell attributes from a column definition.
	 *
	 * @param array<string, mixed> $column Column definition.
	 * @param array<string, mixed> $row Row data.
	 * @return array<string, mixed>
	 */
	private static function cellAttributes( array $column, array $row = array() ): array {
		$classes = array( 'mwp-table-cell', 'is-type-' . sanitize_html_class( (string) ( $column['type'] ?? 'text' ) ) );
		$styles  = array();
		$key     = (string) ( $column['key'] ?? '' );
		$value   = $row[ (string) ( $column['sort_key'] ?? $key ) ] ?? ( $row[ $key ] ?? '' );

		if ( is_callable( $column['sort_callback'] ?? null ) ) {
			$value = call_user_func( $column['sort_callback'], $value, $row, $column );
		}

		if ( ! empty( $column['wrap'] ) ) {
			$classes[] = 'is-wrap';
		}

		if ( ! empty( $column['overflow'] ) ) {
			$classes[] = 'has-overflow-' . sanitize_html_class( (string) $column['overflow'] );
		}

		foreach ( array(
			'min_width' => 'min-width',
			'max_width' => 'max-width',
			'width'     => 'width',
		) as $arg => $property ) {
			if ( ! empty( $column[ $arg ] ) ) {
				$styles[] = $property . ': ' . esc_attr( (string) $column[ $arg ] );
			}
		}

		if ( ! empty( $column['vertical_align'] ) ) {
			$styles[] = 'vertical-align: ' . esc_attr( (string) $column['vertical_align'] );
		}

		return array(
			'class'               => trim( implode( ' ', $classes ) . ' ' . ( $column['cell_class'] ?? '' ) ),
			'style'               => ! empty( $styles ) ? implode( '; ', $styles ) : null,
			'data-mwp-column'     => '' !== $key ? $key : null,
			'data-mwp-sort-value' => is_scalar( $value ) ? (string) $value : '',
		);
	}

	/**
	 * Format display text for common table/link cells.
	 *
	 * @param string               $value Raw value.
	 * @param array<string, mixed> $args Cell args.
	 * @return string
	 */
	private static function formatDisplayValue( string $value, array $args ): string {
		if ( is_callable( $args['display_callback'] ?? null ) ) {
			return (string) call_user_func( $args['display_callback'], $value, $args );
		}

		if ( ! empty( $args['strip_site_url'] ) || 'relative_site_url' === ( $args['format'] ?? '' ) ) {
			$site_url = home_url();
			if ( 0 === strpos( $value, $site_url ) ) {
				return '/' . ltrim( substr( $value, strlen( $site_url ) ), '/' );
			}
		}

		return $value;
	}

	/**
	 * Render keyed meta rows.
	 *
	 * @param string $wrapper_class Wrapper class.
	 * @param mixed  $rows Rows.
	 * @return void
	 */
	private static function renderMetaRows( string $wrapper_class, $rows ): void {
		if ( empty( $rows ) || ! is_array( $rows ) ) {
			return;
		}

		?>
		<div class="<?php echo esc_attr( $wrapper_class ); ?>">
			<?php foreach ( $rows as $label => $value ) : ?>
				<div>
					<span><?php echo esc_html( (string) $label ); ?></span>
					<strong><?php echo self::ksesCell( $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Return a named action SVG or custom SVG markup.
	 *
	 * @param string $icon Icon name or SVG.
	 * @return string
	 */
	public static function iconMarkup( string $icon ): string {
		if ( false !== strpos( $icon, '<svg' ) ) {
			return $icon;
		}

		$icons = array(
			'edit'           => '<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>',
			'delete'         => '<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg>',
			'link'           => '<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7 0l2-2a5 5 0 0 0-7-7l-1 1"/><path d="M14 11a5 5 0 0 0-7 0l-2 2a5 5 0 0 0 7 7l1-1"/></svg>',
			'download'       => '<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>',
			'copy'           => '<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 8h12v12H8z"/><path d="M4 16V4h12"/></svg>',
			'view'           => '<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>',
			'x'              => '<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>',
			'trash'          => '<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6l-1 14H6L5 6"/></svg>',
			'external-link'  => '<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M21 14v7H3V3h7"/></svg>',
			'arrow-up-right' => '<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>',
			'image'          => '<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"/></svg>',
			'play'           => '<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 3 14 9-14 9Z"/></svg>',
			'pause'          => '<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 4v16"/><path d="M16 4v16"/></svg>',
			'refresh'        => '<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11a8.1 8.1 0 0 0-15.5-2M4 5v4h4"/><path d="M4 13a8.1 8.1 0 0 0 15.5 2M20 19v-4h-4"/></svg>',
			'sun'            => '<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>',
			'moon'           => '<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.99 12.43A9 9 0 1 1 11.57 3a7 7 0 0 0 9.42 9.43Z"/></svg>',
		);

		return $icons[ $icon ] ?? '';
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

		foreach ( array( 'circle', 'line', 'polyline', 'rect' ) as $tag ) {
			$allowed[ $tag ] = array(
				'cx'              => true,
				'cy'              => true,
				'd'               => true,
				'fill'            => true,
				'height'          => true,
				'points'          => true,
				'r'               => true,
				'rx'              => true,
				'stroke'          => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
				'stroke-width'    => true,
				'width'           => true,
				'x'               => true,
				'x1'              => true,
				'x2'              => true,
				'y'               => true,
				'y1'              => true,
				'y2'              => true,
			);
		}

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
				'value'           => 0,
				'label'           => '',
				'max_width'       => '',
				'grow'            => false,
				'hidden'          => false,
				'class'           => '',
				'attributes'      => array(),
				'data_attributes' => array(),
			)
		);

		$value                       = max( 0, min( 100, absint( $args['value'] ) ) );
		$styles                      = array();
		$classes                     = trim( 'mwp-progress ' . ( $args['grow'] ? 'is-grow ' : '' ) . $args['class'] );
		$attributes                  = Attrs::merge( is_array( $args['attributes'] ) ? $args['attributes'] : array(), $classes, is_array( $args['data_attributes'] ) ? $args['data_attributes'] : array() );
		$attributes['role']          = 'progressbar';
		$attributes['aria-valuemin'] = 0;
		$attributes['aria-valuemax'] = 100;
		$attributes['aria-valuenow'] = $value;
		$attributes['hidden']        = wp_validate_boolean( $args['hidden'] );

		if ( '' !== $args['max_width'] ) {
			$styles[] = 'max-width: ' . esc_attr( (string) $args['max_width'] );
		}

		if ( ! empty( $styles ) ) {
			$attributes['style'] = implode( '; ', $styles );
		}
		?>
		<div <?php echo Attrs::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
				'title'   => '',
				'src'     => '',
				'alt'     => '',
				'caption' => '',
				'class'   => '',
			)
		);

		$classes = trim( 'mwp-lightbox-trigger ' . $args['class'] );
		?>
		<button class="<?php echo esc_attr( $classes ); ?>" type="button" data-mwp-lightbox-trigger data-mwp-lightbox-title="<?php echo esc_attr( $args['title'] ); ?>" data-mwp-lightbox-src="<?php echo esc_url( $args['src'] ); ?>" data-mwp-lightbox-alt="<?php echo esc_attr( $args['alt'] ); ?>" data-mwp-lightbox-caption="<?php echo esc_attr( $args['caption'] ); ?>">
			<img src="<?php echo esc_url( $args['src'] ); ?>" alt="<?php echo esc_attr( $args['alt'] ); ?>">
		</button>
		<?php
	}
}
