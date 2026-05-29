# CRUD Module Development Guide

This guide defines the generic structure that should be followed when building new CRUD modules.

It is intentionally project-agnostic.

## Goal

Build CRUD modules with:
- clear boundaries
- predictable file layout
- thin controllers
- reusable validation
- maintainable views
- scalable growth when a simple CRUD later becomes a workflow-heavy module

## Core Rule

A module is not just a table.

A module should group:
- routes
- permissions
- controller
- requests
- model
- views
- tests

around one business concept.

## Standard Module Shape

Every CRUD module should use this structure.

### Backend Files

- `app/Http/Controllers/{Module}Controller.php`
- `app/Http/Requests/{Module}StoreRequest.php`
- `app/Http/Requests/{Module}UpdateRequest.php`
- `app/Models/{Module}.php`
- `database/migrations/...create_{table}_table.php`
- `database/factories/{Module}Factory.php`
- `database/seeders/{Module}Seeder.php`

### Frontend Files

- `resources/views/{module}/index.blade.php`
- `resources/views/{module}/create.blade.php`
- `resources/views/{module}/edit.blade.php`
- `resources/views/{module}/show.blade.php`
- `resources/views/{module}/trash.blade.php`

### Optional Supporting Files

Add these only when complexity justifies them:

- `app/Services/{Module}/...`
- `app/Policies/{Module}Policy.php`
- `app/Http/Resources/...`
- `resources/js/modules/{module}.js`

## Controller Rules

Use one controller per module.

Controller responsibilities:
- render pages
- receive validated requests
- call model/service logic
- return redirects or responses

Controller responsibilities should not include:
- large business rules
- complex query composition
- integration logic
- reusable mutation logic
- formatting-heavy table transformation logic

If a controller starts becoming large, move logic into:
- Form Requests for authorization and request-specific validation concerns
- service classes for business logic and multi-model mutations
- query builders for reusable query composition
- policies for authorization rules

## Request Rules

Every writable CRUD module should have:
- one store request
- one update request

Form Request should act as a thin adapter layer.

Form Request responsibilities:
- authorize the request
- delegate reusable rules to the model
- delegate reusable messages to the model
- handle request-specific validation only when the validation pattern is truly limited to one case or one feature
- expose `$request->validated()` to controllers

Recommended pattern:
- `{Module}::validationRules(?{Module} $record = null): array`
- `{Module}::validationMessages(?{Module} $record = null): array`

Store request should usually call:
- `{Module}::validationRules()`
- `{Module}::validationMessages()`

Update request should usually:
- extend the store request when the contract is mostly identical
- pass the current route model into:
  - `{Module}::validationRules($this->route('{module}'))`
  - `{Module}::validationMessages($this->route('{module}'))`

Placement rule:
- if a validation pattern is generic and reused across store/update or across more than one feature, define it in the model
- if a validation pattern happens only in one specific case, one endpoint, or one feature flow, it may stay in the Form Request
- once the same validation pattern appears in more than one place, move it to the model so the validation contract stays centralized

Rules:
- use array syntax
- use `$request->validated()`
- do not duplicate reusable validation rules across requests
- do not validate full CRUD payloads inline in controllers
- allow small inline validation only for lightweight operational endpoints such as reorder/filter utilities when it is truly local and not reusable

## Model Rules

Every model should define:
- explicit `$fillable`
- explicit casts
- explicit relationships

Models may include:
- accessors for stable derived values
- scopes for reusable filters
- generic validation definitions reused by multiple requests or features
- reusable validation messages
- validation variants that accept the current record for update scenarios

Models should not include:
- HTML generation
- redirect logic
- controller response concerns
- request-specific validation flow that is only relevant to one case

Validation placement principle:
- reusable validation belongs in the model
- one-off validation may stay in the request
- if a one-off rule starts being reused, promote it into the model

## Route Rules

Routes should be named and grouped by module capability.

Typical route set:
- `index`
- `create`
- `store`
- `show`
- `edit`
- `update`
- `destroy`
- `trash`
- `restore`
- `force-delete`

Typical capability groups:
- `view`
- `create`
- `edit`
- `delete`
- `restore`
- `force_delete`

Use permission middleware consistently.

## Permission Rules

Permissions should be module-based and capability-based.

Recommended pattern:
- `{domain}.{module}.view`
- `{domain}.{module}.create`
- `{domain}.{module}.edit`
- `{domain}.{module}.delete`
- `{domain}.{module}.restore`
- `{domain}.{module}.force_delete`

Only add special permissions when there is a real workflow need, such as:
- `sync`
- `approve`
- `export`
- `import`

## View Rules

Each module should have a consistent page set:
- listing page
- create form
- edit form
- detail page
- trash page

Use shared Blade components for repeated field patterns.

Do not duplicate:
- labels
- help text
- validation message markup
- repeated form wrappers

when a shared component already exists.

### Modal CRUD Rule

If a module form has **no more than 6 primary input fields**, prefer a **modal CRUD pattern** for create and edit.

Use modal CRUD when:
- the form is short
- the fields are straightforward
- the workflow is simple
- the user benefits from staying on the listing page

Typical pattern:
- index page contains the table
- create action opens a modal
- edit action opens a modal
- validation failure should reopen the correct modal

Do not force modal CRUD when:
- the form is long
- the form contains advanced sections or tabs
- the form includes workflow-heavy logic
- the form contains complex dependent inputs
- the form needs a large preview/editing workspace

Default rule:
- **6 fields or fewer** -> prefer modal create/edit
- **more than 6 fields** -> prefer dedicated create/edit pages unless there is a strong reason not to

Show/detail pages may still remain full pages even when create/edit use modals.

## Form Rules

Forms should be built around:
- Form Request as the validation entry point
- model-centered reusable validation
- named routes
- reusable field components

Use shared components for:
- text inputs
- textarea
- number inputs
- select
- checkbox/switch
- file upload
- date input

When a field pattern repeats in more than one module, make it reusable.

For modal CRUD modules:
- keep one shared form partial for create/edit
- keep modal-specific differences minimal
- do not duplicate the full form markup between create and edit modals
- make validation redirects reopen the correct modal state

## Migration Rules

Use one clear concern per migration.

Every new CRUD table should define:
- primary key
- timestamps
- indexes for common lookups
- foreign keys where relations are real

Use soft deletes only when recovery is needed.

If the module supports recovery, prefer:
- `deleted_at`
- `deleted_by`

If the module supports full auditing, consider:
- `created_by`
- `updated_by`
- `deleted_by`

Do not put unrelated schema changes into the same migration.

## Listing Screen Rules

Use one of two listing styles intentionally.

### Server-rendered pagination

Use when:
- the dataset is moderate
- filters are simple
- export is not required

### DataTables or server-side table endpoint

Use when:
- the dataset is large
- searching and sorting must be server-driven
- export tools are required
- the module is operationally heavy

Do not default to DataTables for every module.

### Clickable Row Pattern

When a DataTable listing should navigate to a detail page on row click, use the shared `dtRowLink` helper instead of writing per-module CSS/JS.

Shared assets (already registered in the Sneat layout and Vite):
- `resources/css/layout/datatables.css` — provides the `.dt-row-link` hover style
- `resources/js/modules/dt-row-link.js` — exposes `window.dtRowLink` with `createdRow` and `bind`

Three steps to enable clickable rows on any module:

1. In the controller's DataTable response, expose the detail URL as `show_url`:

```php
return DataTables::eloquent($query)
    ->addColumn('show_url', fn ($record) => route('module.show', $record))
    ->make(true);
```

2. In the view's DataTable config, wire the `createdRow` callback:

```js
const table = $('#module-table').DataTable({
    // ...existing options...
    createdRow: window.dtRowLink.createdRow,
});
```

3. After DataTable init, bind the click handler once:

```js
window.dtRowLink.bind('#module-table');
```

Rules:
- Do not copy the CSS into the view — the hover style is global via the shared stylesheet.
- Do not write a per-module click handler — `dtRowLink.bind` delegates to the `tr.dt-row-link` selector and ignores clicks on `a`, `button`, `form`, `input`, `select`, `textarea`, and `label` children.
- Rows without a `show_url` in the row data remain non-interactive, so the pattern is safe for mixed result sets.
- Permission-gate the `show_url` in the controller when only some users should see the detail page.

Behavior:
- Hover applies a subtle primary-tinted background, top/bottom borders, and a 3px left accent on the first cell.
- Click anywhere on the row (except interactive children) navigates to `show_url`.
- Cursor becomes `pointer` only on rows that have a `show_url`.

## Detail Page Components

Shared Blade components for module `show` pages. Use them instead of re-implementing the same markup in every detail view.

### `<x-relations.tabs>`

Renders a tabbed card of related records, each tab backed by a server-side DataTable. Use it when a record has one or more collections of related rows that should be browsable (sortable, searchable, paginated) without leaving the detail page.

Shared assets (already wired in the Sneat layout and Vite):
- `resources/views/components/relations/tabs.blade.php` — the component itself
- `resources/js/modules/dt-row-link.js` — row-click navigation, reused automatically

Tab config lives in a dedicated builder class at `app/View/RelationTabs/{Model}ShowTabs.php` with a static `build({Model} $record): array` method, so the view stays declarative and the config is unit-testable.

Four pieces wire this up on any detail page:

1. A builder class returning the tab config array:

```php
namespace App\View\RelationTabs;

use App\Models\Company;

class CompanyShowTabs
{
    public static function build(Company $company): array
    {
        return [
            [
                'id' => 'company-related-locations',
                'label' => 'Lokasi',
                'count' => $company->locations_count,
                'tableId' => 'company-locations-table',
                'dataUrl' => route('companies.locations.data', $company),
                'columns' => [
                    ['data' => 'name_link', 'name' => 'name', 'title' => 'Nama Lokasi'],
                    ['data' => 'notes', 'name' => 'notes', 'title' => 'Catatan', 'orderable' => false],
                ],
                'emptyMessage' => 'Belum ada lokasi yang terkait dengan company ini.',
            ],
            // ... additional tabs
        ];
    }
}
```

2. Controller `show` method that loads the counts and passes the config to the view:

```php
public function show(Company $company): View
{
    $company->loadCount(['locations', 'departments'])
        ->load(['creator', 'updater', 'deleter']);

    $relationTabs = CompanyShowTabs::build($company);

    return view('companies.show', compact('company', 'relationTabs'));
}
```

3. One DataTables endpoint per tab, following the existing `datatables()->of(...)` pattern. Always expose `show_url` so row clicks navigate correctly via `dtRowLink`:

```php
public function locationsData(Company $company): JsonResponse
{
    return datatables()->of(
        $company->locations()->select(['id', 'company_id', 'name', 'notes'])
    )
        ->addColumn('name_link', fn (Location $l): string => '<a href="'.route('locations.show', $l).'" class="text-success fw-semibold">'.e($l->name).'</a>')
        ->addColumn('show_url', fn (Location $l): string => route('locations.show', $l))
        ->editColumn('notes', fn (Location $l): string => $l->notes ?: '-')
        ->rawColumns(['name_link'])
        ->make(true);
}
```

4. A single invocation in the `show` view:

```blade
<x-relations.tabs :tabs="$relationTabs" />
```

#### Tab config shape

Each entry in `$tabs`:

| Key | Type | Purpose |
|---|---|---|
| `id` | string | Unique id for the `.tab-pane` container (must be unique on the page) |
| `label` | string | Visible tab-button text |
| `count` | int \| null | Optional badge count; omit or pass `null` to hide the badge |
| `tableId` | string | Unique DOM id for the DataTable |
| `dataUrl` | string | Server-side DataTables endpoint URL |
| `columns` | array | yajra-style column definitions (`data`, `name`, `title`, `orderable`, etc.) |
| `emptyMessage` | string | Indonesian empty-state message shown when the response has zero rows |

#### Component behavior

- The first tab initializes on page load; the rest initialize lazily on `shown.bs.tab`.
- Initialized tables are cached, and `columns.adjust()` is called when a tab is re-shown to fix the known "DataTables inside hidden tab" width miscalc.
- `dom: 'lfrtip'` is set so the layout matches the project's index pages instead of the DataTables 2.x default grid.
- Row-click navigation is wired automatically when the row data contains `show_url`.
- The component also accepts a `cardClass` prop (default `mt-4`) to override outer card spacing.

#### Rules

- Name the builder `{Model}ShowTabs` and place it in `app/View/RelationTabs/` so future detail pages have an obvious home.
- Keep route names scoped to the parent resource, e.g. `companies.locations.data`, `manufacturers.asset-models.data`.
- Server-render any HTML column (links, badges) via `addColumn(...)` + `rawColumns([...])`. Do not try to pass client-side `render` functions through the `columns` config — they are not forwarded.
- Use `loadCount` (not `load`) on the parent record when you only need the number for the badge; it avoids materializing the full relation.
- Gate each data endpoint with the same `permission:{module}.view` middleware as the parent `show` route.
- Each `dataUrl` endpoint should constrain its query to the parent record (e.g. `$company->locations()`), not the global collection.

#### When not to use

- If the detail page only needs a handful of rows that fit on screen with no sort/search, a plain `@forelse` table is simpler and does not require a new endpoint.
- If there is only one related collection and tabs add no value, render a single card with a DataTable inline rather than forcing a one-tab `<x-relations.tabs>`.

## Trash Pattern

If a module supports soft delete, it should also support:
- trash listing
- restore
- permanent delete

This pattern should be implemented consistently across modules.

## Service Extraction Rules

Create a service class when:
- a mutation affects multiple models
- the same mutation is reused
- transaction boundaries matter
- external APIs are involved
- the controller method stops being simple coordination

Do not create services for trivial one-line persistence unless there is a clear reuse case.

## Query Extraction Rules

Create a dedicated query class or query method when:
- the listing query becomes large
- filtering logic is reused
- joins or aggregates become non-trivial
- the same query feeds multiple screens or exports

Keep Blade templates free of query logic.

## Testing Rules

Each module should have focused feature tests for:
- listing access
- create success
- create validation failure
- update success
- update validation failure
- delete
- restore
- force delete

Add extra tests for:
- permissions
- workflows
- special business rules

Use factories for test data.

## Growth Path

Every module should start simple, but the structure must allow growth.

Recommended evolution path:

1. model + migration
2. requests + controller
3. views + routes + permissions
4. tests
5. extract services/queries if complexity grows

Do not over-engineer at the start.
Do not leave large modules under-structured once they grow.

## Naming Rules

Use:
- singular model class names
- plural route/resource names
- descriptive request names
- descriptive service names

Examples:
- `Product`
- `products.index`
- `ProductStoreRequest`
- `SyncProductCatalogService`

Avoid:
- generic names like `DataController`
- names that collide with framework concepts
- abbreviations unless they are standard in the business domain

## Module Checklist

When building a new CRUD module, follow this checklist:

1. Create the migration.
2. Create the model.
3. Create the factory.
4. Create the seeder if needed.
5. Create store and update Form Requests.
6. Create the controller.
7. Register named routes.
8. Register permissions.
9. Add menu/navigation entry if needed.
10. Build `index`, `create`, `edit`, `show`, and `trash` views.
11. Add feature tests.
12. Run formatting.
13. Run the minimal relevant tests.

## Final Standard

The structure to follow is:
- thin controller
- Form Request as adapter/entry point
- reusable validation centralized in the model
- explicit model
- named routes
- capability-based permissions
- one module directory in views
- optional service/query extraction when complexity grows
- consistent trash and recovery behavior when soft deletes are used

If a new CRUD module does not fit this structure, redesign it before implementation.

## Form Components Guide

This section documents the reusable Blade form component approach that should be followed when building forms.

### Goal

Use the `x-form.*` component layer as the default way to build forms.

Benefits:
- consistent labels, help text, and validation output
- shared old-input handling
- shared styling and invalid state handling
- centralized JS-driven enhancements for date range picker, rich editor, and FilePond

### Component List

- `x-form.group`
- `x-form.error`
- `x-form.input`
- `x-form.text`
- `x-form.number`
- `x-form.textarea`
- `x-form.select`
- `x-form.checkbox`
- `x-form.switch`
- `x-form.date`
- `x-form.file`
- `x-form.rich-editor`

### General Rules

- Prefer `x-form.*` components over raw form markup for new forms.
- Keep validation keys aligned with the component `name` prop.
- Let components handle `old()` values unless there is a clear reason not to.
- Use `groupClass` for wrapper spacing/layout and `inputClass` for control-level styling.
- Keep labels, help text, and errors inside the component instead of duplicating markup around it.
- Use named routes and backend-generated URLs when passing upload endpoints or editor endpoints.

### Shared Patterns

#### `name`

The `name` prop is the source of truth for:
- input name
- generated id when `id` is omitted
- validation error lookup
- old input resolution

Example:

```blade
<x-form.text name="title" label="Title" />
```

#### `old`

Most value-based components support `old` and default it to `true`.

Use default behavior for normal form pages:

```blade
<x-form.text name="title" label="Title" :value="$record->title" />
```

Disable old-value override only when a fixed value is required:

```blade
<x-form.text name="token_preview" label="Token" :value="$token" :old="false" readonly />
```

#### Error Handling

Components automatically add invalid classes and render the field error via `x-form.error`.

For file arrays, `x-form.file` should support both:
- `field`
- `field.*`

### Base Components

#### `x-form.group`

Wrapper for:
- label
- required marker
- slotted input
- error output
- help text

Use it only when building a new custom field component, not for normal app form fields.

#### `x-form.error`

Dedicated error renderer for a field name.

Use this only inside custom form components.

### Text-Like Inputs

#### `x-form.input`

Base input component.

Typical props:
- `name`
- `label`
- `type`
- `value`
- `required`
- `help`
- `placeholder`
- `id`
- `inputClass`
- `groupClass`
- `old`

Example:

```blade
<x-form.input
    name="slug"
    label="Slug"
    type="text"
    placeholder="auto-generated-slug"
/>
```

#### `x-form.text`

Shorthand around `x-form.input` for text fields.

Use for:
- names
- titles
- slugs
- short descriptions
- simple readonly values

#### `x-form.number`

Shorthand around `x-form.input` with numeric semantics.

Example:

```blade
<x-form.number
    name="sort_order"
    label="Sort Order"
    min="0"
    :value="$record->sort_order"
/>
```

#### `x-form.textarea`

Use for longer text values such as:
- descriptions
- notes
- comments

Example:

```blade
<x-form.textarea
    name="description"
    label="Description"
    rows="4"
    :value="$record->description"
/>
```

### Selection Components

#### `x-form.select`

Supports:
- associative arrays
- flat list arrays
- object/array lists
- custom option slot content

Example:

```blade
<x-form.select
    name="status"
    label="Status"
    :options="[
        'draft' => 'Draft',
        'review' => 'Review',
        'published' => 'Published',
    ]"
    placeholder="Choose status"
/>
```

Object-list example:

```blade
<x-form.select
    name="role_template_id"
    label="Role Template"
    :options="$roleTemplates"
    optionValue="id"
    optionLabel="display_name"
    :value="$user->role_template_id"
/>
```

Slot example:

```blade
<x-form.select name="category_id" label="Category">
    <option value="">Choose category</option>
    @foreach ($categories as $category)
        <option value="{{ $category->id }}">{{ $category->name }}</option>
    @endforeach
</x-form.select>
```

### Boolean Components

#### `x-form.checkbox`

Use for simple boolean fields.

#### `x-form.switch`

Use when the UI should present the boolean as a switch.

Example:

```blade
<x-form.switch
    name="is_active"
    label="Active"
    :checked="$record->is_active"
/>
```

#### Boolean Submission Rule

Checkbox and switch components should not rely on hidden fallback values automatically.

Normalize booleans with:
- `$request->boolean('field')`
- validated request data plus explicit normalization where needed

### Date Component

#### `x-form.date`

Supports:
- native browser date input
- daterangepicker mode

Native example:

```blade
<x-form.date
    name="publish_date"
    label="Publish Date"
    :value="$record->publish_date?->format('Y-m-d')"
/>
```

Single date picker example:

```blade
<x-form.date
    name="event_date"
    label="Event Date"
    mode="daterangepicker"
    :single="true"
    value="2026-04-10"
/>
```

Range example:

```blade
<x-form.date
    name="booking_window"
    label="Booking Window"
    mode="daterangepicker"
    :single="false"
    value="2026-04-10 - 2026-04-20"
/>
```

Implementation notes:
- Date Range Picker assets are loaded by the component.
- JS initialization should live in `resources/js/modules/date-range-picker.js`.
- If frontend changes do not appear, rebuild frontend assets.

### File Component

#### `x-form.file`

Supports:
- native file input
- FilePond upload mode

Native example:

```blade
<x-form.file
    name="attachment"
    label="Attachment"
    accept="image/*,.pdf"
/>
```

FilePond example:

```blade
<x-form.file
    name="attachment_filepond"
    label="Attachment"
    mode="filepond"
    accept="image/*,.pdf"
    :acceptedFileTypes="['image/png', 'image/jpeg', 'application/pdf']"
    maxFileSize="5MB"
/>
```

Default FilePond endpoints when present:
- `uploads.process`
- `uploads.revert`
- `uploads.load`

Implementation notes:
- JS initialization should live in `resources/js/modules/filepond.js`.
- Existing images can use FilePond preview and poster support.
- For array uploads, use `multiple` and a field name such as `attachments`.
- Backend validation must support either a normal upload or a stored temp path.
- `uploads.load` is for temporary FilePond files only. Do not use a permanent stored path such as `departments/example.jpg` as the preload source.
- For permanent files on S3/CDN, preload FilePond with the public URL, for example `:existingFiles="$record?->imageUrl() ? [$record->imageUrl()] : []"`. This avoids 404s from `/uploads/load` on edit pages such as the department form.

### Rich Editor

#### `x-form.rich-editor`

TipTap-based rich editor component.

Example:

```blade
<x-form.rich-editor
    name="body"
    label="Body"
    :value="$record->body"
    placeholder="Write content"
    minHeight="220px"
/>
```

Implementation notes:
- JS initialization should live in `resources/js/modules/rich-editor.js`.
- The component stores content in a hidden input with the same `name`.
- `imageUploadUrl` should be treated as integration-specific configuration.

### Recommended Usage Pattern

Prefer forms structured like this:

```blade
<form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
    @csrf

    <x-form.text name="title" label="Title" required />

    <x-form.select
        name="status"
        label="Status"
        :options="$statusOptions"
        placeholder="Choose status"
        required
    />

    <x-form.textarea
        name="summary"
        label="Summary"
        rows="4"
    />

    <x-form.rich-editor
        name="body"
        label="Body"
    />

    <x-form.file
        name="cover_image"
        label="Cover Image"
        mode="filepond"
        accept="image/*"
        :acceptedFileTypes="['image/png', 'image/jpeg', 'image/webp']"
    />

    <x-form.switch
        name="is_active"
        label="Active"
        :checked="true"
    />

    <button type="submit" class="btn btn-primary">Save</button>
</form>
```

### Consistency Rules

- Do not duplicate label/error/help markup when a form component already covers the case.
- Extend `x-form.input` or `x-form.group` when creating a new custom field type.
- Keep component props predictable and aligned with existing names like `inputClass`, `groupClass`, `help`, `old`, and `required`.
- Put enhancement JS in `resources/js/modules/{module}.js`.
- Use component-driven asset loading patterns consistently.
- Add or update tests when changing non-trivial component behavior.

### When To Extend Instead Of Rebuild

Create or extend a reusable component when:
- the same field pattern appears in more than one form
- the field needs shared JS behavior
- the field has repeated validation/error/help markup
- the field has a stable prop contract that multiple modules can share

Do not create a new component when:
- the pattern is truly one-off
- the markup is simpler than the abstraction
- the new field would duplicate an existing component with only cosmetic differences
