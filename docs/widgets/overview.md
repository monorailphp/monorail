# Widgets

Widgets are small UI components that render a single piece of data —
counters, charts, recent records, activity feeds. They appear on the
dashboard and on resource pages (list, create, edit, view).

## Declaring widgets on a resource

```php
use Monorail\Dashboard\RecentRecordsWidget;
use Monorail\Dashboard\StatWidget;

public static function widgets(): array
{
    return [
        StatWidget::make('Total Users', User::query()->count())
            ->columnSpan(3)
            ->only(['list']),

        RecentRecordsWidget::make('Recent Users')
            ->columnSpan(3)
            ->resource(UserResource::class)
            ->limit(5),
    ];
}
```

## Widget types

| Class | Purpose |
| --- | --- |
| `StatWidget` | Single value with a label (count, total, status). |
| `ChartWidget` | Line / bar / area chart. |
| `TableWidget` | Ad-hoc rows + columns table. |
| `RecentRecordsWidget` | Latest N records from a resource. |
| `ActivityFeedWidget` | Chronological list of events. |

## Common modifiers

- `->columnSpan(int)` — width in the dashboard grid (1–6; panel-configured max)
- `->only(array)` — restrict to specific resource pages (`list`, `create`, `edit`, `view`)
- Omitting `->only()` renders the widget on every page

## Placement

Widgets declared in `Resource::widgets()` appear on that resource's pages.
For the main dashboard, register widgets on the panel or via a custom
`DashboardPage`.

See [Panel Configuration](../panels/configuration.md#layout) for the
`dashboardColumns()` setting.

## Adding a custom widget

Custom widgets are registry-driven — you don't fork Monorail to add one.
Subclass `Widget` for the PHP schema, write a React component, register
it via `createMonorail({ widgets: { ... } })` in your host app entry.

See [Customizing the Frontend](../advanced/customizing-the-frontend.md#registering-a-custom-widget)
for the full walkthrough.

If you're upstreaming a widget into Monorail itself, add the case to
`components/widget-renderer.tsx` and cover the serialization in a
feature test — see [custom columns](../tables/columns.md#adding-a-custom-column)
for the contributor-side pattern.
