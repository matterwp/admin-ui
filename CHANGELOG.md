# Changelog

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
