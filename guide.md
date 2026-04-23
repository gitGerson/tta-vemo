# Form Components Guide

This guide documents the reusable Blade form components under `resources/views/components/form` and the rules for using them consistently in future development.

## Goal

Use the `x-form.*` component layer as the default way to build forms in this project.

Benefits:
- consistent labels, help text, and validation output
- shared old-input handling
- shared styling and invalid state handling
- centralized JS-driven enhancements for date range picker, rich editor, and FilePond

## Component List

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

## General Rules

- Prefer `x-form.*` components over raw Bootstrap form markup for new forms.
- Keep validation keys aligned with the component `name` prop.
- Let components handle `old()` values unless there is a clear reason to disable that behavior.
- Use `groupClass` for wrapper spacing/layout and `inputClass` for control-level styling.
- Keep labels, help text, and errors inside the component instead of duplicating markup around it.
- Use named routes and backend-generated URLs when passing upload endpoints or editor endpoints.

## Shared Patterns

### `name`

The `name` prop is the source of truth for:
- the input name
- the generated id when `id` is omitted
- validation error lookup
- old input resolution

Example:

```blade
<x-form.text name="title" label="Title" />
```

### `old`

Most value-based components support `old` and default it to `true`.

Use default behavior for normal form pages:

```blade
<x-form.text name="title" :value="$post->title" />
```

Disable old-value override only when you need a fixed value:

```blade
<x-form.text name="token_preview" :value="$token" :old="false" readonly />
```

### Error Handling

Components automatically add invalid classes and render the field error via `x-form.error`.

For file arrays, `x-form.file` already checks both:
- `field`
- `field.*`

## Base Components

### `x-form.group`

Wrapper for:
- label
- required marker
- slotted input
- error output
- help text

Props:
- `name`
- `label`
- `required`
- `help`
- `for`
- `class`
- `wrapperClass`

Use it only when building a new custom component, not for normal app form fields.

### `x-form.error`

Dedicated error renderer for a field name.

Use this only inside custom form components.

## Text-like Inputs

### `x-form.input`

Base input component.

Props:
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

### `x-form.text`

Shorthand around `x-form.input` for text fields.

Use for:
- titles
- names
- slugs
- short descriptions
- simple readonly values

Example:

```blade
<x-form.text
    name="name"
    label="Name"
    :value="$category->name"
    required
/>
```

### `x-form.number`

Shorthand around `x-form.input` with numeric semantics.

Example:

```blade
<x-form.number
    name="sort_order"
    label="Sort Order"
    min="0"
    :value="$category->sort_order"
/>
```

### `x-form.textarea`

Props:
- `name`
- `label`
- `value`
- `rows`
- `required`
- `help`
- `placeholder`
- `id`
- `inputClass`
- `groupClass`
- `old`

Example:

```blade
<x-form.textarea
    name="description"
    label="Description"
    rows="4"
    :value="$category->description"
/>
```

## Selection Components

### `x-form.select`

Supports:
- associative arrays
- flat list arrays
- object/array lists
- custom option slot content

Props:
- `name`
- `label`
- `options`
- `value`
- `placeholder`
- `required`
- `help`
- `id`
- `inputClass`
- `groupClass`
- `optionValue`
- `optionLabel`
- `old`

Associative array example:

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

Object list example:

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

Custom options example:

```blade
<x-form.select name="category_id" label="Category">
    <option value="">Choose category</option>
    @foreach ($categories as $category)
        <option value="{{ $category->id }}">{{ $category->name }}</option>
    @endforeach
</x-form.select>
```

## Boolean Components

### `x-form.checkbox`

Props:
- `name`
- `label`
- `value`
- `checked`
- `help`
- `id`
- `inputClass`
- `groupClass`
- `old`

Example:

```blade
<x-form.checkbox
    name="is_featured"
    label="Featured"
    :checked="$post->is_featured"
/>
```

### `x-form.switch`

Same API as `x-form.checkbox`, but rendered with Bootstrap switch markup.

Example:

```blade
<x-form.switch
    name="is_active"
    label="Active"
    :checked="$record->is_active"
/>
```

### Boolean Submission Rule

Checkbox and switch components do not inject a hidden fallback input automatically.

If your controller expects explicit false values, normalize with:
- `$request->boolean('field')`
- request validation plus controller normalization

## Date Component

### `x-form.date`

Supports two modes:
- native browser date input
- Date Range Picker mode

Props:
- `name`
- `label`
- `value`
- `required`
- `help`
- `placeholder`
- `id`
- `inputClass`
- `groupClass`
- `old`
- `mode`
- `single`
- `format`
- `minDate`
- `maxDate`
- `opens`
- `drops`
- `autoApply`

### Native Mode

Default mode:

```blade
<x-form.date
    name="publish_date"
    label="Publish Date"
    :value="$post->publish_date?->format('Y-m-d')"
/>
```

### Date Range Picker Mode

Single date:

```blade
<x-form.date
    name="event_date"
    label="Event Date"
    mode="daterangepicker"
    :single="true"
    value="2026-04-10"
/>
```

Range:

```blade
<x-form.date
    name="booking_window"
    label="Booking Window"
    mode="daterangepicker"
    :single="false"
    value="2026-04-10 - 2026-04-20"
/>
```

### Date Implementation Notes

- Date Range Picker assets are loaded by the component.
- JS initialization lives in `resources/js/modules/date-range-picker.js`.
- If frontend changes do not appear, rebuild or run Vite.

## File Component

### `x-form.file`

Supports two modes:
- native file input
- FilePond upload mode

Props:
- `name`
- `label`
- `required`
- `help`
- `id`
- `mode`
- `multiple`
- `accept`
- `maxFileSize`
- `acceptedFileTypes`
- `filepondProcessUrl`
- `filepondRevertUrl`
- `filepondLoadUrl`
- `existingFiles`
- `locale`
- `inputClass`
- `groupClass`

### Native File Mode

```blade
<x-form.file
    name="attachment"
    label="Attachment"
    accept="image/*,.pdf"
/>
```

### FilePond Mode

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

### Default FilePond Endpoints

If these routes exist, the component uses them automatically:
- `uploads.process`
- `uploads.revert`
- `uploads.load`

Those are currently backed by:
- `App\Http\Controllers\FileUploadController`
- `App\Services\FilePondUploadService`

### Existing File Preload

Use `existingFiles` to preload already-stored files.

Simple string source:

```blade
<x-form.file
    name="cover_image"
    label="Cover Image"
    mode="filepond"
    :existingFiles="[asset('assets/logo.png')]"
/>
```

Detailed preload item:

```blade
<x-form.file
    name="cover_image"
    label="Cover Image"
    mode="filepond"
    :existingFiles="[[
        'source' => asset('assets/logo.png'),
        'options' => [
            'type' => 'local',
            'file' => [
                'name' => 'logo.png',
                'size' => 58231,
                'type' => 'image/png',
            ],
        ],
    ]]"
/>
```

### FilePond Implementation Notes

- JS initialization lives in `resources/js/modules/filepond.js`.
- Existing images use FilePond image preview plus file poster support.
- The component preserves uploaded temp paths and existing file sources on form submit.
- For array uploads, use `multiple` and a field name like `attachments`.
- Backend validation must accept either:
  - a normal uploaded file
  - a temp path string from `FilePondUploadService`

### Backend Pattern For FilePond Fields

Validation pattern:

```php
'attachment_filepond' => [
    'nullable',
    function (string $attribute, mixed $value, Closure $fail) use ($filePondUploads): void {
        if ($value === null || $value === '') {
            return;
        }

        if ($value instanceof UploadedFile) {
            validator(
                ['upload' => $value],
                ['upload' => ['file', 'mimes:jpg,jpeg,png,gif,webp,pdf', 'max:2048']]
            )->validate();

            return;
        }

        ($filePondUploads->tempPathValidationRule())($attribute, $value, $fail);
    },
],
```

Storage pattern:

```php
$storedPath = $this->filePondUploads->storeFromRequestField(
    $request,
    'attachment_filepond',
    'tmp/example/filepond'
);
```

## Rich Editor

### `x-form.rich-editor`

TipTap-based rich editor component.

Props:
- `name`
- `label`
- `value`
- `required`
- `help`
- `placeholder`
- `id`
- `inputClass`
- `groupClass`
- `old`
- `minHeight`
- `imageUploadUrl`

Example:

```blade
<x-form.rich-editor
    name="body"
    label="Body"
    :value="$post->body"
    placeholder="Write content"
    minHeight="220px"
/>
```

Toolbar features:
- paragraph
- heading 1
- heading 2
- heading 3
- bold
- italic
- underline
- strike
- bullet list
- ordered list
- align left
- align center
- align right
- justify
- blockquote
- code block
- horizontal rule
- link
- image
- undo
- redo

### Rich Editor Implementation Notes

- JS initialization lives in `resources/js/modules/rich-editor.js`.
- The component stores content in a hidden input with the same `name`.
- `imageUploadUrl` is reserved for image upload integration.
- If TipTap dependencies are changed, frontend assets must be rebuilt.

## Recommended Usage Pattern

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

## Consistency Rules For Future Development

- Do not introduce raw duplicate markup for label, error, and help text when a form component already covers the use case.
- Extend `x-form.input` or `x-form.group` when creating a new custom field type.
- Keep component props predictable and aligned with existing names like `inputClass`, `groupClass`, `help`, `old`, and `required`.
- Put enhancement JS in `resources/js/modules/{module}.js`.
- Register new frontend modules in `vite.config.js`.
- Use `@once` with `@push('styles')` and `@push('scripts')` inside components for third-party assets.
- Keep demo coverage updated in `resources/views/form-demo/index.blade.php` when adding a new reusable component.
- Add or update tests when changing component contracts, especially for rendered markup and non-trivial modes like FilePond or rich editor.

## DataTable Components

Use `x-datatable.*` components for server-side DataTables pages.

Components:
- `x-datatable.card`
- `x-datatable.table`
- `x-datatable.scripts`

Normal table page:

```blade
<x-datatable.card title="Daftar Produk">
    <x-slot:actions>
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Tambah Produk
        </a>
    </x-slot:actions>

    <x-datatable.table
        id="products-table"
        :columns="['No', 'Nama', 'Status', 'Aksi']"
    />
</x-datatable.card>

<x-datatable.scripts table-id="products-table" ajax="{{ route('products.data') }}">
    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
    { data: 'name', name: 'name' },
    { data: 'status', name: 'status', orderable: false, searchable: false },
    { data: 'action', name: 'action', orderable: false, searchable: false }
</x-datatable.scripts>
```

Clickable rows:

```blade
<x-datatable.scripts table-id="products-table" ajax="{{ route('products.data') }}" clickable-rows>
    { data: 'name', name: 'name' },
    { data: 'action', name: 'action', orderable: false, searchable: false }
</x-datatable.scripts>
```

Controller data for clickable rows:

```php
->addColumn('row_url', function ($product) {
    return route('products.show', $product->id);
})
```

Trash table with date rendering:

```blade
<x-datatable.scripts table-id="trash-table" ajax="{{ route('products.trash.data') }}">
    { data: 'id', name: 'id' },
    { data: 'name', name: 'name' },
    {
        data: 'deleted_at',
        name: 'deleted_at',
        render: function(data) {
            return moment(data).format('DD MMM YYYY HH:mm');
        }
    },
    { data: 'action', name: 'action', orderable: false, searchable: false }
</x-datatable.scripts>
```

Server-side action partial:

```php
->addColumn('action', function ($product) {
    return view('datatable.actions.products', compact('product'))->render();
})
->rawColumns(['action'])
```

Action partial buttons:

```blade
<div class="d-inline-flex align-items-center gap-1">
    <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-info" title="Lihat" aria-label="Lihat">
        <i class="bx bx-show"></i>
    </a>

    <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display: inline;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" title="Hapus" aria-label="Hapus" onclick="return confirm(@js('Yakin ingin menghapus produk ini?'))">
            <i class="bx bx-trash"></i>
        </button>
    </form>
</div>
```

Rules:
- Put `x-datatable.scripts` directly in the Blade file, not inside another `@push('scripts')`.
- Keep DataTables columns in the script slot so raw JS render functions still work.
- Use `variable="productsEsbTable"` when other scripts need `window.productsEsbTable`.
- Use `clickable-rows` only when every returned row has a valid `row_url` show route.
- Set `:export-exclude-last-column="false"` only when the old table exported every column.
- Render action HTML through `resources/views/datatable/actions/*.blade.php`, not controller string concatenation.
- Keep action partials explicit: use normal `<a>` tags for GET actions and forms with `@csrf` / `@method` for destructive actions.
- Add `title` and `aria-label` to every icon-only row action.

## Demo Reference

Live examples are currently available at:
- `resources/views/form-demo/index.blade.php`
- route name: `form-demo.index`

Use that page as the first place to verify:
- component appearance
- invalid state behavior
- JS enhancement behavior
- preload behavior for FilePond

## When To Extend Instead Of Rebuild

Create or extend a reusable component when:
- the same field pattern appears in more than one form
- the field needs shared JS behavior
- the field has repeated validation/error/help markup
- the field has a stable prop contract that multiple modules can share

Do not create a new component when:
- the pattern is truly one-off
- the markup is simpler than the abstraction
- the new field would duplicate an existing component with only cosmetic differences
