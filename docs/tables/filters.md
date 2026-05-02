# Filters

Filters narrow a table's result set. They appear above the table as
dropdowns or toggles and persist in the URL query string.

```php
use Monorail\Tables\Filters\DateRangeFilter;
use Monorail\Tables\Filters\SelectFilter;
use Monorail\Tables\Filters\TernaryFilter;
use Monorail\Tables\Filters\TrashedFilter;

$table->filters([
    SelectFilter::make('status')
        ->options([
            'draft' => 'Draft',
            'published' => 'Published',
            'archived' => 'Archived',
        ]),

    TernaryFilter::make('is_featured')
        ->label('Featured'),

    DateRangeFilter::make('created_at'),

    TrashedFilter::make(),
]);
```

## `SelectFilter`

Single-select dropdown. Pass a `key => label` map via `->options()`.

## `TernaryFilter`

Three-state toggle: **Yes**, **No**, **All**. Backed by a boolean column.

## `DateRangeFilter`

Two date pickers that filter with `whereBetween`.

## `TrashedFilter`

Soft-delete filter with three states:

- **Active only** (default) — `whereNull('deleted_at')`
- **Trashed only** — `onlyTrashed()`
- **All** — `withTrashed()`

Only include this on resources whose model uses `SoftDeletes`.

## Custom filter logic

Every filter supports `->query(Closure $callback)` to override how it
applies to the Eloquent query. Use this when the filter key doesn't map
one-to-one with a column.

```php
SelectFilter::make('activity')
    ->options(['active' => 'Active', 'stale' => 'Stale'])
    ->query(function ($query, $value) {
        return match ($value) {
            'active' => $query->where('last_seen_at', '>', now()->subDays(7)),
            'stale'  => $query->where('last_seen_at', '<=', now()->subDays(7)),
            default  => $query,
        };
    });
```

## Adding a custom filter

Custom filters are registry-driven — you don't fork Monorail to add one.
Subclass `Filter` for the PHP side (`toArray()` + `apply()`), write a
React component, register it via `createMonorail({ filters: { ... } })`
in your host app entry.

See [Customizing the Frontend](../advanced/customizing-the-frontend.md#registering-a-custom-filter)
for the full walkthrough.

If you're upstreaming a filter into Monorail itself, add the case to
`components/table-filters.tsx` and cover the serialization in a feature
test — see [custom columns](columns.md#adding-a-custom-column) for the
contributor-side pattern.
