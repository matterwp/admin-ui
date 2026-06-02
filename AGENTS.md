# next update to-dos

These items came from QR Factory admin-screen customizations. Consider moving them into the Admin UI package so future plugin screens can use them without plugin-specific CSS/JS.

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
  - Keep `wide` and `divider` as aliases for backward compatibility.
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
  - Good examples from v1.0.1: `variant => 'table-only'`, `variant => 'borderless'`, `divider => true`.
  - Continue this pattern with `size`, `variant`, `actions_align`, and cell/action helper args.

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

- Implemented a `borderless` section variant for direct content such as stat grids.
  - Keep the normal `mwp-section-title`.
  - Remove only the section-options chrome: border, radius, and background.
  - Do not strip borders from child components like `mwp-stat` or `mwp-table-wrap`.

- Implemented a `table-only` section variant for table/list sections.
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
