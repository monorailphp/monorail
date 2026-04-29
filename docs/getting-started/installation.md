# Installation

## Requirements

- PHP 8.2+
- Laravel 11 / 12 / 13
- Inertia.js v3 (`inertiajs/inertia-laravel`)
- React 19 + `@inertiajs/react`
- Tailwind CSS v4 in the host app
- shadcn/ui components

## Install the package

```bash
composer require monorail/monorail
```

The service provider is auto-discovered — no manual registration needed.

## Wire up the frontend

**1. Register Monorail's source with Tailwind.** Add to `resources/css/app.css`:

```css
@import 'tailwindcss';
@source '../../vendor/monorail/monorail/resources/js';
```

**2. Add the Monorail entry to `vite.config.ts`:**

```ts
import path from 'node:path';

laravel({
    input: [
        'resources/css/app.css',
        'resources/js/app.tsx',
        'vendor/monorail/monorail/resources/js/monorail.tsx',
    ],
}),
// ...
resolve: {
    alias: {
        '@monorail': path.resolve(__dirname, 'vendor/monorail/monorail/resources/js'),
    },
},
```

**3. Run the dev server:**

```bash
npm run dev
```

## Publish configuration (optional)

```bash
php artisan vendor:publish --tag=monorail-config
php artisan vendor:publish --tag=monorail-lang
```

Next: [Quick Start](quick-start.md).
