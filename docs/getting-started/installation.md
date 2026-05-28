# Installation

## Requirements

- PHP 8.2+
- Laravel 11 / 12 / 13
- Inertia.js v3 (`inertiajs/inertia-laravel`)
- React 19 + `@inertiajs/react`
- Tailwind CSS v4 in the host app
- Node.js 18+ (for npm)

## Install the packages

**1. Install the Composer package:**

```bash
composer require monorailphp/monorail
```

**2. Run the install command (recommended):**

```bash
php artisan monorail:install
```

This command will:
- Install the `monorailphp` npm package from the npm registry
- Update `vite.config.ts` with the Monorail entry point
- Publish the config file
- Publish and run migrations

Or install the npm package manually:

```bash
npm install monorailphp
```

### Peer dependencies

Monorail ships its UI libraries (`radix-ui`, `recharts`, `sonner`,
`react-day-picker`, `cmdk`, `date-fns`, `lucide-react`,
`class-variance-authority`, `clsx`, `tailwind-merge`) as regular
`dependencies` so `npm install monorailphp` pulls them in automatically.

Only React itself and Inertia remain as peers — these must be deduplicated
with the host app so hooks and the Inertia router stay in a single instance:

- `react ^18 || ^19`
- `react-dom ^18 || ^19`
- `@inertiajs/react ^3.0.0`

A standard Laravel + Inertia React app already satisfies these.

## Manual install

If you prefer not to use the install command, follow these steps:

**1. Register Monorail's source with Tailwind.** Add to `resources/css/app.css`:

```css
@import 'tailwindcss';
@source '../../node_modules/monorailphp/resources/js';
```

**2. Add the Monorail entry to `vite.config.ts`:**

```ts
import path from 'node:path';

laravel({
    input: [
        'resources/css/app.css',
        'resources/js/app.tsx',
        'node_modules/monorailphp/resources/js/monorail.tsx',
    ],
}),
// ...
resolve: {
    alias: {
        '@monorail': path.resolve(__dirname, 'node_modules/monorailphp/resources/js'),
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
