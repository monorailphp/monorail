# Configuration Reference

`config/monorail.php` holds package-wide defaults. Every key is optional;
panels can override any value through the fluent [Panel API](panels/configuration.md).

Publish the file to customize defaults:

```bash
php artisan vendor:publish --tag=monorail-config
```

## Default panel

| Key | Default | Purpose |
| --- | --- | --- |
| `default_panel` | `env('MONORAIL_DEFAULT_PANEL', 'admin')` | Panel ID used when no panel is explicitly resolved. |

If you register multiple panels, this is the fallback. You can also mark
one as default with `$panel->default()`.

## Inertia

| Key | Default | Purpose |
| --- | --- | --- |
| `inertia.root_view` | `env('MONORAIL_ROOT_VIEW', 'monorail::app')` | Blade view that wraps every Monorail page. Publish `monorail-views` to override. |

## Assets

| Key | Default | Purpose |
| --- | --- | --- |
| `assets.js_entry` | `env('MONORAIL_JS_ENTRY', 'packages/monorail/resources/js/monorail.tsx')` | Vite entry included in the root Blade view. Point this at your own entry file if you call `createMonorail()` to register custom components — see [Customizing the Frontend](advanced/customizing-the-frontend.md). |

## Routes

| Key | Default | Purpose |
| --- | --- | --- |
| `routes.middleware` | `['web']` | Middleware applied to every panel route. |
| `routes.auth_middleware` | `['auth']` | Middleware applied to authenticated routes only. |
| `routes.domain` | `null` | Restrict all panels to a domain. |

Panels can override each of these with `->middleware()`,
`->authMiddleware()`, and `->domain()`.

## Pagination

| Key | Default | Purpose |
| --- | --- | --- |
| `pagination.per_page` | `25` | Default page size on list pages. |
| `pagination.min_per_page` | `1` | Hard floor for the `perPage` query param. |
| `pagination.max_per_page` | `100` | Hard ceiling — protects against abusive page sizes. |
| `pagination.per_page_options` | `[5, 10, 25, 50, 100]` | Choices shown in the per-page dropdown. |
| `pagination.relation_manager.per_page` | `5` | Page size inside [relation managers](resources/relation-managers.md). |

## Branding

| Key | Default | Purpose |
| --- | --- | --- |
| `brand.name` | `env('MONORAIL_BRAND', 'Monorail')` | Default brand text; overridable per panel with `->brand()`. |

## Publishable tags

Monorail publishes its resources under separate tags so you can pick only
what you need.

| Tag | Destination | Use |
| --- | --- | --- |
| `monorail-config` | `config/monorail.php` | Change defaults (pagination, brand, routes). |
| `monorail-lang` | `lang/vendor/monorail/{locale}.json` | Override translations. See [i18n](i18n.md). |
| `monorail-views` | `resources/views/vendor/monorail/` | Override the Blade root layout. |
| `monorail-assets` | `resources/js/vendor/monorailphp/` | Fork the React frontend. **Most extension needs are better served by the registry-based [createMonorail()](advanced/customizing-the-frontend.md) flow** — only publish if you need to edit built-in components directly. |
| `monorail-stubs` | `stubs/monorail/` | Customise the templates used by `monorail:make-*` generators. |

Publish a single tag:

```bash
php artisan vendor:publish --tag=monorail-config
```

Publish everything at once:

```bash
php artisan vendor:publish --provider='Monorail\MonorailServiceProvider'
```

## Environment variables

All keys that read from `env()` can be set without publishing the config:

```env
MONORAIL_DEFAULT_PANEL=admin
MONORAIL_ROOT_VIEW=monorail::app
MONORAIL_JS_ENTRY=vendor/monorail/monorail/resources/js/monorail.tsx
MONORAIL_BRAND="Acme"
```
