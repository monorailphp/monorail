# Forms

Forms are declared with the `Form` builder and rendered by React from a
serialized schema. Validation rules travel with the schema — errors are
displayed inline without a client round-trip.

`form()` is **optional** on a resource. If you don't define one, the
create/edit pages and the **New** button are hidden automatically — useful
for read-only resources backed by an external system.

```php
use Monorail\Forms\Components\Select;
use Monorail\Forms\Components\TextInput;
use Monorail\Forms\Components\Textarea;
use Monorail\Forms\Form;

public static function form(Form $form): Form
{
    return $form->schema([
        TextInput::make('title')->required()->maxLength(255),
        Textarea::make('body')->rows(6),
        Select::make('status')->options([
            'draft' => 'Draft',
            'published' => 'Published',
        ])->required(),
    ]);
}
```

## Field types

| Class | Purpose |
| --- | --- |
| `TextInput` | Single-line text (supports `email`, `password`, `number` variants). |
| `Textarea` | Multi-line text. |
| `Select` | Single-select dropdown. |
| `MultiSelect` | Multi-select dropdown. |
| `Checkbox` | Single boolean checkbox. |
| `Radio` | Radio group. |
| `Toggle` | Switch toggle for booleans. |
| `DatePicker` | Calendar picker. |
| `BelongsTo` | Relationship dropdown (loads options from a related model). |
| `BelongsToMany` | Multi-select relationship. |
| `FileUpload` | File input with preview. |
| `KeyValue` | Dynamic key/value pairs (arrays, JSON columns). |

## Common modifiers

- `->label(string)` — override the auto-generated label
- `->required()` / `->nullable()`
- `->disabled()` / `->readOnly()`
- `->placeholder(string)`
- `->helperText(string)`
- `->default(mixed)`
- `->columnSpan(int)` — width in the grid (1–12)
- `->hidden()` / `->visible(bool)`

Validation modifiers (serialized as Laravel rules):

- `->minLength(int)` / `->maxLength(int)`
- `->min(int)` / `->max(int)`
- `->email()` / `->url()` / `->regex(string)`
- `->unique()` / `->exists()`
- `->confirmed()` (for password confirmation)

## Layout

Group fields with `Section` and `Tabs`:

```php
use Monorail\Forms\Components\Section;
use Monorail\Forms\Components\Tabs;

Section::make('Details')
    ->description('Basic info')
    ->schema([
        TextInput::make('title'),
        TextInput::make('slug'),
    ]);

Tabs::make('Post')
    ->tabs([
        Tabs\Tab::make('Content')->schema([ /* ... */ ]),
        Tabs\Tab::make('SEO')->schema([ /* ... */ ]),
    ]);
```

## Adding a custom field

Custom fields are registry-driven — you don't fork Monorail to add one.
Subclass `Field` for the PHP schema, write a React component, register
it via `createMonorail({ fields: { ... } })` in your host app entry.

Laravel validation rules travel as part of the schema — the client
displays errors from the server response and never re-validates.

See [Customizing the Frontend](../advanced/customizing-the-frontend.md#registering-a-custom-field)
for the full walkthrough.

If you're upstreaming a primitive into Monorail itself, add the React
case to `components/form-field.tsx` and cover the serialization in a
feature test — see [custom columns](../tables/columns.md#adding-a-custom-column)
for the contributor-side pattern.
