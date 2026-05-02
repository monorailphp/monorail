# Page Blocks

Custom pages (dashboards, reports, landing screens) are built from
**blocks**: reusable schema fragments that compose into full pages.

```php
use Monorail\Pages\Blocks\GridBlock;
use Monorail\Pages\Blocks\HtmlBlock;
use Monorail\Pages\Blocks\WidgetBlock;
use Monorail\Pages\DashboardPage;

final class MarketingDashboard extends DashboardPage
{
    public static function blocks(): array
    {
        return [
            HtmlBlock::make('<h1 class="text-2xl">Marketing</h1>'),

            GridBlock::make()
                ->columns(3)
                ->schema([
                    WidgetBlock::make(StatWidget::make('Signups', 4_212)),
                    WidgetBlock::make(StatWidget::make('Trials', 318)),
                    WidgetBlock::make(StatWidget::make('MRR', '$82k')),
                ]),
        ];
    }
}
```

## Block types

| Class | Purpose |
| --- | --- |
| `WidgetBlock` | Render a dashboard widget inside a page. |
| `GridBlock` | Multi-column layout container (1–6 columns). |
| `HtmlBlock` | Raw HTML content — for headings, callouts, or notes. |

## Composition

Blocks can be nested. A `GridBlock` can contain widgets, HTML, or even
other grids. Each block is an independent schema object, so the React
renderer composes them without any page-specific code.

## Adding a custom block

Custom blocks are registry-driven — you don't fork Monorail to add one.
Subclass a base block class for the PHP schema, write a React component,
register it via `createMonorail({ blocks: { ... } })` in your host app
entry.

See [Customizing the Frontend](../advanced/customizing-the-frontend.md#registering-a-custom-block)
for the full walkthrough.

If you're upstreaming a block into Monorail itself, add the case to
`components/block-renderer.tsx` and cover the serialization in a test
under `tests/Feature/Pages/`. See [Server-Driven UI](../advanced/server-driven-ui.md)
for the broader pattern this follows.
