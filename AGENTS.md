# MatterWP Admin UI Agent Guide

This package is the reusable Admin UI layer for MatterWP WordPress plugins and a Composer package that we actively maintain, update, and extend within the Boilerplate plugin.

For other plugins, Composer packages should be consumed through Composer. No plugin other than the Boilerplate plugin should include .dev/packages/admin-ui in its codebase.

## Required Reading

- Read `skills/RULES.md` before changing component APIs, markup, styles, JavaScript services, or boilerplate examples.
- Read `skills/COMPONENTS.md` before using or extending public component args, variants, data attributes, JavaScript hooks, or examples.
- Update `skills/COMPONENTS.md` whenever public component behavior, args, variants, classes, JS APIs, data attributes, or boilerplate usage changes.

## Package Scope

- Keep reusable admin UI patterns in this package, not plugin templates.
- Prefer stable PHP component args, `mwp-` class hooks, and package JavaScript services over plugin-local markup/CSS/JS.
- Preserve compatibility for public APIs in the current major version unless a migration is explicitly documented.

## Workflow

- PHP source lives in `src/`.
- SCSS lives in `resources/scss/matterwp/`.
- JavaScript lives in `resources/js/`.
- Built assets live in `dist/` and are consumed by plugins through Composer.
- This package owns only admin UI components/assets/browser services. Subscription, onboarding, licensing, analytics, and product telemetry integrations belong to consuming plugins.

## Upgrade First Steps

When upgrading this package or updating existing components:

1. Read `skills/RULES.md` and `skills/COMPONENTS.md` first.
2. Inspect the existing component source before editing: facade in `src/UI.php`, implementation class, SCSS, JavaScript initializer, and current boilerplate example.
3. Check backward compatibility before changing public args, class names, markup shape, data attributes, or browser APIs.
4. Keep reusable behavior in Admin UI; use boilerplate only to demonstrate the package API.
5. Keep `1.2.x` changes compatible within `MTWP\ADMIN\V120`; use a new versioned namespace for the next minor release line when isolation is required.

## Release-Line Bump Checklist

Use this checklist when preparing a new minor release line, such as the `1.1.x` to `1.2.x` bump.

1. Update package metadata:
   - `.dev/packages/admin-ui/composer.json`: bump `version` and PSR-4 namespace.
   - `.dev/packages/admin-ui/package.json`: bump `version`.
   - `.dev/packages/admin-ui/package-lock.json`: bump only the root package version entries unless dependencies actually change.
   - `.dev/packages/admin-ui/src/Assets.php`: update fallback asset versions.

2. Rename the package PHP namespace:
   - Replace the active release namespace in every `.dev/packages/admin-ui/src/*.php` file.
   - Update the consuming boilerplate imports in `src/Admin/Controllers/*`, `src/Core/Controllers/*`, `templates/admin/*.php`, and `templates/admin/tabs/*.php`.
   - Keep historical changelog entries and compatibility notes that intentionally mention older namespaces.

3. Update Composer integration from the boilerplate root:
   - Change root `composer.json` to require the new package constraint, for example `matterwp/admin-ui: ^1.2`.
   - Run `composer update matterwp/admin-ui --with-dependencies` from the boilerplate root.
   - Confirm root `composer.lock` shows the new package version and PSR-4 namespace.

4. Update documentation:
   - Package docs: `.dev/packages/admin-ui/README.md`, `.dev/packages/admin-ui/CHANGELOG.md`, `skills/COMPONENTS.md`, `skills/RULES.md`, and this `AGENTS.md`.
   - Boilerplate docs: root `README.md` and `readme.txt`.
   - Add a new changelog entry instead of editing old release history, except where live/current guidance still points to the old version.

5. Rebuild generated assets:
   - Run `npm run build` inside `.dev/packages/admin-ui`.
   - Run root `npm run build` because the boilerplate imports package source into its Vite build.

6. Verify:
   - Run `php -l` on changed PHP files and all package source files.
   - Run `git diff --check`.
   - Run package PHPCS when practical. If full PHPCS reports existing baseline formatting noise, call that out and run focused checks on files directly affected by the release bump.
   - Confirm no active code imports the old namespace with `rg -F 'MTWP\\ADMIN\\VOLD' src templates .dev/packages/admin-ui/src`.

## 1.2.0 Bump Notes

- The `1.2.0` bump moved live package classes from `MTWP\ADMIN\V110` to `MTWP\ADMIN\V120`.
- Root `composer update matterwp/admin-ui --with-dependencies` updated `composer.lock` from `1.1.0` to `1.2.0` and changed the locked PSR-4 namespace to `MTWP\\ADMIN\\V120\\`.
- `package-lock.json` did not update automatically from the Vite build; its root package version entries needed manual alignment with `package.json`.
- Full package/root PHPCS can report pre-existing formatting issues in `Layout.php` and boilerplate tab templates. Do not use `phpcbf` broadly during a release-line bump unless the user explicitly wants formatting churn.

## Verification

- Run `COMPOSER_ALLOW_SUPERUSER=1 composer phpcs` after touching PHP.
- Run `COMPOSER_ALLOW_SUPERUSER=1 composer phpcbf` only when intentionally fixing style.
- Run `npm run build` after changing package SCSS or JS.
- Run the root plugin build when package asset changes should be reflected in boilerplate assets.
- Do not run browser diagnostics unless the user asks.
