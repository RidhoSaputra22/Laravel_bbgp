@extends('layouts.app', ['title' => 'Tambah Assesment'])

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Tambah Assesment</h1>
            </div>

            <div class="section-body">
                @include('pages.admin.assessment.partials.form', [
                    'assessment' => $assessment,
                    'fieldTypes' => $fieldTypes,
                    'formBuilderData' => $formBuilderData,
                    'formAction' => route('assessment.store'),
                    'httpMethod' => 'POST',
                    'submitLabel' => 'Simpan Assesment',
                    'pageTitle' => 'Form Builder Assesment',
                ])
            </div>
        </section>
    </div>
@endsection
