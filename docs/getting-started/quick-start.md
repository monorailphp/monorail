# Quick Start

## 1. Create a panel

```bash
php artisan monorail:make-panel Admin
```

This generates `app/Providers/Monorail/AdminPanelProvider.php`:

```php
use Monorail\Panel\Panel;
use Monorail\Panel\PanelProvider;

final class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->path('admin')
            ->brand('My App')
            ->discoverResources(
                in: app_path('Monorail/Resources'),
                for: 'App\\Monorail\\Resources',
            );
    }
}
```

> `discoverResources()` is optional. You can instead list resources
> explicitly with `->resources([UserResource::class])`, or skip both and
> add them later. See [Panel Configuration](../panels/configuration.md#resources--pages).

Register it in `bootstrap/providers.php`:

```php
return [
    // ...
    App\Providers\Monorail\AdminPanelProvider::class,
];
```

## 2. Create a resource

```bash
php artisan monorail:make-resource User
```

```php
use App\Models\User;
use Monorail\Forms\Components\TextInput;
use Monorail\Forms\Form;
use Monorail\Resources\Resource;
use Monorail\Tables\Columns\TextColumn;
use Monorail\Tables\Table;

final class UserResource extends Resource
{
    protected static string $model = User::class;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('name')->sortable(),
                TextColumn::make('email')->sortable()->copyable(),
            ])
            ->searchable(['name', 'email'])
            ->defaultSort('id', 'desc');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->required(),
            TextInput::make('email')->email()->required()->unique(),
        ]);
    }
}
```

Visit `/admin/users` — you get a sortable, searchable, paginated table with
create/edit pages.

## What's next?

- [Panel Configuration](../panels/configuration.md) — brand, theme, middleware
- [Tables](../tables/columns.md) — columns, filters, actions
- [Forms](../forms/fields.md) — all field types and layout
- [Authorization](../authorization.md) — wire up policies
