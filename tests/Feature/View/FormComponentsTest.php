<?php

namespace Tests\Feature\View;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\Factory as ViewFactory;
use Tests\TestCase;

class FormComponentsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->startSession();
        request()->setLaravelSession(app('session.store'));
    }

    protected function renderComponent(string $template, array $data = [], ?ViewErrorBag $errors = null): string
    {
        app(ViewFactory::class)->share('errors', $errors ?? new ViewErrorBag());
        request()->setLaravelSession(app('session.store'));

        return Blade::render($template, $data);
    }

    public function test_input_renders_label_name_and_old_value(): void
    {
        session()->flashInput(['title' => 'Judul Lama']);

        $html = $this->renderComponent('<x-form.text name="title" label="Judul" />');

        $this->assertStringContainsString('label', $html);
        $this->assertStringContainsString('Judul', $html);
        $this->assertStringContainsString('name="title"', $html);
        $this->assertStringContainsString('value="Judul Lama"', $html);
    }

    public function test_input_shows_invalid_class_and_error_message(): void
    {
        $errors = new ViewErrorBag();
        $errors->put('default', new MessageBag([
            'name' => ['Nama wajib diisi'],
        ]));

        $html = $this->renderComponent('<x-form.text name="name" label="Nama" />', [], $errors);

        $this->assertStringContainsString('is-invalid', $html);
        $this->assertStringContainsString('invalid-feedback', $html);
        $this->assertStringContainsString('Nama wajib diisi', $html);
    }

    public function test_textarea_renders_rows_and_help_text(): void
    {
        $html = $this->renderComponent('<x-form.textarea name="description" label="Deskripsi" rows="5" help="Isi deskripsi" />');

        $this->assertStringContainsString('textarea', $html);
        $this->assertStringContainsString('rows="5"', $html);
        $this->assertStringContainsString('form-text', $html);
        $this->assertStringContainsString('Isi deskripsi', $html);
    }

    public function test_select_marks_selected_option_from_old_input(): void
    {
        session()->flashInput(['category_id' => '2']);

        $html = $this->renderComponent(
            '<x-form.select name="category_id" label="Kategori" :options="$options" placeholder="Pilih" />',
            [
                'options' => [
                    1 => 'A',
                    2 => 'B',
                ],
            ]
        );

        $this->assertStringContainsString('name="category_id"', $html);
        $this->assertMatchesRegularExpression('/<option value="2" selected[^>]*>/', $html);
    }

    public function test_switch_respects_checked_default_and_old_override(): void
    {
        $htmlDefault = $this->renderComponent('<x-form.switch name="is_active" label="Aktif" :checked="true" />');

        $this->assertStringContainsString('checked', $htmlDefault);

        session()->flashInput(['is_active' => '0']);

        $htmlOld = $this->renderComponent('<x-form.switch name="is_active" label="Aktif" :checked="true" />');

        $this->assertStringNotContainsString(' checked', preg_replace('/\s+/', ' ', $htmlOld) ?? $htmlOld);
    }

    public function test_file_native_mode_renders_standard_file_input(): void
    {
        $html = $this->renderComponent('<x-form.file name="image" label="Gambar" accept="image/*" />');

        $this->assertStringContainsString('type="file"', $html);
        $this->assertStringContainsString('class="form-control', $html);
        $this->assertStringContainsString('accept="image/*"', $html);
        $this->assertStringNotContainsString('js-filepond', $html);
    }

    public function test_file_filepond_mode_uses_generic_upload_routes(): void
    {
        $template = <<<'BLADE'
<x-form.file
    name="attachments[]"
    label="Lampiran"
    mode="filepond"
    :multiple="true"
    max-file-size="3MB"
    :accepted-file-types="['image/png','image/jpeg']"
/>
BLADE;

        $html = $this->renderComponent($template);

        $this->assertStringContainsString('js-filepond', $html);
        $this->assertStringContainsString('data-filepond-enabled="1"', $html);
        $this->assertStringContainsString(route('uploads.process'), $html);
        $this->assertStringContainsString(route('uploads.revert'), $html);
        $this->assertStringContainsString(route('uploads.load', ['source' => '']), $html);
    }
}
