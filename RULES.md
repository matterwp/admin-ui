# MatterWP Admin UI Rules

This package exists to make WordPress plugin admin screens consistent, composable, and boring to maintain. It should absorb repeated admin UI patterns so plugin code can focus on product behavior instead of markup, styling, Ajax plumbing, table state, modal wiring, notices, or theme details.

## Philosophy

Admin UI is product infrastructure, not page decoration.

The package should provide stable PHP components, predictable CSS hooks, and small JavaScript services for common admin workflows. A plugin screen should be assembled from named components and options, not from copied DOM fragments and one-off CSS.

Components should feel like WordPress admin tools: compact, readable, durable, and fast to scan. Prefer quiet structure, restrained styling, clear hierarchy, and dense useful information. Avoid marketing-style composition, ornamental surfaces, and visual patterns that make operational screens harder to use repeatedly.

Every option should describe intent, not implementation trivia. Good options are names like `section_variant`, `option_variant`, `layout`, `control_width`, `align`, `icon_position`, and `data_attributes`. Avoid ambiguous names that collide across layers or hide what part of the component they affect.

## Core Rules

1. Keep plugin screens out of low-level UI debt.
   - If two plugins need the same admin pattern, move it into Admin UI.
   - Prefer component args over plugin-owned wrapper CSS.
   - Prefer package JavaScript services over plugin-local generic JS.

2. Components own their markup contract.
   - Markup must be predictable from the component name and args.
   - Public classes should use the `mwp-` prefix.
   - State and variant classes should be explicit: `is-section-minimal`, `is-option-variant-minimal`, `is-layout-divided`, `is-icon-after`.
   - Do not add new legacy alias classes unless migration requires them.

3. Variants must be semantic and small.
   - Add a variant only when it represents a reusable layout or behavior.
   - Do not create variants for one plugin's visual tweak.
   - `section_variant => 'wrapped'` means the section provides chrome around rows.
   - `section_variant => 'minimal'` means direct content is bare and responsible for its own shape.
   - `option_variant => 'standard'` means option rows have normal row structure.
   - `option_variant => 'minimal'` means option rows are naked: no row chrome and no padding on `.mwp-option`, `.mwp-option-info`, `.mwp-option-input`, or nested package surface roots such as `.mwp-result-card`.

4. Options should be explicit and scoped.
   - Use distinct names when parent and child variants both exist, for example `section_variant` and `option_variant`.
   - Use enumerated string values instead of booleans when more states may exist later.
   - Validate accepted values in PHP and fall back to documented defaults.
   - Keep defaults conservative and compatible with existing screens.

5. CSS must be component-owned and theme-aware.
   - Put reusable styles in `resources/scss/matterwp/`.
   - Keep dark-mode coverage with the component when the component owns colors, borders, or surfaces.
   - Do not rely on plugin CSS to fix base component spacing, borders, or theme tokens.
   - Avoid nested cards, decorative backgrounds, and one-off palette choices.
   - Minimal variants must actually remove package chrome, not merely hide borders.

6. JavaScript should be a service layer.
   - Public browser APIs live under `window.MatterAdminUI`.
   - Generic behavior belongs in the package: notices, modals, table mutation, Ajax form lifecycle, tabs, media controls, clipboard, dependency toggles.
   - Plugin JS should call package services and handle product-specific data only.
   - JS-created UI must use the same class and state contracts as PHP-rendered UI.

7. Accessibility is part of the component contract.
   - Interactive controls need correct element types, disabled states, focus behavior, labels, and roles.
   - Modal and overlay work must preserve focus management and Escape behavior.
   - Notices should use `role="status"` or `role="alert"` based on severity.
   - Icons are decoration unless they provide the only meaning.

8. Documentation follows every public change.
   - Update `COMPONENTS.md` whenever component args, variants, classes, JS APIs, data attributes, or boilerplate usage change.
   - Update `AGENTS.md` changelog notes for active package direction.
   - Keep examples current with the preferred API. Do not document newly removed legacy options as active choices.

9. Backward compatibility is deliberate, not accidental.
   - Removing or renaming public args requires a clear migration path and docs update.
   - Prefer one clean API over keeping many aliases forever.
   - Historical changelog entries may mention old names, but active guidance should point to current names.

10. Verification is required for meaningful UI changes.
    - Run package build after changing package SCSS or JS.
    - Run root build when generated plugin assets are expected to change.
    - Run PHP lint after touching PHP component code.
    - Use browser diagnostics or focused Playwright probes for visual behavior such as stacking, animation, dark mode, and computed spacing.
    - Run `git diff --check` before finishing.

## Component Design Checklist

- Can the component replace repeated plugin markup?
- Is the option name clear about the layer it controls?
- Are accepted values documented and validated?
- Does the generated markup expose stable `mwp-` hooks?
- Does the component work in light and dark mode?
- Does the component fit dense admin workflows without visual noise?
- Does JavaScript behavior degrade safely when no matching element exists?
- Did docs and changelog move with the change?

## Preferred Direction

Future development should move toward higher-level composition:

- More field and form composition through PHP args.
- More table, empty-state, action-bar, result-card, and modal workflows handled by package helpers.
- Fewer plugin-specific wrapper classes.
- Fewer copied scripts for tabs, notices, Ajax, modal state, and table mutation.
- Better primitives that stay small, predictable, and easy to combine.

The goal is not to make every plugin page look identical. The goal is to make every plugin page use the same reliable grammar.
