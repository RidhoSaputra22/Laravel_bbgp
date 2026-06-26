@extends('layouts.app', ['title' => 'Tambah Assesment'])

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Tambah Assesment</h1>
                <div class="section-header-breadcrumb">
                    <button type="button" class="btn btn-primary" data-toggle="modal"
                        data-target="#assessmentPreviewModal">
                        <i class="fas fa-eye"></i> Preview Form
                    </button>
                </div>
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
