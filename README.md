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
