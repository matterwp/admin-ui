# next update to-dos

## Documentation

- Package rules and philosophy live in `RULES.md`. Read and follow that file before changing component APIs, markup, styles, JavaScript services, or boilerplate examples.
- Component API docs live in `COMPONENTS.md`. Update that file whenever public component args, style variants, JavaScript data attributes, or boilerplate usage examples change.
- Keep the changelog in `COMPONENTS.md` aligned with this file.

## v1.0.4 release

- Added preferred `section()` args `section_variant` and `option_variant`; deprecated `variant` and `option_box` remain supported throughout the `1.x` line.
- Replaced section `borderless` / `table-only` variants with `section_variant => 'minimal'`; direct table-only, stat-card-only, card, empty-state, and result-card layouts should use the same minimal section variant.
- Tightened `option_variant => 'minimal'` so option rows, `.mwp-option-info`, `.mwp-option-input`, and nested `.mwp-result-card` roots are all padding-free.
- Added preferred `option()` args `layout`, `align`, `label_width`, and `control_width`; deprecated `wide`, `align_start`, `divider`, and `style` remain supported throughout the `1.x` line.
- Removed legacy generated CSS hooks for option/section layouts: `is-style-*`, `is-divided`, `is-borderless`, `is-table-only`, and `is-option-box-*`.
- Added `icon_position => 'before'|'after'` to `button()` and modal footer actions, with matching SCSS.
- Added enter/exit animation and collapsing stack behavior for package-managed notices.
- Updated boilerplate templates to exercise `section_variant => 'minimal'`, `layout`, `align`, and `icon_position => 'after'`.

These items came from QR Factory admin-screen customizations. Consider moving them into the Admin UI package so future plugin screens can use them without plugin-specific CSS/JS.

## v1.0.4 updates

Maintainer's Activation Schedule option shows a common scheduling row pattern: start datetime, end datetime, timezone select, and an auto-disable switch. Admin UI can compose most of this today with `option()` + `fieldGrid()`, but date/time inputs still require raw HTML because `Controls::input()` downgrades unsupported types to `text`.

### Highest value

- Use `section_variant => 'minimal'` for table-only sections.

  - QR Factory exposed a border consistency issue in the Generator tab when a `.mwp-table-wrap` was nested inside an Ajax refresh host.
  - Problem structure:
    ```html
    <div class="mwp-section-options">
        <div class="plugin-table-host">
            <div class="mwp-table-wrap">...</div>
        </div>
    </div>
    ```
  - Wrapped section layout CSS treats direct children as normal option rows through `.mwp-section-block.is-section-wrapped.is-option-variant-standard .mwp-section-options > *:not(.mwp-premium-badge)`, so Ajax table hosts can receive row borders/background if the section is not minimal.
  - The nested `.mwp-table-wrap` then also has its own border, producing a double-bordered/inconsistent table compared with direct table sections.
  - Current package contract: render table-only layouts in a minimal section, and use `.mwp-table-host` and/or `[data-mwp-table-host]` only if a plugin needs a stable Ajax host.
  - Example SCSS for plugin-owned table hosts:
    ```scss
    .mwp-section-block.is-section-minimal {
        .mwp-section-options {
            > .mwp-table-host,
            > [data-mwp-table-host] {
                position: static;
                z-index: auto;
                border: 0;
                border-inline: 0;
                border-radius: 0;
                background: transparent;
            }
        }
    }
    ```
  - Include dark-mode coverage only when plugin-specific host styling adds borders/background.
  - Document usage for Ajax/refreshed tables:
    ```html
    <div class="mwp-table-host" data-mwp-table-host>
        <div class="mwp-table-wrap">...</div>
    </div>
    ```
  - Long-term improvement: let `dataTable()` / `paginatedTable()` render a stable Ajax host or accept a `host_attributes` / `host_data_attributes` option, so plugins do not need custom outer wrappers.
  - Add this pattern to Boilerplate's data-table example, especially for tables that are replaced after Ajax responses.

- Expand input type support for schedule/date controls.

  - Add `date`, `time`, `datetime-local`, `month`, and `week` to `Controls::input()`.
  - Add the same allowed types to generated fields in `form( array( 'fields' => ... ) )` and `schemaOption()`.
  - Preserve existing sanitization and attribute support: `min`, `max`, `step`, `readonly`, `required`, `autocomplete`, `disabled`, `attributes`, and `data_attributes`.
  - This lets plugins render schedule fields without raw `<input type="datetime-local">` markup.

- Add or document a schedule-row composition pattern.
  - Target structure: section -> full-width/column option row -> 3-column field grid -> start datetime, end datetime, timezone select.
  - Pair with a second standard option row for an auto-disable switch.
  - The pattern should work with locked/pro controls through `MatterAdminUI::controlLockedAttrs()` or standard `disabled` args.
  - Consider a small helper only if this pattern repeats across plugins; otherwise document the `option()` + `fieldGrid()` recipe.

## v1.0.3 updates

QR Factory is now on Admin UI v1.0.2. That update removed most of the low-level styling debt, but the plugin still needs too much hand-written PHP and JS for common admin workflows. v1.0.3 should focus on component composition, dynamic tables, Ajax-friendly forms, and app-shell helpers.

### Highest value

- Add a triggerless modal render option.

  - Current `MatterAdminUI::modal()` always renders a trigger button before the modal.
  - QR Factory campaign rows already use table action buttons as triggers, so the modal shell is still hand-written in `templates/admin/tabs/campaigns.php`.
  - Suggested API: `render_trigger => false` or `trigger => false`.
  - Keep existing trigger behavior as the default.
  - Support all existing modal args: `fields`, `footer_actions`, slot classes, `attributes`, and `data_attributes`.
  - This lets plugins render reusable edit modals while opening them from table actions, cards, menus, or custom JS.

- Improve modal field and footer action customization.

  - Modal fields need `data_attributes`, `attributes`, `readonly`, `required`, `placeholder`, `autocomplete`, `min`, `max`, `step`, `field_class`, and `layout` support consistently.
  - Footer actions need `attributes`, `data_attributes`, `id`, loading/disabled state classes, and optional icon support.
  - QR Factory campaign editing needs fields with `data-qrfactory-edit-*`, `data-mwp-field`, `readonly`, and Ajax save hooks.
  - A structured modal form should be able to replace the whole campaign edit modal without losing plugin-specific hooks.

- Add a richer `dataTable` / `paginatedTable` API for plugin-managed rows.

  - Existing table helpers are useful for static rows, but QR Factory still hand-renders Library, Campaigns, Analytics, and Logs tables.
  - Add row-level args: `row_id`, `row_key`, `row_attributes`, `row_data_attributes`, and `row_class`.
  - Add column callbacks with access to the full row and column definition.
  - Add column types for common cells: `text`, `title`, `link`, `external_link`, `code`, `image`, `badge`, `date`, `actions`.
  - Add `empty_state`, `pagination`, `per_page`, `initial_page`, `empty_target`, and `table_class` in one component call.
  - Support cell wrapping/truncation options, e.g. `wrap => true`, `min_width`, `max_width`, and `overflow => anywhere`.
  - This would remove repeated table wrapper, header, tbody, pagination, and empty-state markup from QR Factory.

- Add a JS row-rendering contract for dynamic tables.

  - QR Factory duplicates the Campaign table row in PHP and in `src/Admin/Static/js/modules/management.js`.
  - Provide a package helper for appending/updating/removing rows after Ajax without rebuilding the whole table by hand.
  - Suggested browser API:
    - `MatterAdminUI.table.appendRow(table, rowHtmlOrData, options)`
    - `MatterAdminUI.table.updateRow(table, rowId, rowHtmlOrData)`
    - `MatterAdminUI.table.removeRow(table, rowId)`
    - `MatterAdminUI.table.refresh(table, { page: 'first' | 'last' | number })`
  - Keep `mwp:table-refresh`, but make empty-state toggling and pagination refresh automatic.
  - Ideally expose a PHP row partial pattern or serialized column definitions so PHP and JS do not diverge.

- Add an Ajax action/form helper.

  - QR Factory repeats fetch/FormData/nonce/error/disabled-button handling in generator, campaigns, library deletes, campaign update, and bulk generation.
  - Suggested data API: `data-mwp-ajax-form`, `data-mwp-action`, `data-mwp-submit`, `data-mwp-result`, `data-mwp-loading`.
  - Suggested JS helper: `MatterAdminUI.ajax.submit(formOrRoot, options)`.
  - Include standard events: `mwp:ajax-before`, `mwp:ajax-success`, `mwp:ajax-error`, `mwp:ajax-complete`.
  - Let plugins provide callbacks for rendering results, appending rows, or showing notices.
  - This would make plugin JS smaller and more predictable.

- Add a reusable result/preview card component.

  - QR Factory has local `.qrfactory-result-card` CSS and duplicate result renderers in Generator and Campaigns.
  - Suggested component: `MatterAdminUI::resultCard()` or `previewCard()`.
  - Support image preview, title/meta rows, primary link, code/value rows, actions, `status`, `variant`, and responsive layout.
  - Provide JS render support for Ajax responses.
  - This is useful for generated files, exports, uploaded assets, API keys, reports, and other plugin workflows.

- Add an Admin UI app shell helper.
  - QR Factory still hand-renders `#mwp-settings`, header, navigation, version badge, settings form wrapper, save area, and tab panels.
  - Suggested API: `MatterAdminUI::app( array( 'brand' => ..., 'version' => ..., 'tabs' => ..., 'form' => true ), $content )`.
  - Include header slots, nav items, active tab persistence, theme toggle, version display, and full-width layout option.
  - Let plugins pass SVG/logo markup safely, or an attachment/image URL.
  - This reduces boilerplate in every new plugin and keeps tab behavior consistent.

### QR Factory checkup notes

- Campaigns tab is the biggest remaining complexity.

  - Create form, edit modal, server table row, JS-created table row, Ajax create/update/delete, empty-state toggling, and pagination refresh are all managed separately.
  - Admin UI can reduce this with triggerless modals, modal field metadata, data-table row definitions, and Ajax table helpers.

- Library tab is mostly aligned with v1.0.2, but table rendering is still hand-written.

  - It needs row data hooks for deletion, compact badge cells, image cells, external links, and actions.
  - A typed `paginatedTable` with row attributes and action columns would remove most of the template body.

- Generator and Recent QRs need reusable Ajax list handling.

  - Recent logs use server-rendered PHP initially, then JS replaces the whole block with hand-written table markup.
  - Package-level server/Ajax pagination would keep the markup consistent and avoid duplicate table code.

- Logs and Analytics use table markup that repeats the same wrapper/header/body patterns.

  - A data-table component with column type callbacks would make these easier to scan and safer to maintain.
  - Analytics destination/referrer columns need long-text wrapping options built into table cells.

- Design and Settings tabs are close to schema-driven, but still manually compose many rows.

  - `schemaOption()` should support more schema metadata so rows can be generated from `Settings::getSchema()` with small overrides.
  - Needed schema keys: `ui`, `component`, `option`, `control`, `layout`, `control_width`, `media`, `choices_display`, `step`, `placeholder`, `help`, `dependencies`, and `visible_if`.
  - QR Factory fields like logo media, logo size, export size, color pickers, and storage choice should be renderable from schema plus concise overrides.

- Automations and Tools both need better choice-grid primitives.

  - Automations renders one option row per post type; Tools uses `switchGrid`.
  - Add a schema-aware multi-switch option for `multi_select` fields so plugins can render post-type toggles from the schema directly.
  - Support dense/list/card display modes and per-choice descriptions.

- Bulk Generate still needs progress/action layout options.

  - v1.0.2 `actionBar` helped, but progress sizing still needs local CSS: `.mwp-action-bar .mwp-progress`.
  - Add `progress` args such as `max_width`, `grow`, `label`, `value`, `hidden`, and `data_attributes`.
  - Add action-bar item sizing so a progress meter plus button does not need plugin CSS.

- Theme and navigation JS should move closer to the package.

  - QR Factory has local `navigation.js`, `theme-toggle.js`, and `theme-bootstrap.js`.
  - Admin UI should own tab persistence, active indicator measurement, dark-mode storage/bootstrap, and header theme toggle.
  - Plugins should only pass tab definitions and brand data.

- Notices should be a package service, not plugin-specific JS.

  - QR Factory still has a local `showNotice()` wrapper and notice container classes.
  - Admin UI should expose `MatterAdminUI.notice(message, type, options)` and handle container creation, timing, stacking, and dark-mode styles.

- Field markup should be easier to compose.

  - Campaign create/edit forms still use raw `<label class="mwp-field">` blocks for simple text/url/select fields.
  - Add `fieldGrid()` and `field()` args for `wide`, `columns`, `data_attributes`, and generated controls.
  - Let `form()` accept `fields` and `actions`, not only a callback.

- Action icons need one more pass.

  - v1.0.2 added common named icons, but QR Factory still passes a custom X SVG for delete because the requested visual was specifically an X.
  - Add `x`, `trash`, `external-link`, `image`, `play`, `pause`, and `refresh` named icons.
  - Allow `icon => 'x'` with `variant => 'danger'` for destructive mini buttons.

- URL display transformations should be component options.

  - Library strips the current site URL using a local closure before rendering URL columns.
  - Add a table/link/code cell option like `strip_site_url => true`, `display_callback`, or `format => 'relative_site_url'`.
  - This keeps URL display logic reusable without hard-coding a site host in templates.

- Long text/table overflow should be first-class.

  - QR Factory still keeps `.qrfactory-data-table` and `.qrfactory-log-table` CSS for min widths and long URLs/paths.
  - Add package classes or args for `min_width`, `vertical_align`, `wrap_code`, `wrap_links`, and `word_break`.
  - Tables with URLs, paths, refs, and user agents should not need local CSS.

- Add dependency/conditional UI support.

  - QR Factory has logo fields that conceptually depend on `qrfactory_design_logo_enabled`.
  - Future plugins will need fields that show/hide or disable based on switches, selects, or plan state.
  - Suggested schema: `visible_if`, `disabled_if`, and `requires`.
  - Package JS should update dependent rows without each plugin writing custom scripts.

- Add safer page-level layout options.
  - QR Factory still needs local CSS for WordPress admin chrome: full-height layout, hidden footer, zero `#wpcontent` padding, and dark page background.
  - The app shell should expose `layout => 'fullscreen' | 'standard'`, `hide_wp_footer`, `content_padding`, and `body_class` guidance.

### v1.0.3 success criteria

- A plugin can define its header, tabs, sections, schema options, tables, modals, notices, and Ajax table mutations with mostly Admin UI helpers.
- Dynamic table rows can be created, updated, deleted, paginated, and emptied without duplicating row markup between PHP and JS.
- Modal forms can be rendered by the package and opened from any custom trigger, especially table action icons.
- QR Factory local CSS should shrink to branding/theme variables and QR-specific result imagery only.
- QR Factory local JS should focus on QR business behavior, not generic tabs, notices, Ajax boilerplate, modal plumbing, pagination, or table row state.

## v1.0.2 update

v1.0.1 covered the highest-impact QR Factory needs: `borderless` and `table-only` section variants, divided option rows, table action buttons, static table pagination, `[hidden]` handling, and dynamic modal helpers. The next update should focus on smaller reusable variants and helpers that still force plugins to add local CSS or repetitive markup.

### Highest value

- Standardize shared args across all components.

  - Every renderer should consistently support `class`, `attributes`, and `data_attributes`.
  - Components with visual variations should use `variant` and `size` instead of plugin-specific classes.
  - Components with internal parts should support a `classes` slot map while keeping existing `*_class` args backward compatible.
  - This makes custom behavior predictable and keeps plugin templates from mixing package args with hand-written attributes.

- Add a compact badge size.

  - Suggested API: `MatterAdminUI::badge( array( 'label' => 'Active', 'variant' => 'success', 'size' => 'compact' ) )`.
  - Suggested class: `.mwp-badge.is-compact`.
  - Target style: `min-height: 0`, `padding: 1px 6px`, `font-size: 10px`, `line-height: 1.4`.
  - This removes repeated table-status/type badge CSS from plugins.

- Add a plain form variant.

  - Suggested API: `MatterAdminUI::form( array( 'variant' => 'plain' ), $content )`.
  - Suggested class: `.mwp-form.is-plain`.
  - Remove form chrome: padding, border, background, and box shadow.
  - Keep `.mwp-form-fields` grid and `.mwp-form-actions` behavior.
  - Add an action alignment arg: `actions_align => 'left' | 'right' | 'between'`, default `right`.
  - Add `columns => 1 | 2 | 3 | 'auto'` so plugins do not need one-off field-grid wrappers for common form layouts.

- Add a reusable empty-state component.
  - Suggested API: `MatterAdminUI::emptyState( array( 'title' => '', 'description' => '', 'class' => '' ) )`.
  - Use a neutral dashed container with centered title/body text.
  - Must respect `[hidden]`.
  - Support optional `actions` for one or two buttons.

### Efficiency improvements for plugin builds

- Improve `option` layout args.

  - Prefer `layout => 'row' | 'column' | 'divided'` as the primary API.
  - Do not use legacy `wide`, `align_start`, `divider`, or `style` args.
  - Add `control_width => 'narrow' | 'standard' | 'wide' | 'full'`.
  - Add `align => 'center' | 'start' | 'stretch'`.
  - Add optional `badge`, `help`, and `actions` args for common settings-row metadata.
  - This would cover rows like Bulk Generate, Storage Details, Logo Image, and Batch Processor without local input classes.

- Improve controls for future schema-driven plugins.

  - `input` should support `min`, `max`, `step`, `readonly`, `required`, and `autocomplete`.
  - `input`, `select`, `textarea`, `switch`, and `button` should consistently support `attributes` and `data_attributes`.
  - This removes raw HTML inputs in QR Factory fields like logo size and export size.

- Add `settingOption` or `schemaOption`.

  - Input: a schema field, current value, and optional overrides.
  - Output: an `option` row plus the right control.
  - Support common schema keys: `label`, `description`, `type`, `choices`, `min`, `max`, `step`, and `default`.
  - Keep manual `option` rendering available for custom rows.

- Add layout helpers for repeated non-table content.

  - `switchGrid` or `choiceGrid` for switch-card groups like Tools post-type selection.
  - `keyValueList` for Settings/System Info details such as current path, URL, and storage status.
  - `actionBar` for progress plus button layouts like Batch Processor.

- Add row/cell helpers for common table content.

  - Badge cell: render compact badges without local wrapper classes.
  - Link cell: safe external link with `target="_blank"` and `rel="noopener noreferrer"`.
  - Code cell: wrap long URLs/IDs without plugin CSS.
  - Image preview cell: fixed-size preview thumbnail.
  - Date cell: consistent timestamp display.
  - Actions cell: render `actionGroup` inside table cells without custom wrappers.

- Add typed table column definitions.

  - Suggested API: `columns => array( 'status' => array( 'label' => 'Status', 'type' => 'badge', 'badge_size' => 'compact' ) )`.
  - Support custom cell callbacks for advanced cases.
  - This keeps future plugin tables data-driven while preserving escape hatches.

- Improve `paginatedTable` for dynamic tables.

  - Let callers provide `empty_selector` or `empty_target` so pagination refresh can show/hide empty states after rows are added or removed.
  - Let callers set `initial_page => 'first' | 'last' | 1`.
  - Keep the existing `mwp:table-refresh` event.

- Expand `actionGroup` ergonomics.

  - Add named icons for common Lucide-style actions: `edit`, `delete`, `link`, `download`, `copy`, `view`.
  - Keep custom SVG support for advanced cases.
  - Support `confirm` metadata for destructive actions when a plugin only needs a browser confirm.

- Add a modal form helper.
  - Suggested API: modal accepts `fields` and `footer_actions` so common edit modals do not require hand-written modal markup.
  - Preserve the existing `window.MatterAdminUI.openModal()` population contract with `name` or `data-mwp-field`.

### Consistency notes

- Prefer args that map directly to classes over plugin-specific classes.

  - Good examples: `section_variant => 'minimal'`, `layout => 'divided'`, `size`, `variant`, `actions_align`, and cell/action helper args.

- Keep callback-based rendering as the default.

  - Add structured helpers for common cases, but do not force every plugin into schema-driven rendering.
  - The package should make simple rows fast and advanced rows possible.

- Keep helper output optional and composable.

  - Plugins should still be able to write raw table rows, custom modals, and custom SVG icons.
  - Package helpers should cover the common case without making advanced cases harder.

- Avoid one-off QR Factory names in package APIs.
  - Generalize around admin patterns: status badges, empty states, URL/code cells, image previews, destructive actions, and plain embedded forms.

## v1.0.1 covered reference

These items were added in v1.0.1 and are kept here as context for why the APIs exist.

### Section Layouts

- Formerly implemented a `borderless` section variant for direct content such as stat grids. Current API uses `section_variant => 'minimal'`.

  - Keep the normal `mwp-section-title`.
  - Remove only the section-options chrome: border, radius, and background.
  - Do not strip borders from child components like `mwp-stat` or `mwp-table-wrap`.

- Formerly implemented a `table-only` section variant for table/list sections. Current API uses `section_variant => 'minimal'`.
  - Keep the normal `mwp-section-title`.
  - Let a direct child table wrapper render without an enclosing option row.
  - Preserve the native `mwp-table-wrap` border, radius, and background.

### Option Rows

- Implemented an option-row variant that separates `.mwp-option-info` from `.mwp-option-input` with a full-width divider.
  - Avoid negative margins.
  - Use the same 20px spacing as regular option rows.
  - Ensure `box-sizing: border-box` so wide forms do not overflow.

### Tables

- Implemented a documented pattern for admin tables rendered directly inside sections.

  - Wrapper: `.mwp-table-wrap`.
  - Table: `.mwp-table`.
  - Optional project class should not be needed for normal table visuals.

- Implemented built-in client-side table pagination for static rows.
  - Previous/next buttons.
  - Page label.
  - Per-page setting, default 10.
  - Re-render support after rows are added or removed dynamically.

### Actions

- Implemented a compact icon action group for table action columns.
  - 28px icon buttons.
  - 8px gap.
  - Normal and danger variants.
  - Works for links and buttons.
  - Uses lucide-style icons consistently.

### Empty States

- Ensured components with explicit display rules still respect `[hidden]`.
  - Example: an empty state using `display: flex` must have `[hidden] { display: none !important; }`.

### Modals

- Kept modal actions aligned with the Admin UI modal contract.

  - Trigger uses `data-mwp-modal-trigger`, `aria-controls`, `aria-haspopup="dialog"`, and `aria-expanded`.
  - Modal uses `data-mwp-modal`, `aria-hidden`, and `hidden`.
  - Close controls use `data-mwp-modal-close`.

- Exposed a helper for dynamic edit modals.
  - Let callers populate fields before opening.
  - Preserve Admin UI focus handling and body lock.
