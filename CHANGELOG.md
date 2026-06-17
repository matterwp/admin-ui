# Changelog

## 1.2.0

- Made the dark block in `_tokens.scss` self-contained: redeclared `--mwp-ui-primary` (and the action-primary, focus ring, switch active, progress bar, nav upgrade, premium badge, upsell action, primary badge, and log filter active variants) so consumers can override a single token under `html[data-mwp-theme="dark"]` to give dark mode a distinct primary. Default values match the light block; current consumers see no change. Documented the extension point in the README.

## 1.1.1


- Added server-side DataTable pagination with shared `dataTableRows()`, `dataTableRow()`, and `dataTableResponse()` helpers.
- Added package-rendered table filters/search with automatic client/server request wiring.
- Added sortable columns, server `orderby`/`order` parameters, and client row sorting.
- Added loading, error, and empty-state handling for asynchronous tables.
- Added `MatterAdminUI.table.loadPage()` and `replaceRows()` plus table lifecycle/filter/sort events.
- Rebuilt bundled `dist/` CSS and JavaScript assets.

## 1.1.0

- Moved PHP classes to the release-line namespace `MTWP\ADMIN\V110`.
- Renamed the PHP facade from `MatterAdminUI` to `UI`; browser APIs remain under `window.MatterAdminUI`.
- Added grouped `UI::navigation()` support and `UI::app( navigation => ... )` composition.
- Added `UI::grid()` and `UI::divider()` layout primitives.
- Added linked app version badges through `changelog_url`.
- Updated the app header actions, theme toggle, version badge, log viewer surfaces, and dark-mode styling.
- Changed `UI::emptyState()` to accept custom callback content instead of static `actions`.
- Improved locked premium option styling so only option info and input content are dimmed.
- Prevented wrapped-section table chrome from adding extra borders around `.mwp-table-wrap`.
- Rebuilt bundled `dist/` CSS and JS assets.
