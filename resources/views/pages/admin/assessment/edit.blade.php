@extends('layouts.app', ['title' => 'Edit Assesment'])

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Edit Assesment</h1>
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
                    'formAction' => route('assessment.update', $assessment->id),
                    'httpMethod' => 'PUT',
                    'submitLabel' => 'Update Assesment',
                    'pageTitle' => 'Edit Struktur Assesment',
                ])
            </div>
        </section>
    </div>
@endsection
