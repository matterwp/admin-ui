# next update to-dos

These items came from QR Factory admin-screen customizations. Consider moving them into the Admin UI package so future plugin screens can use them without plugin-specific CSS/JS.

## Section Layouts

- Add a `borderless` section variant for direct content such as stat grids.
  - Keep the normal `mwp-section-title`.
  - Remove only the section-options chrome: border, radius, and background.
  - Do not strip borders from child components like `mwp-stat` or `mwp-table-wrap`.

- Add a `table-only` section variant for table/list sections.
  - Keep the normal `mwp-section-title`.
  - Let a direct child table wrapper render without an enclosing option row.
  - Preserve the native `mwp-table-wrap` border, radius, and background.

## Option Rows

- Add an option-row variant that separates `.mwp-option-info` from `.mwp-option-input` with a full-width divider.
  - Avoid negative margins.
  - Use the same 20px spacing as regular option rows.
  - Ensure `box-sizing: border-box` so wide forms do not overflow.

## Tables

- Add a documented pattern for admin tables rendered directly inside sections.
  - Wrapper: `.mwp-table-wrap`.
  - Table: `.mwp-table`.
  - Optional project class should not be needed for normal table visuals.

- Add built-in client-side table pagination for static rows.
  - Previous/next buttons.
  - Page label.
  - Per-page setting, default 10.
  - Re-render support after rows are added or removed dynamically.

## Actions

- Add a compact icon action group for table action columns.
  - 28px icon buttons.
  - 8px gap.
  - Normal and danger variants.
  - Works for links and buttons.
  - Uses lucide-style icons consistently.

## Empty States

- Ensure components with explicit display rules still respect `[hidden]`.
  - Example: an empty state using `display: flex` must have `[hidden] { display: none !important; }`.

## Modals

- Keep modal actions aligned with the Admin UI modal contract.
  - Trigger uses `data-mwp-modal-trigger`, `aria-controls`, `aria-haspopup="dialog"`, and `aria-expanded`.
  - Modal uses `data-mwp-modal`, `aria-hidden`, and `hidden`.
  - Close controls use `data-mwp-modal-close`.

- Consider exposing a helper for dynamic edit modals.
  - Let callers populate fields before opening.
  - Preserve Admin UI focus handling and body lock.
