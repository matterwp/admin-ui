# MatterWP Admin UI Component Reference

Version: `1.0.3`

This document maps the public `MatterWP\AdminUI\MatterAdminUI` API, accepted options, style variants, JavaScript hooks, and current boilerplate usage.

Primary source files:

- `src/MatterAdminUI.php`: public facade.
- `src/Layout.php`: app shell, sections, options, cards, forms, fields.
- `src/Controls.php`: primitive controls.
- `src/InputGroups.php`: compound input controls.
- `src/Components.php`: tables, data display, action groups, utility components.
- `src/Overlays.php`: modals and confirmation dialogs.
- `src/Premium.php`: premium badge and locked-control helpers.
- `resources/scss/matterwp/`: component styles.
- `resources/js/admin-ui.js`: tabs, modals, lightbox, accordions, notices, Ajax forms, dependency handling.
- `resources/js/modules/media-controls.js`: WordPress media field behavior.
- `resources/js/modules/log-viewer.js`: log filtering and table pagination.
- `resources/js/modules/clipboard.js`: clipboard feedback.

## General Contracts

Most modern components accept:

| Option | Type | Purpose |
| --- | --- | --- |
| `class` | string | Extra class on the component root. |
| `attributes` | array | Root HTML attributes. Boolean `true` renders valueless attributes. `false`, `null`, and empty attribute names are skipped. |
| `data_attributes` | array | Root `data-*` attributes. Keys may include or omit the `data-` prefix. |
| `classes` | array | Slot class map for nested wrappers, for example `array( 'table' => 'my-table' )`. |

Legacy slot class options still work where present, such as `title_class`, `options_class`, `info_class`, `description_class`, `input_class`, `header_class`, `content_class`, `table_class`, and `pagination_class`.

## Layout Components

### `panel( string $id, callable $content )`

Renders one tab panel.

| Argument | Purpose |
| --- | --- |
| `$id` | Tab panel id. Rendered as `data-ui-panel`. |
| `$content` | Panel body callback. |

Current use: every admin tab wraps content in `panel()`.

### `app( array $args, callable $content )`

Renders the full admin app shell with header, tabs, optional form, theme toggle, and content.

| Option | Default | Accepted values / notes |
| --- | --- | --- |
| `id` | `mwp-settings` | Root id. |
| `brand` | empty | Header brand text. |
| `logo` | empty | Image URL or SVG markup. |
| `version` | empty | Header version badge text. |
| `tabs` | `array()` | Tab map. String values become labels. Array items may contain `id`, `label`, `url`, `active`, `icon`. |
| `active_tab` | empty | Active tab id. If empty, tab item `active` is used. |
| `form` | `true` | Wrap content in a `<form>`. |
| `form_attributes` | `array()` | Attributes for the generated form. |
| `options_class` | empty | Extra class on `.mwp-options`. |
| `header_actions` | `array()` | Array of `button()` args. |
| `theme_toggle` | `true` | Render package dark-mode toggle. |
| `layout` | `standard` | `standard`, `fullscreen`. |
| `full_width` | `false` | Adds `is-full-width`. |
| `class` | empty | Extra root class. |
| `attributes` | `array()` | Root attributes. |
| `data_attributes` | `array()` | Root data attributes. |

Current use: `templates/admin/settings.php` passes `brand`, `logo`, `version`, `tabs`, `options_class => mwp-options-full-width`, and `form_attributes` with `method` and `data-ui-form`.

### `section( array $args, callable $content )`

Groups option rows or direct content.

| Option | Default | Accepted values / notes |
| --- | --- | --- |
| `title` | empty | String, or false-like value to hide. |
| `description` | empty | String, or false-like value to hide. |
| `badge` | `null` | Args for `premiumBadge()`. |
| `section_variant` | `wrapped` | `wrapped`, `minimal`. Use `minimal` for direct table/card/stat/result layouts. |
| `option_variant` | `standard` | `standard`, `minimal`. Use `minimal` for option rows without row chrome or padding. |
| `class` | empty | Root class. |
| `title_class` | empty | Title wrapper class. |
| `options_class` | empty | Options wrapper class. |
| `classes` | `array()` | Slot classes: `title`, `options`. |
| `attributes` | `array()` | Root attributes. |
| `data_attributes` | `array()` | Root data attributes. |

Current use:

- Standard sections across all tabs.
- `section_variant => minimal` for cards, stats, empty state, result card, and data table.
- `badge` for the locked premium section.

### `option( array $args, callable $control )`

Renders one setting row.

| Option | Default | Accepted values / notes |
| --- | --- | --- |
| `title` | empty | Row title, or false-like value to hide. |
| `description` | empty | Row description, supports safe HTML. |
| `count` | `null` | Small count text in info area. |
| `class` | empty | Root class. |
| `input_label` | `true` | Show info/label block. |
| `input_id` | empty | If set, title renders as `<label for="...">`. |
| `label_width` | `standard` | `standard`, `full`. |
| `layout` | `row` | `row`, `column`, `divided`. |
| `control_width` | `standard` | `narrow`, `standard`, `wide`, `full`. |
| `align` | empty | `center`, `start`, `stretch`. |
| `badge` | `null` | String label or badge args. Defaults to compact size. |
| `help` | empty | Help text below control, supports safe HTML. |
| `actions` | `array()` | Inline action buttons, each using `button()` args. |
| `info_class` | empty | Info wrapper class. |
| `title_class` | empty | Title class. |
| `description_class` | empty | Description class. |
| `count_class` | empty | Count class. |
| `input_class` | empty | Input wrapper class. |
| `help_class` | empty | Help class. |
| `actions_class` | empty | Actions wrapper class. |
| `classes` | `array()` | Slot classes: `info`, `title`, `description`, `count`, `input`, `help`, `actions`. |
| `attributes` | `array()` | Root attributes. |
| `data_attributes` | `array()` | Root data attributes. |

Current use:

- `layout => column` with `input_class => mwp-option-input-wide` for wide examples.
- `layout => divided`, `label_width => full`, `control_width => full` for divided rows.
- `align => start` for media field.
- `badge`, `count`, `help`, and `actions` in primitive examples.

### `schemaOption( array $schema, $value = null, array $overrides = array() )`

Renders an `option()` row and selects the control from schema metadata.

Schema keys:

| Key | Purpose |
| --- | --- |
| `name`, `key` | Control name. |
| `id` | Control id. Defaults from name. |
| `type` | Control type. Supports `boolean`, `bool`, `switch`, `multi_select`, `multi-switch`, `multi_switch`, `select`, `choice`, `textarea`, and all `input()` types. |
| `default` | Fallback value. |
| `label`, `title` | Option title. |
| `description` | Option description. |
| `ui` | Nested UI metadata. May include `component`, `option`, `control`, `layout`, `control_width`, `help`, `badge`. |
| `component` | Force component. Accepted aliases: `color_picker`, `colorPicker`, `media`, `media_field`, `mediaField`. |
| `option` | Args merged into `option()`. |
| `control` | Args merged into generated control. |
| `media` | Args merged into `mediaField()` when media component is used. |
| `options`, `choices` | Choice map for select and choice grids. |
| `choices_display` | `grid`, `cards` to render `choiceGrid()`. |
| `placeholder` | Control placeholder. |
| `disabled`, `required`, `readonly` | Control state. |
| `min`, `max`, `step` | Numeric/date control attributes. |
| `autocomplete` | Control autocomplete value. |
| `rows` | Textarea row count. |
| `visible_if`, `disabled_if`, `requires` | Encoded as dependency data attributes for package JS. |

Override keys:

| Key | Purpose |
| --- | --- |
| `name`, `id`, `type`, `component` | Override schema identity or control selection. |
| `option` | Merge into option args last. |
| `control` | Merge into control args last. |

Current use: primitives tab uses a saved switch and text field.

### `card( array $args, callable $content )`

| Option | Default | Notes |
| --- | --- | --- |
| `title` | empty | Card header title. |
| `description` | empty | Card header copy. |
| `class` | empty | Root class. |
| `header_class` | empty | Header wrapper class. |
| `content_class` | empty | Content wrapper class. |

Current use: cards tab renders content cards. This component does not currently support `attributes` or `data_attributes`.

### `form( array $args, callable $content )`

| Option | Default | Accepted values / notes |
| --- | --- | --- |
| `title` | empty | Form group title. |
| `description` | empty | Form group description. |
| `class` | empty | Root class. |
| `header_class` | empty | Header class. |
| `fields_class` | empty | Fields grid class. |
| `variant` | `standard` | `standard`, `plain`. |
| `columns` | `2` | `1`, `2`, `3`, `auto`. |
| `actions_align` | `right` | `left`, `right`, `between`. |
| `fields` | `array()` | Generated fields. Each field passes through `generatedField()`. |
| `actions` | `array()` | Buttons using `button()` args. |
| `classes` | `array()` | Slot classes: `header`, `fields`. |
| `attributes` | `array()` | Root attributes. Default role is `group`. |
| `data_attributes` | `array()` | Root data attributes. |

Generated field types: `textarea`, `select`, `choice`, `switch`, `boolean`, `bool`, `color_picker`, `colorPicker`, `media`, `media_field`, `mediaField`, and all `input()` types.

Current use: compound tab renders a generated multi-field notification form with ghost and primary actions.

### `field( string $label, callable $control, array $args = array() )`

| Option | Default | Notes |
| --- | --- | --- |
| `class` | empty | Root label class. |
| `label_class` | empty | Label text class. |
| `wide` | `false` | Adds `is-wide mwp-field-grid__wide`. |
| `classes` | `array()` | Slot class: `label`. |
| `attributes` | `array()` | Root attributes. |
| `data_attributes` | `array()` | Root data attributes. |

Current use: schedule and field-grid examples.

### `fieldGrid( array $args, callable $content )`

| Option | Default | Accepted values |
| --- | --- | --- |
| `columns` | `2` | `1`, `2`, `3`, `4`, `auto`. |
| `density` | `comfortable` | `compact`, `comfortable`. |
| `class` | empty | Root class. |
| `attributes` | `array()` | Root attributes. |
| `data_attributes` | `array()` | Root data attributes. |

Current use: schedule row uses `columns => 3`, `density => compact`.

## Form Controls

### `switch( array $args )`

| Option | Default | Notes |
| --- | --- | --- |
| `id` | empty | Input id. |
| `name` | empty | Input name. |
| `value` | `null` | Checkbox value. Omitted when null. |
| `checked` | `false` | Checked state. |
| `disabled` | `false` | Disabled state. |
| `class` | empty | Root label class. |
| `input_class` | empty | Input class. |
| `slider_class` | empty | Slider span class. |
| `attributes` | `array()` | Root attributes. |
| `input_attrs` | `array()` | Input attributes. |
| `data_attributes` | `array()` | Root data attributes. |

Current use: via `schemaOption()` and locked premium raw markup.

### `input( array $args )`

| Option | Default | Notes |
| --- | --- | --- |
| `type` | `text` | Unknown types normalize to `text`. |
| `id` | empty | Input id. |
| `name` | empty | Input name. |
| `value` | empty | Input value. |
| `placeholder` | empty | Placeholder. |
| `class` | empty | Input class. |
| `disabled` | `false` | Disabled state. |
| `readonly` | `false` | Readonly state. |
| `required` | `false` | Required state. |
| `min` | `null` | Input min. |
| `max` | `null` | Input max. |
| `step` | `null` | Input step. |
| `autocomplete` | empty | Autocomplete attr. |
| `attributes` | `array()` | Input attributes. |
| `data_attributes` | `array()` | Input data attributes. |

Supported types: `text`, `number`, `url`, `email`, `password`, `search`, `color`, `tel`, `hidden`, `date`, `time`, `datetime-local`, `month`, `week`.

Current use: text, number, url, email, datetime-local, search, disabled text.

### `textarea( array $args )`

| Option | Default |
| --- | --- |
| `name` | empty |
| `id` | empty |
| `value` | empty |
| `placeholder` | empty |
| `class` | empty |
| `rows` | `4` |
| `disabled` | `false` |
| `readonly` | `false` |
| `required` | `false` |
| `autocomplete` | empty |
| `attributes` | `array()` |
| `data_attributes` | `array()` |

Current use: wide textarea and notes field.

### `select( array $args )`

| Option | Default | Notes |
| --- | --- | --- |
| `name` | empty | Select name. |
| `id` | empty | Select id. |
| `value` | empty | Selected value. |
| `options` | `array()` | Value => label map. |
| `class` | empty | Select class. |
| `option_class` | empty | Class applied to every option. |
| `option_classes` | `array()` | Value => option class map. |
| `disabled` | `false` | Disabled state. |
| `required` | `false` | Required state. |
| `attributes` | `array()` | Select attributes. |
| `data_attributes` | `array()` | Select data attributes. |

Current use: schedule timezone, status selects, option class customization.

### `button( array $args )`

| Option | Default | Accepted values / notes |
| --- | --- | --- |
| `label` | empty | Button text. |
| `type` | `button` | `button`, `submit`, `reset`. |
| `variant` | `secondary` | `primary`, `secondary`, `ghost`, `danger`. |
| `size` | `standard` | `standard`, `compact`. |
| `icon` | empty | Named icon or SVG. |
| `icon_position` | `before` | `before`, `after`. |
| `class` | empty | Button class. |
| `disabled` | `false` | Disabled state. |
| `loading` | `false` | Adds `is-loading`, disables button, and sets `data-mwp-loading`. |
| `key` | empty | Rendered as a `key` attribute when present. |
| `attributes` | `array()` | Button attributes. |
| `data_attributes` | `array()` | Button data attributes. |

Current use: all variants, compact size, disabled state, icons `play`, `edit`, `copy`, and `icon_position => after`.

### `badge( array $args )`

| Option | Default | Accepted values |
| --- | --- | --- |
| `label` | empty | Badge text. |
| `variant` | `neutral` | `neutral`, `primary`, `warning`, `success`. |
| `size` | `standard` | `standard`, `compact`. |
| `class` | empty | Root class. |
| `attributes` | `array()` | Root attributes. |
| `data_attributes` | `array()` | Root data attributes. |

Current use: all variants and compact size.

### `notice( array $args )`

| Option | Default | Accepted values |
| --- | --- | --- |
| `message` | empty | Notice text. |
| `variant` | `success` | `success`, `error`, `warning`, `info`. |
| `class` | empty | Root class. |
| `attributes` | `array()` | Root attributes. |
| `data_attributes` | `array()` | Root data attributes. |

Current use: all variants.

## Compound Input Components

### `colorPicker( array $args )`

| Option | Default | Notes |
| --- | --- | --- |
| `name` | empty | Text input name. |
| `value` | `#059669` | Color value. |
| `label` | empty | Currently accepted but not rendered. |
| `placeholder` | `#000000` | Text input placeholder. |
| `class` | empty | Root class. |
| `swatch_class` | empty | Color input class. |
| `input_class` | empty | Text input class. |
| `disabled` | `false` | Disables both inputs. |

Current use: compound tab with default value.

### `inputButton( array $args )`

| Option | Default | Notes |
| --- | --- | --- |
| `input` | `array()` | Args for `input()`. |
| `button` | `array()` | Args for `button()`. |
| `class` | empty | Root class. |
| `wide` | `false` | Adds full-width class. |

Current use: url test button and full-width search pattern.

### `radioGroup( array $args )`

| Option | Default |
| --- | --- |
| `name` | empty |
| `value` | empty |
| `options` | `array()` |
| `class` | empty |
| `item_class` | empty |
| `input_class` | empty |
| `label_class` | empty |

If no `name` is supplied, a generated name is used and inputs get `data-mwp-ignore-autosave="true"`.

Current use: interactive tab mode selector.

### `buttonGroup( array $args )`

| Option | Default |
| --- | --- |
| `buttons` | `array()` |
| `class` | empty |

Each button is passed to `button()`.

Current use: interactive tab segmented day/week/month buttons.

### `mediaField( array $args )`

| Option | Default | Accepted values / notes |
| --- | --- | --- |
| `name` | empty | Hidden input name. |
| `value` | `0` | Attachment id. |
| `mode` | `compact` | `compact`, `logo`, `wide`, `button_only`. |
| `preview_size` | `thumbnail` | WP image size. |
| `preview_height` | empty | Sets `--mwp-media-preview-height`. |
| `preview_ratio` | empty | Sets `--mwp-media-preview-ratio`. |
| `library_type` | `image` | Media library type. |
| `button_text` | `Select Image` | Choose button label. |
| `remove_text` | `Remove Image` | Remove button label. |
| `media_title` | `Select Image` | WP media frame title. |
| `media_button` | `Use Image` | WP media frame button. |
| `placeholder` | `No image selected` | Accepted but currently not rendered by default preview. |
| `class` | empty | Root class. |
| `preview_class` | empty | Preview wrapper class. |
| `actions_class` | empty | Actions wrapper class. |
| `input_class` | empty | Accepted for hidden input class, but default markup currently builds it and does not apply it to the hidden input. |
| `choose_class` | empty | Choose button class. |
| `remove_class` | empty | Remove button class. |
| `choose_icon` | `image` | Named icon or SVG. |
| `remove_icon` | `trash` | Named icon or SVG. |
| `data_attributes` | `array()` | Root data attrs. |
| `attributes` | `array()` | Root attrs. |
| `slots` | `array()` | Slot map: `before`, `after`, `preview`, `actions`. |
| `before`, `after`, `preview`, `actions` | `null` | Callable or safe markup slots. |

Filters:

- `matterwp_admin_ui_media_field_args`
- `matterwp_admin_ui_media_field_preview`
- `matterwp_admin_ui_media_field_actions`

Events:

- `mwp:media-selected`
- `mwp:media-removed`

Current use: compact image selector.

## Overlay Components

### `modal( array $args, callable $content )`

| Option | Default | Accepted values / notes |
| --- | --- | --- |
| `id` | generated | Modal id. |
| `title` | empty | Modal title. |
| `trigger` | `Open Modal` | Trigger label. Set `false` to suppress. |
| `render_trigger` | `true` | Set false for caller-owned trigger. |
| `trigger_variant` | `primary` | `primary`, `secondary`, `ghost`, `danger`. |
| `class` | empty | Modal root class. |
| `trigger_class` | empty | Trigger button class. |
| `dialog_class` | empty | Dialog class. |
| `header_class` | empty | Header class. |
| `content_class` | empty | Content class. |
| `footer_class` | empty | Footer class. |
| `fields_class` | empty | Generated fields wrapper class. |
| `footer` | empty | Safe footer HTML when no `footer_actions` are provided. |
| `footer_actions` | `array()` | Array of footer action button args. |
| `fields` | `array()` | Generated modal field definitions. |
| `classes` | `array()` | Accepted but currently not used by modal slots. |
| `attributes` | `array()` | Modal root attrs. |
| `data_attributes` | `array()` | Modal root data attrs. |

Generated modal field keys:

| Key | Purpose |
| --- | --- |
| `name`, `key`, `id`, `type`, `label`, `title` | Field identity and type. |
| `value`, `checked`, `placeholder`, `autocomplete` | Control values and hints. |
| `min`, `max`, `step` | Input attrs. |
| `readonly`, `required`, `disabled` | Control state. |
| `options`, `choices` | Select choices. |
| `field_class`, `wide`, `layout` | Field wrapper class shaping. |
| `field_attributes`, `field_data_attributes` | Field wrapper attrs. |
| `attributes`, `data_attributes` | Control attrs. |
| `switch_attributes`, `input_attrs` | Switch-specific attrs. |

Footer action keys: `label`, `id`, `type`, `variant`, `size`, `icon`, `icon_position`, `class`, `disabled`, `loading`, `autofocus`, `data_attributes`, `attributes`.

Current use:

- Triggered modal with ghost cancel and primary continue.
- Triggerless edit modals opened by custom buttons/table actions.
- Generated fields for text and select.

### `confirmDialog( array $args )`

| Option | Default | Accepted values / notes |
| --- | --- | --- |
| `id` | generated | Modal id. |
| `title` | empty | Dialog title. |
| `trigger` | `Delete` | Trigger label. |
| `render_trigger` | `true` | Suppress generated trigger when false. |
| `trigger_variant` | `danger` | `primary`, `secondary`, `ghost`, `danger`. |
| `confirm_label` | `Confirm` | Confirm button label. |
| `confirm_variant` | `danger` | `primary`, `secondary`, `ghost`, `danger`. |
| `cancel_label` | `Cancel` | Cancel button label. |
| `class` | empty | Modal root class. |
| `trigger_class` | empty | Trigger class. |
| `dialog_class` | empty | Dialog class. |
| `header_class` | empty | Header class. |
| `footer_class` | empty | Footer class. |

Current use: delete confirmation example.

## Data And Display Components

### `table( array $args )`

| Option | Default | Notes |
| --- | --- | --- |
| `columns` | `array()` | Column definitions. |
| `rows` | `array()` | Row data. |
| `class` | empty | Wrapper class. |
| `table_class` | empty | Table class. |
| `classes` | `array()` | Slot class: `table`. |
| `attributes` | `array()` | Wrapper attrs. |
| `table_attributes` | `array()` | Table attrs. |
| `data_attributes` | `array()` | Wrapper data attrs. |
| `row_id` | empty | Row data key used as `<tr id>`. |
| `row_key` | empty | Row data key used for `data-row-key` and `data-mwp-row-id`. |
| `row_class` | empty | Class on every row. |
| `row_attributes` | `array()` | Attrs on every row. |
| `row_data_attributes` | `array()` | Data attrs on every row. |

Current use: simple compound table.

### `paginatedTable( array $args )`

Includes all `table()` args plus:

| Option | Default | Notes |
| --- | --- | --- |
| `per_page` | `10` | Client-side page size. |
| `current_page` | `1` | Current page. |
| `total` | `0` | Defaults to row count. |
| `pagination_class` | empty | Pagination wrapper class. |
| `prev_class`, `next_class`, `info_class` | empty | Pagination slot classes. |
| `empty_selector` | empty | External empty-state selector. |
| `empty_target` | empty | Empty-state id. |
| `initial_page` | empty | `first`, `last`, or page number. |
| `empty_state` | `null` | Args for `emptyState()`. |
| `pagination` | `true` | Render pagination controls. |

Current use: through `dataTable()`.

### `dataTable( array $args )`

Wrapper. If `pagination` is omitted or truthy, calls `paginatedTable()`. If `pagination => false`, calls `table()`.

Column definitions:

| Key | Default | Notes |
| --- | --- | --- |
| `key` | column array key | Row value key. |
| `label` | key | Header label. |
| `type` | `text` | `text`, `title`, `badge`, `link`, `external_link`, `code`, `image`, `date`, `actions`. |
| `callback` | none | Callable receives `( $value, $row, $column )`. |
| `wrap` | `false` | Adds wrapping class. |
| `overflow` | empty | Styled values: `anywhere`, `truncate`. |
| `min_width`, `max_width`, `width` | empty | Inline cell style. |
| `vertical_align` | empty | Inline cell style. |
| `cell_class` | empty | Extra cell class. |
| `badge` | `array()` | Args for `badgeCell()` on `badge` columns. |
| `url`, `url_key` | empty / `url` | Link target for link columns. |
| `external` | false | External link behavior. Forced true for `external_link`. |
| `alt` | empty | Image alt for image columns. |
| `format` | empty | `relative_site_url` supported. |
| `strip_site_url` | false | Strip current site URL from display text. |
| `display_callback` | none | Callable receives `( $value, $args )`. |

Row definitions may include `row_class`, `row_attributes`, and `row_data_attributes`.

Current use: data tab uses typed title, code, external link, badge, date, and actions columns, `row_key`, `per_page`, empty state, max width, `overflow => anywhere`, `strip_site_url`, and `format => relative_site_url`.

### `actionGroup( array $args )`

| Option | Default |
| --- | --- |
| `actions` | `array()` |
| `class` | empty |
| `attributes` | `array()` |
| `data_attributes` | `array()` |

Action item keys:

| Key | Default | Notes |
| --- | --- | --- |
| `label` | empty | Also used as `aria-label`. |
| `icon` | empty | Named icon or SVG. If empty, lowercased label is tried as icon name. |
| `url` | empty | Renders `<a>` when present. |
| `variant` | `normal` | `normal`, `danger`. |
| `class` | empty | Action class. |
| `type` | `button` | `button`, `submit`, `reset`. |
| `disabled` | `false` | Button disabled state. |
| `data_attributes` | `array()` | Action data attrs. |
| `attributes` | `array()` | Action attrs. |
| `confirm` | empty | Adds `data-mwp-confirm`. |

Current use: generated by `actionsCell()` in data table, with edit and danger delete actions.

### `emptyState( array $args )`

| Option | Default |
| --- | --- |
| `title` | empty |
| `description` | empty |
| `actions` | `array()`; max 2 rendered |
| `class` | empty |
| `attributes` | `array()` |
| `data_attributes` | `array()` |

Current use: primitives tab and data table empty state.

### `resultCard( array $args )`

| Option | Default | Accepted values / notes |
| --- | --- | --- |
| `title` | empty | Header title. |
| `description` | empty | Description. |
| `image` | empty | Preview image URL. |
| `image_alt` | empty | Preview alt text. |
| `status` | empty | Rendered as success compact badge. |
| `variant` | `standard` | `standard`, `success`, `warning`, `danger`. |
| `meta` | `array()` | Label => safe value rows. |
| `values` | `array()` | Label => safe value rows. |
| `actions` | `array()` | Buttons, default size compact. |
| `primary_link` | `array()` | `url`, `label`, `class`, `attributes`. |
| `class` | empty | Root class. |
| `attributes` | `array()` | Root attrs. |
| `data_attributes` | `array()` | Root data attrs. |

Current use: generated asset preview with image, status, meta, values, primary link, and copy button.

### `keyValueList( array $args )`

| Option | Default |
| --- | --- |
| `items` | `array()` |
| `class` | empty |
| `attributes` | `array()` |
| `data_attributes` | `array()` |

Current use: primitive badged row details.

### `actionBar( array $args )`

| Option | Default | Accepted values |
| --- | --- | --- |
| `actions` | `array()` | Buttons. |
| `align` | `right` | `left`, `right`, `between`. |
| `item_size` | empty | `auto`, `grow`. |
| `class` | empty | Root class. |
| `attributes` | `array()` | Root attrs. |
| `data_attributes` | `array()` | Root data attrs. |

Current use: not used in boilerplate templates, but documented and styled.

### `switchGrid( array $args )`

| Option | Default |
| --- | --- |
| `items` | `array()` |
| `class` | empty |
| `attributes` | `array()` |
| `data_attributes` | `array()` |

Each item supports `title`, `description`, and either `switch` args or direct `switch()` args.

Current use: not used in boilerplate templates.

### `choiceGrid( array $args )`

| Option | Default | Accepted values |
| --- | --- | --- |
| `name` | empty | Input name. |
| `value` | empty | String or array. |
| `options` | `array()` | Value => label or value => `array( 'label', 'description' )`. |
| `type` | `radio` | `radio`, `checkbox`. |
| `class` | empty | Root class. |
| `attributes` | `array()` | Root attrs. |
| `data_attributes` | `array()` | Root data attrs. |

Current use: indirectly available through `schemaOption()` for `multi_select` or `choices_display`.

### `accordion( array $args )`

| Option | Default | Notes |
| --- | --- | --- |
| `items` | `array()` | Each item supports `title`, `content`, `open`. |
| `class` | empty | Root class. |

Current use: interactive tab with one open item.

### `progress( array $args )`

| Option | Default |
| --- | --- |
| `value` | `0`; clamped 0 to 100 |
| `label` | empty |
| `max_width` | empty |
| `grow` | `false` |
| `hidden` | `false` |
| `class` | empty |
| `attributes` | `array()` |
| `data_attributes` | `array()` |

Current use: interactive tab with label, value 68, grow, max width.

### `statCard( array $args )`

| Option | Default |
| --- | --- |
| `label` | empty |
| `value` | empty |
| `class` | empty |

Current use: cards/stats tab dashboard stats. This component does not currently support `attributes` or `data_attributes`.

### `lightbox( array $args )`

| Option | Default |
| --- | --- |
| `src` | empty |
| `alt` | empty |
| `caption` | empty |
| `class` | empty |

Current use: interactive tab image preview. This component does not currently support `attributes` or `data_attributes`.

## Cell Helpers And Utilities

| Helper | Arguments | Notes |
| --- | --- | --- |
| `badgeCell()` | `string $label, $args = array()` | `$args` may be variant string or badge args. |
| `linkCell()` | `string $label, string $url, array $args = array()` | Supports `attributes`, `external`, `class`, `strip_site_url`, `format`, `display_callback`. |
| `codeCell()` | `$value` | Returns escaped `<code>`. |
| `imageCell()` | `string $src, string $alt = '', array $args = array()` | Supports `attributes`, `class`. |
| `dateCell()` | `$value, string $format = ''` | Uses WP date format when format empty. |
| `actionsCell()` | `array $actions` | Wraps `actionGroup()`. |
| `icon()` | `string $icon` | Returns named icon SVG or raw SVG markup. |
| `attrs()` | `array $attributes` | Escaped attr string. |
| `dataAttrs()` | `array $attributes` | Escaped `data-*` attr string. |
| `premiumBadge()` | `array $args` | `label`, `url`, `link_label`. |
| `controlLockedAttrs()` | `bool $locked` | Echoes `disabled data-pro-locked="true"` when locked. |

Named icons:

`edit`, `delete`, `trash`, `x`, `link`, `external-link`, `download`, `copy`, `view`, `image`, `play`, `pause`, `refresh`, `sun`, `moon`.

## Style Variants And Classes

### Root And Theme

| Selector / token | Purpose |
| --- | --- |
| `#mwp-settings`, `.mwp-admin-app` | Main app scope. |
| `is-layout-standard`, `is-layout-fullscreen` | App layout classes from `app()`. |
| `is-full-width` | App full-width modifier. |
| `html[data-mwp-theme="dark"]` | Dark mode scope. |
| `mwp-theme-dark` | Class mirrored on `<html>` by JS/bootstrap. |

Important CSS variables:

- Brand: `--mwp-ui-primary`, `--mwp-ui-primary-hover`, `--mwp-ui-radius`, `--mwp-ui-border`, `--mwp-ui-bg`.
- Focus: `--mwp-focus-ring-color`.
- Nav: `--mwp-nav-active-bg`, `--mwp-nav-active-x`, `--mwp-nav-active-y`, `--mwp-nav-active-width`, `--mwp-nav-active-height`.
- Buttons: `--mwp-action-primary-bg`, `--mwp-action-primary-bg-hover`, `--mwp-action-primary-border`, `--mwp-action-primary-text`.
- Status: `--mwp-color-success-*`, `--mwp-color-danger-*`, `--mwp-color-warning-*`, `--mwp-color-info-*`.
- Media: `--mwp-media-preview-height`, `--mwp-media-preview-ratio`.

### Section And Option Styles

| Class | Source / use |
| --- | --- |
| `is-section-wrapped` | Default wrapped section chrome. |
| `is-section-minimal` | Minimal section chrome for direct table/card/stat/result layouts. Used in data, cards, stats, empty state, and result examples. |
| `is-option-variant-standard` | Default option-row chrome. |
| `is-option-variant-minimal` | Minimal option rows without row chrome or padding. |
| `is-layout-row` | Default option row. |
| `is-layout-column` | Column option row. Used heavily in examples. |
| `is-layout-divided` | Divided option row. Used in primitive/forms examples. |
| `is-label-width-standard`, `is-label-width-full` | Option label width. |
| `is-control-width-narrow`, `is-control-width-standard`, `is-control-width-wide`, `is-control-width-full` | Option control width. |
| `is-align-center`, `is-align-start`, `is-align-stretch` | Option alignment. |
| `has-input-label`, `has-no-input-label` | Option label presence. |

### Control Styles

| Class | Component |
| --- | --- |
| `.mwp-button.is-primary` | Primary button. |
| `.mwp-button.is-secondary` | Secondary button. Secondary is mostly default base style plus class. |
| `.mwp-button.is-ghost` | Ghost button. |
| `.mwp-button.is-danger` | Danger button. |
| `.mwp-button.is-compact` | Compact button. |
| `.mwp-button.is-loading` | Loading button. |
| `.mwp-button.has-icon.is-icon-before` | Button with icon before label. |
| `.mwp-button.has-icon.is-icon-after` | Button with icon after label. |
| `.mwp-badge.is-primary` | Primary badge. |
| `.mwp-badge.is-warning` | Warning badge. |
| `.mwp-badge.is-success` | Success badge. |
| `.mwp-badge.is-compact` | Compact badge. |
| `.mwp-inline-notice.is-success` | Success inline notice. |
| `.mwp-inline-notice.is-info` | Info inline notice. |
| `.mwp-inline-notice.is-warning` | Warning inline notice. |
| `.mwp-inline-notice.is-error` | Error inline notice. |
| `.mwp-switch` | Switch root. |
| `.mwp-color-picker` | Color picker root. |
| `.mwp-input-button.is-full-width` | Full-width input-button. |

### Form And Grid Styles

| Class | Component |
| --- | --- |
| `.mwp-form.is-standard` | Standard form. |
| `.mwp-form.is-plain` | Plain form. |
| `.mwp-form.has-1-columns`, `.has-2-columns`, `.has-3-columns`, `.has-auto-columns` | Form generated field columns. |
| `.mwp-form.has-actions-left`, `.has-actions-right`, `.has-actions-between` | Action alignment. |
| `.mwp-field-grid--one`, `--two`, `--three`, `--four`, `--auto` | Field grid columns. |
| `.mwp-field-grid--compact`, `--comfortable` | Field grid density. |
| `.mwp-field-grid__wide`, `.mwp-field.is-wide` | Full-row field in grid. |

### Table Styles

| Class | Component |
| --- | --- |
| `.mwp-table-wrap`, `.mwp-table` | Table wrappers. |
| `.mwp-pagination`, `.mwp-pagination__info` | Client pagination. |
| `.mwp-table-actions` | Table action group. |
| `.mwp-table-action.is-normal`, `.mwp-table-action.is-danger` | Table action variants. |
| `.mwp-table-cell.is-type-text`, `.is-type-title`, `.is-type-link`, `.is-type-external_link`, `.is-type-code`, `.is-type-image`, `.is-type-date`, `.is-type-actions`, `.is-type-badge` | Type cell classes. |
| `.mwp-table-cell.is-wrap` | Wrap cells. |
| `.mwp-table-cell.has-overflow-anywhere` | Break long text anywhere. |
| `.mwp-table-cell.has-overflow-truncate` | Truncate long text. |

### Media Styles

| Class | Component |
| --- | --- |
| `.mwp-media-field.is-compact` | Compact media mode. Used currently. |
| `.mwp-media-field.is-logo` | Logo mode. |
| `.mwp-media-field.is-wide` | Wide mode. |
| `.mwp-media-field.is-button-only` | Button-only mode. |
| `.mwp-media-field.has-image` | Root has selected image. |
| `.mwp-media-field.is-empty` | Root has no image. |
| `.mwp-media-field__preview.has-image` | Preview has image. |

### Other Display Styles

| Class | Component |
| --- | --- |
| `.mwp-card` | Card. |
| `.mwp-stat` | Stat card. |
| `.mwp-empty-state` | Empty state. |
| `.mwp-result-card.is-standard`, `.is-success`, `.is-warning`, `.is-danger` | Result card variants. |
| `.mwp-action-bar.is-align-left`, `.is-align-right`, `.is-align-between` | Action bar alignment. |
| `.mwp-action-bar.has-item-auto`, `.has-item-grow` | Action bar item sizing. |
| `.mwp-choice-grid.is-radio`, `.is-checkbox` | Choice grid types. |
| `.mwp-progress.is-grow` | Grow progress. |
| `.mwp-modal.is-danger` | Danger modal from confirm dialog. |

## JavaScript Contracts

### Browser API

`window.MatterAdminUI` exposes:

| API | Purpose |
| --- | --- |
| `MatterAdminUI.openModal(idOrElement, values, trigger)` | Opens a package modal and optionally populates fields. |
| `MatterAdminUI.closeModal(idOrElement)` | Closes a modal. |
| `MatterAdminUI.populateModal(idOrElement, values)` | Populates fields by `name` or `data-mwp-field`. |
| `MatterAdminUI.notice(message, type, options)` | Shows package-managed notice with stacked enter/exit animation. |
| `MatterAdminUI.ajax.submit(formOrRoot, options)` | Submits Ajax form/root. |
| `MatterAdminUI.table.appendRow(table, rowHtmlOrData, options)` | Appends a table row. |
| `MatterAdminUI.table.updateRow(table, rowId, rowHtmlOrData)` | Replaces a table row. |
| `MatterAdminUI.table.removeRow(table, rowId)` | Removes a table row. |
| `MatterAdminUI.table.refresh(table, options)` | Refreshes pagination and empty states. |

### Data Attributes

| Attribute | Purpose |
| --- | --- |
| `data-ui-tab`, `data-ui-panel` | Tab trigger and panel. |
| `data-mwp-theme-toggle` | Theme toggle. |
| `data-mwp-visible-if`, `data-mwp-disabled-if`, `data-mwp-requires` | Dependency behavior. |
| `data-mwp-color-picker`, `data-mwp-color-swatch`, `data-mwp-color-input` | Color picker sync. |
| `data-mwp-modal`, `data-mwp-modal-trigger`, `data-mwp-modal-close`, `data-mwp-field`, `data-mwp-autofocus` | Modal behavior. |
| `data-mwp-lightbox-trigger`, `data-mwp-lightbox-src`, `data-mwp-lightbox-alt`, `data-mwp-lightbox-caption`, `data-mwp-lightbox-close` | Lightbox behavior. |
| `data-mwp-accordion`, `data-mwp-accordion-trigger`, `data-mwp-accordion-content` | Accordion behavior. |
| `data-mwp-confirm` | Browser confirm behavior for actions. |
| `data-mwp-ajax-form`, `data-mwp-action`, `data-mwp-submit`, `data-mwp-loading` | Ajax forms. |
| `data-mwp-paginated-table`, `data-mwp-pagination`, `data-mwp-page`, `data-mwp-row-id`, `data-row-key` | Table pagination and row mutation. |
| `data-media-field`, `data-media-target`, `data-media-input`, `data-media-preview`, `data-media-remove` | Media field behavior. |
| `data-log-viewer`, `data-log-filter`, `data-log-search`, `data-log-level` | Log viewer behavior. |

### Events

| Event | Purpose |
| --- | --- |
| `mwp:ajax-before` | Before Ajax submit. |
| `mwp:ajax-success` | Ajax success. |
| `mwp:ajax-error` | Ajax error. |
| `mwp:ajax-complete` | Ajax complete. |
| `mwp:table-refresh` | Manual table refresh. |
| `mwp:media-selected` | Media item selected. |
| `mwp:media-removed` | Media item removed. |

## Current Boilerplate Usage Matrix

| Component | Current usage |
| --- | --- |
| `app()` | Main admin shell in `templates/admin/settings.php`. |
| `panel()` | All admin tabs. |
| `section()` | Wrapped sections plus `section_variant => minimal` for cards, stats, result cards, empty states, and tables. |
| `option()` | Most rows; uses row, column, divided, full label, control widths, badges, help, actions. |
| `schemaOption()` | Saved switch and text option in primitives tab. |
| `card()` | Cards tab. |
| `form()` | Compound tab generated form. |
| `field()` | Field-grid examples. |
| `fieldGrid()` | Schedule and form layout examples. |
| `switch()` | Via schema and locked markup. |
| `input()` | Text, number, URL, email, datetime-local, search, disabled examples. |
| `textarea()` | Long content and notes examples. |
| `select()` | Status, role, interval, timezone examples. |
| `button()` | Primary, secondary, ghost, danger, compact, disabled, icon-before, and icon-after buttons. |
| `badge()` | Neutral, primary, warning, success, compact badges. |
| `notice()` | Success, info, warning, error notices. |
| `modal()` | Standard modal and triggerless edit modals. |
| `confirmDialog()` | Delete confirmation example. |
| `lightbox()` | Icon preview. |
| `accordion()` | Interactive FAQ example. |
| `table()` | Simple component table. |
| `dataTable()` | Paginated typed table with actions and empty state. |
| `resultCard()` | Generated asset preview. |
| `keyValueList()` | Primitive metadata row. |
| `emptyState()` | Primitive empty block and table empty state. |
| `progress()` | Migration progress example. |
| `statCard()` | Four dashboard stat cards. |
| `colorPicker()` | Color field example. |
| `inputButton()` | URL/action and full-width search examples. |
| `radioGroup()` | Segmented mode choice. |
| `buttonGroup()` | Segmented command set. |
| `mediaField()` | Compact media picker. |
| `controlLockedAttrs()` | Locked premium raw controls. |
| `actionBar()`, `switchGrid()`, `choiceGrid()` | Available but not directly used in current templates. |

## Changelog

### Unreleased

- Renamed section layout args: `variant` became `section_variant`, and `option_box` became `option_variant`.
- Replaced section `borderless` and `table-only` variants with `section_variant => 'minimal'`; direct table, stat, card, empty-state, and result-card layouts now use one minimal section model.
- Tightened `option_variant => 'minimal'` so `.mwp-option-info`, `.mwp-option-input`, and nested `.mwp-result-card` roots also render with zero padding.
- Removed legacy `option()` args `wide`, `align_start`, `divider`, and `style`; use `layout`, `align`, `label_width`, and `control_width`.
- Removed legacy option/section style classes from generated markup and SCSS: `is-style-*`, `is-divided`, `is-borderless`, `is-table-only`, and `is-option-box-*`.
- Added `icon_position => 'before'|'after'` to `button()` and modal footer actions, with matching button classes and SCSS.
- Added enter/exit animation and collapsing stack behavior for package-managed notices.
- Updated boilerplate templates to use the new API and added a visible icon-after button example.

### `1.0.3`

- Added admin-shell documentation and support around `app()`: header, tabs, form wrapper, dark-mode toggle, active tab persistence.
- Added richer schema row support: `ui`, `component`, `option`, `control`, `layout`, `control_width`, `media`, `choices_display`, `placeholder`, `help`, `visible_if`, `disabled_if`, and `requires`.
- Expanded input support to schedule/date controls: `date`, `time`, `datetime-local`, `month`, `week`.
- Added triggerless modal support with `render_trigger => false` and `trigger => false`.
- Added generated modal fields and footer actions with control attributes, data attributes, readonly/required/min/max/step/autocomplete, loading, icon, and autofocus support.
- Added richer table/data-table API: row keys, row attributes, typed columns, callbacks, empty states, pagination, widths, wrapping, overflow, relative site URL display.
- Added JavaScript table mutation helpers on `MatterAdminUI.table`.
- Added Ajax form helper and standard Ajax lifecycle events.
- Added `resultCard()`, `emptyState()`, `actionBar()`, `switchGrid()`, `choiceGrid()`, `keyValueList()`.
- Added compact badge size and additional icons: `x`, `trash`, `external-link`, `image`, `play`, `pause`, `refresh`.
- Added media field slots, filters, CSS sizing variables, and events.
- Added package-managed notice API and notice styling.

### `1.0.2`

- Added section variants for `borderless` and `table-only`.
- Added option row variants for divided and column layouts.
- Added compact table actions and static table pagination.
- Improved support for hidden states and dynamic modal helpers.
- Standardized more components around `class`, `attributes`, `data_attributes`, and slot classes.

### `1.0.1` and earlier

- Initial reusable admin UI package surface.
- Base layout helpers: `panel()`, `section()`, `option()`, `card()`, `form()`, `field()`.
- Base controls: `input()`, `textarea()`, `select()`, `switch()`, `button()`, `badge()`, `notice()`.
- Base interactive/data helpers: `modal()`, `lightbox()`, `accordion()`, `table()`, `progress()`, `statCard()`, `colorPicker()`, `inputButton()`, `radioGroup()`, `buttonGroup()`.

## Known API Gaps

- `card()`, `statCard()`, `lightbox()`, `accordion()`, `buttonGroup()`, `radioGroup()`, `inputButton()`, and `colorPicker()` do not fully support the shared `attributes` and `data_attributes` contract yet.
- `modal()` accepts `classes`, but current implementation uses legacy slot class keys instead of the slot map.
- `colorPicker()` accepts `label`, but does not render it.
- `mediaField()` accepts `placeholder`, but the default preview markup does not render placeholder copy.
- `mediaField()` computes `input_class`, but default hidden input markup currently does not apply it.
- `confirmDialog()` has a footer-only dialog body. It is suited for terse confirmations, not explanatory content.
