# Customizing the Frontend

Monorail's React layer is **registry-driven**. You never fork the package
to add a new field type, widget, or page — you register a component
against a schema `type` and the built-in renderers pick it up
automatically.

This is the React analogue of Laravel's Blade vendor-publish overrides:
you keep using the package, you only declare what you want to replace
or extend.

## When to use this

| Need | Path |
| --- | --- |
| Add a new field type (`rich_text`, `color_picker`, …) | Registry — `fields` |
| Override how `text` renders | Registry — `fields` |
| Add a new dashboard widget type | Registry — `widgets` |
| Add a new page block type | Registry — `blocks` |
| Add a new table column cell type | Registry — `columns` |
| Add a new table filter type | Registry — `filters` |
| Replace a built-in page (e.g. dashboard) | Registry — `pages` |
| Tweak markup of a built-in component | `vendor:publish --tag=monorail-assets`, then edit |
| Upstream a primitive into Monorail itself | See [Contributing](../contributing.md) |

## The override entry point

Out of the box, Vite points at the package's own entry
(`vendor/monorail/monorail/resources/js/monorail.tsx`) which auto-boots
Monorail with no overrides. **If you don't need to override anything,
do nothing — the default flow already works.**

To register overrides, create your own entry file and call
`createMonorail()` from it:

```ts
// resources/js/admin.tsx
import { createMonorail } from '@monorail/monorail/create';
import RichTextEditor from './fields/rich-text';
import LeaderboardWidget from './widgets/leaderboard';
import MyDashboard from './pages/my-dashboard';

createMonorail({
    fields: {
        rich_text: RichTextEditor,        // new field type
        text: MyOverriddenTextInput,      // override built-in
    },
    widgets: {
        leaderboard: LeaderboardWidget,
    },
    columns: {
        progress: ProgressCell,            // new table cell type
    },
    filters: {
        money_range: MoneyRangeFilter,     // new table filter type
    },
    pages: {
        'monorail/dashboard': MyDashboard, // replace a built-in page
    },
});
```

Then point Vite at your file in `vite.config.ts`:

```ts
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/admin.tsx'], // was: vendor/monorail/.../monorail.tsx
            refresh: true,
        }),
        // ...
    ],
});
```

`createMonorail()` boots Inertia exactly once; your overrides are merged
with the defaults before the React tree mounts.

## Registering a custom field

Two moves — PHP for the schema, React for the renderer.

**1. PHP.** Subclass `Field` and emit a unique `type`:

```php
namespace App\Monorail\Fields;

use Monorail\Forms\Components\Field;

final class RichTextField extends Field
{
    protected string $type = 'rich_text';

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'type' => 'rich_text',
        ]);
    }
}
```

**2. React.** Build a component matching the registry's `FieldRenderer` shape:

```tsx
// resources/js/fields/rich-text.tsx
import type { FieldRendererProps } from '@monorail/monorail/registry';

export default function RichTextEditor({ field, value, onChange }: FieldRendererProps) {
    return (
        <textarea
            id={field.name}
            value={String(value ?? '')}
            onChange={(e) => onChange(e.target.value)}
            className="rich-text"
        />
    );
}
```

**3. Register.** Pass it to `createMonorail()`:

```ts
createMonorail({
    fields: { rich_text: RichTextEditor },
});
```

Your `RichTextField` now renders with the custom component. Validation
rules and labels still come from PHP.

## Registering a custom widget

```ts
import type { WidgetRendererProps } from '@monorail/monorail/registry';

function Leaderboard({ widget }: WidgetRendererProps<{ type: 'leaderboard'; rows: { name: string; score: number }[] }>) {
    return (
        <ul>
            {widget.rows.map((r) => (
                <li key={r.name}>{r.name} — {r.score}</li>
            ))}
        </ul>
    );
}

createMonorail({
    widgets: { leaderboard: Leaderboard },
});
```

Pair it with a PHP `Widget` subclass that emits `type: 'leaderboard'`
plus whatever payload your renderer expects.

## Registering a custom block

```ts
import type { BlockRendererProps } from '@monorail/monorail/registry';

function CalloutBlock({ block }: BlockRendererProps<{ type: 'callout'; tone: 'info' | 'warn'; html: string }>) {
    return (
        <div className={`callout callout-${block.tone}`} dangerouslySetInnerHTML={{ __html: block.html }} />
    );
}

createMonorail({
    blocks: { callout: CalloutBlock },
});
```

## Registering a custom column

Pair a PHP `Column` subclass that emits a unique `type` with a React
component that renders one cell.

```tsx
// resources/js/columns/progress-cell.tsx
import type { ColumnRendererProps } from '@monorail/monorail/registry';

type ProgressCol = { type: 'progress'; extra: { max?: number } };

export default function ProgressCell({ column, value }: ColumnRendererProps<ProgressCol>) {
    const max = column.extra.max ?? 100;
    const pct = Math.min(100, (Number(value) / max) * 100);
    return (
        <div className="h-2 w-24 rounded bg-muted">
            <div className="h-2 rounded bg-primary" style={{ width: `${pct}%` }} />
        </div>
    );
}
```

```ts
createMonorail({
    columns: { progress: ProgressCell },
});
```

A custom column receives the full column schema and the raw value — and
takes precedence over the built-in null/empty placeholder, so the
component is responsible for rendering its own empty state.

## Registering a custom filter

Filter components participate in the table's URL state, so they receive
the current `query`, the `pending` (unsaved) state, a `stage()` helper
to mark a value as pending, and `navigate()` to commit pending values to
the URL. Pair with a PHP `Filter` subclass that implements `apply()`.

```tsx
// resources/js/filters/money-range-filter.tsx
import type { FilterRendererProps } from '@monorail/monorail/registry';

type MoneyRangeFilter = {
    type: 'money_range';
    state_key: string;
    label: string;
    value: { min?: number; max?: number } | null;
};

export default function MoneyRange({
    filter,
    pending,
    stage,
    navigate,
}: FilterRendererProps<MoneyRangeFilter>) {
    const current = (pending[filter.state_key] ?? filter.value ?? {}) as { min?: number; max?: number };

    const update = (patch: Partial<typeof current>) => {
        const next = { ...current, ...patch };
        stage(filter.state_key, next);
    };

    return (
        <div className="flex items-center gap-2">
            <input
                type="number"
                value={current.min ?? ''}
                onChange={(e) => update({ min: e.target.value === '' ? undefined : Number(e.target.value) })}
                onBlur={() => navigate((q) => ({ ...q, [filter.state_key]: current }))}
            />
            <span>—</span>
            <input
                type="number"
                value={current.max ?? ''}
                onChange={(e) => update({ max: e.target.value === '' ? undefined : Number(e.target.value) })}
                onBlur={() => navigate((q) => ({ ...q, [filter.state_key]: current }))}
            />
        </div>
    );
}
```

```ts
createMonorail({
    filters: { money_range: MoneyRange },
});
```

## Replacing a built-in page

The page registry uses Inertia component names as keys. The built-ins are:

- `monorail/list-records`
- `monorail/create-record`
- `monorail/edit-record`
- `monorail/view-record`
- `monorail/dashboard`
- `monorail/notifications`
- `monorail/error`
- `monorail/page`
- `monorail/login`
- `monorail/register`
- `monorail/forgot-password`
- `monorail/reset-password`
- `monorail/verify-email`
- `monorail/profile`

Pass a React component under the matching key and yours renders instead.

## What lives where

| Concern | File | Override mechanism |
| --- | --- | --- |
| Page resolver | `lib/create.tsx` | `pages` registry |
| Field renderer | `components/form-field.tsx` | `fields` registry |
| Widget renderer | `components/widget-renderer.tsx` | `widgets` registry |
| Block renderer | `components/block-renderer.tsx` | `blocks` registry |
| Table cell renderer | `components/data-table.tsx` | `columns` registry |
| Table filter renderer | `components/table-filters.tsx` | `filters` registry |
| Layout (`PanelShell`), data-table chrome, header actions | various | `vendor:publish --tag=monorail-assets`, then edit |

## Escape hatch: publish and edit

For deeper changes (panel shell layout, data-table internals) the
filesystem-publish path is still available:

```bash
php artisan vendor:publish --tag=monorail-assets
```

This copies the package JS into `resources/js/vendor/monorailphp/` for
you to edit directly. Treat it as a fork — you own the changes and
won't get upstream improvements automatically.

## Upstream contribution path

If your custom primitive is broadly useful (not project-specific),
contribute it to Monorail itself instead of carrying it in the host app
forever. See [Contributing](../contributing.md) for the expected PR shape.
