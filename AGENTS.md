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

## Upgrade First Steps

When upgrading this package or updating existing components:

1. Read `skills/RULES.md` and `skills/COMPONENTS.md` first.
2. Inspect the existing component source before editing: facade in `src/MatterAdminUI.php`, implementation class, SCSS, JavaScript initializer, and current boilerplate example.
3. Check backward compatibility before changing public args, class names, markup shape, data attributes, or browser APIs.
4. Keep reusable behavior in Admin UI; use boilerplate only to demonstrate the package API.
5. If another active plugin may load an older `MatterWP\AdminUI` copy first, avoid fatal examples by feature-detecting newly added methods.

## Verification

- Run `COMPOSER_ALLOW_SUPERUSER=1 composer phpcs` after touching PHP.
- Run `COMPOSER_ALLOW_SUPERUSER=1 composer phpcbf` only when intentionally fixing style.
- Run `npm run build` after changing package SCSS or JS.
- Run the root plugin build when package asset changes should be reflected in boilerplate assets.
- Do not run browser diagnostics unless the user asks.
