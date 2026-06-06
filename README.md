# MatterWP Admin UI

Reusable admin UI helpers, styles, and small JavaScript behaviors for MatterWP WordPress plugins.

## Installation

Install the package with Composer:

```bash
composer require matterwp/admin-ui
```

Load Composer's autoloader from your plugin bootstrap if your plugin does not already do so:

```php
require_once __DIR__ . '/vendor/autoload.php';
```

Import the helpers you need:

```php
use MatterWP\AdminUI\Assets;
use MatterWP\AdminUI\MatterAdminUI;
```

## Assets

The package includes compiled CSS and JavaScript in `dist/` for direct use from WordPress plugins.

```php
add_action(
	'admin_enqueue_scripts',
	static function (): void {
		Assets::enqueueBuiltStyle( 'my-plugin-admin-ui' );
		Assets::enqueueBuiltScript( 'my-plugin-admin-ui' );
	}
);
```

Call `wp_enqueue_media()` on admin pages that render media fields.

## Extension model

`matterwp/admin-ui` is intended to be used as a Composer package. Plugins should consume the base components and extend them with arguments, stable classes, WordPress filters, CSS custom properties, and plugin-specific JavaScript.

Avoid copying package internals into consuming plugins. If a plugin needs a very custom workflow, compose it from smaller admin-ui primitives or use component slots/callbacks where available.

## Attribute helpers

Use `MatterAdminUI::attrs()` or `MatterAdminUI::dataAttrs()` when a plugin needs to render escaped attributes consistently with the package.

```php
echo MatterAdminUI::attrs(
	array(
		'class' => 'plugin-custom-control',
		'data-plugin-control' => 'logo',
		'aria-live' => 'polite',
	)
);
```

## Layout Shells

Sections and options are the base shells for admin screens. A section groups related content. An option row arranges the label, supporting copy, counters, and the control/content area. Inputs, buttons, forms, cards, tables, and custom markup should fit into these shells without each component inventing layout chrome.

Most helpers accept `class`, `attributes`, and `data_attributes` for the component root. Nested wrappers accept `classes` slot maps, while legacy scoped keys such as `title_class`, `options_class`, `info_class`, `description_class`, `input_class`, `header_class`, `content_class`, `table_class`, and `pagination_class` still work.

Sections support:

- `title` and `description`: string content, or `false` to hide.
- `option_box`: `standard` for normal grouped option chrome, or `minimal` for no option-box borders, radius, background, or option padding.
- `variant`: specialized compatibility variants such as `borderless` and `table-only`.

Use `option_box => 'minimal'` for direct content such as stat grids:

```php
MatterAdminUI::section(
	array(
		'title'      => __( 'Stats', 'plugin' ),
		'option_box' => 'minimal',
	),
	static function (): void {
		// Render stat cards, grids, or other direct content.
	}
);
```

Options support:

- `title` and `description`: string content, or `false` to hide.
- `input_label`: show or hide the option label/info block. When true and `input_id` is provided, the title renders as a `<label>`.
- `label_width`: `standard` or `full`.
- `layout`: `row`, `column`, or `divided`. Legacy `style`, `wide`, and `divider` aliases still map here.
- `control_width`: `narrow`, `standard`, `wide`, or `full`.
- `align`: `center`, `start`, or `stretch`.
- `badge`, `help`, and `actions` for option metadata and inline commands.

```php
MatterAdminUI::option(
	array(
		'title'       => __( 'Status', 'plugin' ),
		'description' => __( 'Full-width label with a divided control area.', 'plugin' ),
		'style'       => 'divided',
		'label_width' => 'full',
		'input_label' => true,
		'input_id'    => 'plugin-status',
	),
	static function (): void {
		MatterAdminUI::select(
			array(
				'id'      => 'plugin-status',
				'value'   => 'queued',
				'options' => array(
					'queued' => __( 'Queued', 'plugin' ),
					'done'   => __( 'Done', 'plugin' ),
				),
			)
		);
	}
);
```

Use `variant => 'table-only'` for a table rendered directly in a section:

```php
MatterAdminUI::section(
	array(
		'title'   => __( 'Entries', 'plugin' ),
		'variant' => 'table-only',
	),
	static function (): void {
		MatterAdminUI::paginatedTable(
			array(
				'columns'  => array( 'name' => __( 'Name', 'plugin' ) ),
				'rows'     => $rows,
				'per_page' => 10,
			)
		);
	}
);
```

Schema-driven rows can use `schemaOption()`:

```php
MatterAdminUI::schemaOption(
	array(
		'name'        => 'plugin_limit',
		'label'       => __( 'Limit', 'plugin' ),
		'description' => __( 'Maximum items to process.', 'plugin' ),
		'type'        => 'number',
		'min'         => 1,
		'max'         => 100,
		'step'        => 1,
	),
	10,
	array(
		'option' => array( 'control_width' => 'narrow' ),
	)
);
```

Forms support `variant => 'plain'`, `columns => 1|2|3|'auto'`, and `actions_align => 'left'|'right'|'between'`.

Inputs support standard text-like controls plus schedule controls: `text`, `number`, `url`, `email`, `password`, `search`, `color`, `tel`, `hidden`, `date`, `time`, `datetime-local`, `month`, and `week`. The same types work in generated `form()` fields and `schemaOption()` rows.

For a schedule row with start/end datetime and timezone, compose a column option with `fieldGrid()`:

```php
MatterAdminUI::option(
	array(
		'title'         => __( 'Activation Schedule', 'plugin' ),
		'description'   => __( 'Schedule when maintenance should begin and end.', 'plugin' ),
		'layout'        => 'column',
		'control_width' => 'full',
	),
	static function () use ( $values ): void {
		MatterAdminUI::fieldGrid(
			array(
				'columns' => 3,
				'density' => 'compact',
			),
			static function () use ( $values ): void {
				MatterAdminUI::input(
					array(
						'name'  => 'schedule_start',
						'type'  => 'datetime-local',
						'value' => $values['schedule_start'] ?? '',
					)
				);
				MatterAdminUI::input(
					array(
						'name'  => 'schedule_end',
						'type'  => 'datetime-local',
						'value' => $values['schedule_end'] ?? '',
					)
				);
				MatterAdminUI::select(
					array(
						'name'    => 'schedule_timezone',
						'value'   => $values['schedule_timezone'] ?? wp_timezone_string(),
						'options' => array_combine( timezone_identifiers_list(), timezone_identifiers_list() ),
					)
				);
			}
		);
	}
);
```

Badges support `size => 'compact'`.

`MatterAdminUI::app()` renders the common admin shell:

```php
MatterAdminUI::app(
	array(
		'brand'      => __( 'Plugin Name', 'plugin' ),
		'version'    => '1.0.3',
		'tabs'       => array(
			'general' => __( 'General', 'plugin' ),
			'logs'    => __( 'Logs', 'plugin' ),
		),
		'form'       => true,
	),
	static function (): void {
		MatterAdminUI::panel( 'general', static function (): void {} );
		MatterAdminUI::panel( 'logs', static function (): void {} );
	}
);
```

The shell includes package-owned tab persistence, active indicator measurement, and a dark-mode toggle. Use `MatterAdminUI::fieldGrid()` or `form( array( 'fields' => ..., 'actions' => ... ) )` for generated form layouts.

Schema rows now understand richer metadata: `ui`, `component`, `option`, `control`, `layout`, `control_width`, `media`, `choices_display`, `placeholder`, `help`, `dependencies`, `visible_if`, `disabled_if`, and `requires`. Components include `color_picker`, `media`, `switch`, `select`, `textarea`, and standard inputs.

## Tables

Admin tables use `.mwp-table-wrap` and `.mwp-table`. Columns can be strings or typed arrays with `label`, `type`, and optional `callback`. Supported cell types include `badge`, `link`, `code`, `image`, `date`, and `actions`.

`MatterAdminUI::paginatedTable()` provides static client-side pagination with previous/next buttons, a page label, default `per_page` of 10, mutation re-rendering, `empty_selector`/`empty_target`, `initial_page => 'first'|'last'|1`, and an `mwp:table-refresh` event for manual refreshes.

`MatterAdminUI::dataTable()` adds row-level and typed-column arguments for plugin-managed tables: `row_id`, `row_key`, `row_attributes`, `row_data_attributes`, `row_class`, `empty_state`, `pagination`, `table_class`, `wrap`, `min_width`, `max_width`, `overflow`, `vertical_align`, `strip_site_url`, `display_callback`, and `format => 'relative_site_url'`. Column types include `text`, `title`, `link`, `external_link`, `code`, `image`, `badge`, `date`, and `actions`.

The JavaScript table API is available as:

```js
MatterAdminUI.table.appendRow(table, rowHtml, { page: 'last' });
MatterAdminUI.table.updateRow(table, rowId, rowHtml);
MatterAdminUI.table.removeRow(table, rowId);
MatterAdminUI.table.refresh(table, { page: 'first' });
```

For compact table action columns, use `MatterAdminUI::actionGroup()` with named or custom SVG icons:

```php
MatterAdminUI::actionGroup(
	array(
		'actions' => array(
			array(
				'label' => __( 'Edit', 'plugin' ),
				'icon'  => 'edit',
				'url'   => $edit_url,
			),
			array(
				'label'   => __( 'Delete', 'plugin' ),
				'icon'    => 'delete',
				'variant' => 'danger',
				'confirm' => __( 'Delete this row?', 'plugin' ),
			),
		),
	)
);
```

Named icons include `edit`, `delete`, `trash`, `x`, `link`, `external-link`, `download`, `copy`, `view`, `image`, `play`, `pause`, and `refresh`.

## Modals, Ajax, And Results

Modals can be rendered without a trigger by passing `render_trigger => false` or `trigger => false`. Modal fields support `attributes`, `data_attributes`, `readonly`, `required`, `placeholder`, `autocomplete`, `min`, `max`, `step`, `field_class`, and `layout`. Footer actions support `id`, `attributes`, `data_attributes`, `disabled`, `loading`, `size`, and `icon`.

Ajax forms can use `data-mwp-ajax-form`, `data-mwp-action`, `data-mwp-submit`, and `data-mwp-loading`. The package exposes `MatterAdminUI.ajax.submit(formOrRoot, options)` and dispatches `mwp:ajax-before`, `mwp:ajax-success`, `mwp:ajax-error`, and `mwp:ajax-complete`.

Use `MatterAdminUI.notice(message, type, options)` for package-managed notices. Use `MatterAdminUI::resultCard()` for generated previews, exported files, uploaded assets, API keys, reports, or other Ajax results.

Use `MatterAdminUI::emptyState()` for neutral dashed empty blocks. Pair with paginated tables via `empty_target` or `empty_selector`.

## Dynamic modals

Modal markup follows the package contract: triggers use `data-mwp-modal-trigger`, `aria-controls`, `aria-haspopup="dialog"`, and `aria-expanded`; modals use `data-mwp-modal`, `aria-hidden`, and `hidden`; close controls use `data-mwp-modal-close`.

JavaScript exposes `window.MatterAdminUI.openModal(idOrElement, values, trigger)`, `closeModal(idOrElement)`, and `populateModal(idOrElement, values)` for edit modals. Values are matched by `name` or `data-mwp-field`.

Modals can generate simple fields with `fields` and submit controls with `footer_actions`:

```php
MatterAdminUI::modal(
	array(
		'id'     => 'plugin-edit-modal',
		'title'  => __( 'Edit item', 'plugin' ),
		'fields' => array(
			array(
				'name'  => 'item_name',
				'label' => __( 'Name', 'plugin' ),
				'type'  => 'text',
			),
		),
		'footer_actions' => array(
			array( 'label' => __( 'Save', 'plugin' ), 'type' => 'submit', 'variant' => 'primary' ),
		),
	),
	static function (): void {}
);
```

## Media field customization

The media field supports compact, logo, wide, and button-only modes. It can also be customized with CSS variables, data attributes, slots, and filters.

```php
MatterAdminUI::mediaField(
	array(
		'name'           => 'plugin_logo_id',
		'value'          => $logo_id,
		'mode'           => 'logo',
		'preview_height' => '80px',
		'preview_ratio'  => '1 / 1',
		'button_text'    => __( 'Choose Logo', 'plugin' ),
		'remove_text'    => __( 'Remove Logo', 'plugin' ),
		'data_attributes' => array(
			'plugin-logo-picker' => 'true',
		),
	)
);
```

For deeper customization, pass callable slots:

```php
MatterAdminUI::mediaField(
	array(
		'name' => 'plugin_image_id',
		'preview' => static function ( array $args, string $uid, string $image_url ): void {
			// Render custom preview markup. Include data-media-preview when using package JS.
		},
		'actions' => static function ( array $args, string $uid ): void {
			// Render custom action markup. Include data-media-input, data-media-target, and data-media-remove when using package JS.
		},
	)
);
```

Available media filters:

- `matterwp_admin_ui_media_field_args`
- `matterwp_admin_ui_media_field_preview`
- `matterwp_admin_ui_media_field_actions`

Available media JavaScript events:

- `mwp:media-selected`
- `mwp:media-removed`

Both events bubble from the media field root and include `uid`, `field`, and related control details in `event.detail`.
