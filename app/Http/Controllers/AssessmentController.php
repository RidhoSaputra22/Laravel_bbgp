<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AssessmentController extends Controller
{
    private string $menu = 'assessment';

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAccess();

        $datas = Assessment::with(['forms.fields'])
            ->orderByDesc('id')
            ->get();

        return view('pages.admin.assessment.index', [
            'menu' => $this->menu,
            'datas' => $datas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizeAccess();

        return view('pages.admin.assessment.create', [
            'menu' => $this->menu,
            'assessment' => new Assessment([
                'status' => 'draft',
                'is_active' => true,
            ]),
            'fieldTypes' => $this->fieldTypes(),
            'formBuilderData' => [],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $this->validatePayload($request);

        DB::beginTransaction();

        try {
            $assessment = Assessment::create([
                'kode_assessment' => $validated['kode_assessment'],
                'judul' => $validated['judul'],
                'slug' => $this->generateUniqueSlug($validated['judul']),
                'deskripsi' => $validated['deskripsi'] ?? null,
                'petunjuk' => $validated['petunjuk'] ?? null,
                'status' => $validated['status'],
                'is_active' => (bool) ($validated['is_active'] ?? false),
            ]);

            $this->syncForms($assessment, $validated['forms']);

            DB::commit();

            return redirect()->route('assessment.index')->with('message', 'store');
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['assessment' => 'Terjadi kesalahan saat menyimpan data assesment.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->authorizeAccess();

        $assessment = Assessment::with(['forms.fields'])->findOrFail($id);

        return view('pages.admin.assessment.show', [
            'menu' => $this->menu,
            'assessment' => $assessment,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->authorizeAccess();

        $assessment = Assessment::with(['forms.fields'])->findOrFail($id);

        return view('pages.admin.assessment.edit', [
            'menu' => $this->menu,
            'assessment' => $assessment,
            'fieldTypes' => $this->fieldTypes(),
            'formBuilderData' => $this->buildFormBuilderData($assessment),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorizeAccess();

        $assessment = Assessment::with('forms.fields')->findOrFail($id);
        $validated = $this->validatePayload($request, $assessment->id);

        DB::beginTransaction();

        try {
            $assessment->update([
                'kode_assessment' => $validated['kode_assessment'],
                'judul' => $validated['judul'],
                'slug' => $this->generateUniqueSlug($validated['judul'], $assessment->id),
                'deskripsi' => $validated['deskripsi'] ?? null,
                'petunjuk' => $validated['petunjuk'] ?? null,
                'status' => $validated['status'],
                'is_active' => (bool) ($validated['is_active'] ?? false),
            ]);

            $assessment->forms()->delete();
            $this->syncForms($assessment, $validated['forms']);

            DB::commit();

            return redirect()->route('assessment.index')->with('message', 'update');
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['assessment' => 'Terjadi kesalahan saat memperbarui data assesment.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorizeAccess();

        $assessment = Assessment::findOrFail($id);
        $assessment->delete();

        return response()->json([
            'status' => true,
        ]);
    }

    private function authorizeAccess(): void
    {
        abort_unless(
            in_array(session('role'), ['admin', 'superadmin', 'kepala', 'database'], true),
            403
        );
    }

    private function fieldTypes(): array
    {
        return [
            'text' => 'Text',
            'textarea' => 'Textarea',
            'number' => 'Number',
            'email' => 'Email',
            'date' => 'Date',
            'select' => 'Select',
            'radio' => 'Radio',
            'checkbox' => 'Checkbox',
            'file' => 'File',
        ];
    }

    private function validatePayload(Request $request, ?int $assessmentId = null): array
    {
        $validator = Validator::make(
            $request->all(),
            [
                'kode_assessment' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('assessments', 'kode_assessment')->ignore($assessmentId),
                ],
                'judul' => 'required|string|max:255',
                'deskripsi' => 'nullable|string',
                'petunjuk' => 'nullable|string',
                'status' => 'required|in:draft,publish,nonaktif',
                'is_active' => 'nullable|boolean',
                'forms' => 'required|array|min:1',
                'forms.*.judul_form' => 'required|string|max:255',
                'forms.*.kode_form' => 'nullable|string|max:100',
                'forms.*.deskripsi' => 'nullable|string',
                'forms.*.urutan' => 'nullable|integer|min:1',
                'forms.*.is_active' => 'nullable|boolean',
                'forms.*.fields' => 'required|array|min:1',
                'forms.*.fields.*.label' => 'required|string|max:255',
                'forms.*.fields.*.nama_field' => 'required|string|max:100',
                'forms.*.fields.*.tipe_field' => [
                    'required',
                    'string',
                    Rule::in(array_keys($this->fieldTypes())),
                ],
                'forms.*.fields.*.placeholder' => 'nullable|string|max:255',
                'forms.*.fields.*.bantuan' => 'nullable|string',
                'forms.*.fields.*.opsi_field_text' => 'nullable|string',
                'forms.*.fields.*.nilai_default' => 'nullable|string',
                'forms.*.fields.*.lebar_kolom' => 'nullable|string|max:20',
                'forms.*.fields.*.urutan' => 'nullable|integer|min:1',
                'forms.*.fields.*.is_required' => 'nullable|boolean',
                'forms.*.fields.*.is_active' => 'nullable|boolean',
            ],
            [
                'kode_assessment.required' => 'Kode assesment wajib diisi.',
                'kode_assessment.unique' => 'Kode assesment sudah digunakan.',
                'judul.required' => 'Judul assesment wajib diisi.',
                'forms.required' => 'Minimal harus ada satu child form.',
                'forms.*.judul_form.required' => 'Judul child form wajib diisi.',
                'forms.*.fields.required' => 'Setiap child form minimal memiliki satu form-field.',
                'forms.*.fields.*.label.required' => 'Label form-field wajib diisi.',
                'forms.*.fields.*.nama_field.required' => 'Nama field wajib diisi.',
                'forms.*.fields.*.tipe_field.required' => 'Tipe field wajib dipilih.',
            ]
        );

        $validator->after(function ($validator) use ($request) {
            $forms = $request->input('forms', []);
            $fieldTypesWithOptions = ['select', 'radio', 'checkbox'];

            foreach ($forms as $formIndex => $form) {
                $usedFieldNames = [];

                foreach (($form['fields'] ?? []) as $fieldIndex => $field) {
                    $namaField = Str::snake($field['nama_field'] ?? '');

                    if ($namaField === '') {
                        $validator->errors()->add(
                            "forms.$formIndex.fields.$fieldIndex.nama_field",
                            'Nama field tidak boleh kosong.'
                        );
                    }

                    if (in_array($namaField, $usedFieldNames, true)) {
                        $validator->errors()->add(
                            "forms.$formIndex.fields.$fieldIndex.nama_field",
                            'Nama field dalam satu child form harus unik.'
                        );
                    }

                    $usedFieldNames[] = $namaField;

                    if (
                        in_array($field['tipe_field'] ?? '', $fieldTypesWithOptions, true) &&
                        blank($field['opsi_field_text'] ?? null)
                    ) {
                        $validator->errors()->add(
                            "forms.$formIndex.fields.$fieldIndex.opsi_field_text",
                            'Opsi wajib diisi untuk field select, radio, atau checkbox.'
                        );
                    }
                }
            }
        });

        return $validator->validate();
    }

    private function syncForms(Assessment $assessment, array $forms): void
    {
        foreach (array_values($forms) as $formIndex => $formData) {
            $form = $assessment->forms()->create([
                'judul_form' => $formData['judul_form'],
                'kode_form' => $formData['kode_form'] ?: 'FORM-' . str_pad((string) ($formIndex + 1), 2, '0', STR_PAD_LEFT),
                'deskripsi' => $formData['deskripsi'] ?? null,
                'urutan' => (int) ($formData['urutan'] ?? ($formIndex + 1)),
                'is_active' => (bool) ($formData['is_active'] ?? false),
            ]);

            $usedFieldNames = [];

            foreach (array_values($formData['fields']) as $fieldIndex => $fieldData) {
                $fieldName = $this->resolveUniqueFieldName($fieldData['nama_field'], $usedFieldNames);
                $usedFieldNames[] = $fieldName;

                $form->fields()->create([
                    'label' => $fieldData['label'],
                    'nama_field' => $fieldName,
                    'tipe_field' => $fieldData['tipe_field'],
                    'placeholder' => $fieldData['placeholder'] ?? null,
                    'bantuan' => $fieldData['bantuan'] ?? null,
                    'opsi_field' => $this->parseFieldOptions(
                        $fieldData['tipe_field'],
                        $fieldData['opsi_field_text'] ?? null
                    ),
                    'nilai_default' => $fieldData['nilai_default'] ?? null,
                    'validasi' => [
                        'required' => (bool) ($fieldData['is_required'] ?? false),
                        'tipe_field' => $fieldData['tipe_field'],
                    ],
                    'lebar_kolom' => $fieldData['lebar_kolom'] ?? 'col-md-6',
                    'urutan' => (int) ($fieldData['urutan'] ?? ($fieldIndex + 1)),
                    'is_required' => (bool) ($fieldData['is_required'] ?? false),
                    'is_active' => (bool) ($fieldData['is_active'] ?? false),
                ]);
            }
        }
    }

    private function parseFieldOptions(string $fieldType, ?string $rawOptions): ?array
    {
        if (!in_array($fieldType, ['select', 'radio', 'checkbox'], true)) {
            return null;
        }

        $options = preg_split('/[\r\n,]+/', (string) $rawOptions);
        $options = array_values(array_filter(array_map('trim', $options)));

        return $options === [] ? null : $options;
    }

    private function resolveUniqueFieldName(string $rawName, array $usedFieldNames): string
    {
        $baseName = Str::snake($rawName);
        $fieldName = $baseName;
        $counter = 2;

        while (in_array($fieldName, $usedFieldNames, true)) {
            $fieldName = $baseName . '_' . $counter;
            $counter++;
        }

        return $fieldName;
    }

    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 2;

        while (
            Assessment::where('slug', $slug)
                ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function buildFormBuilderData(Assessment $assessment): array
    {
        return $assessment->forms->map(function ($form) {
            return [
                'judul_form' => $form->judul_form,
                'kode_form' => $form->kode_form,
                'deskripsi' => $form->deskripsi,
                'urutan' => $form->urutan,
                'is_active' => $form->is_active,
                'fields' => $form->fields->map(function ($field) {
                    return [
                        'label' => $field->label,
                        'nama_field' => $field->nama_field,
                        'tipe_field' => $field->tipe_field,
                        'placeholder' => $field->placeholder,
                        'bantuan' => $field->bantuan,
                        'opsi_field_text' => $field->opsi_field ? implode(', ', $field->opsi_field) : null,
                        'nilai_default' => $field->nilai_default,
                        'lebar_kolom' => $field->lebar_kolom,
                        'urutan' => $field->urutan,
                        'is_required' => $field->is_required,
                        'is_active' => $field->is_active,
                    ];
                })->toArray(),
            ];
        })->toArray();
    }
}
