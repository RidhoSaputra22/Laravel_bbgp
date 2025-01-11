@extends('layouts.app', ['title' => 'Data Berkas'])

@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data {{ ucfirst($menu) }}</h1>
            </div>


            <div class="section-body">


                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <div class="row">

                                    <div class="col-md-12 col-lg-12">
                                        <div class="row mb-4">
                                            <div class="col-md-4">
                                                <button class="btn btn-primary" type="button" data-toggle="modal"
                                                    data-target="#uploadModal">
                                                    <i class="fas fa-plus"></i>
                                                    Upload laporan
                                                </button>
                                            </div>
                                        </div>

                                    </div>

                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-temp">
                                        <thead>
                                            <tr>
                                                <th class="text-center">
                                                    #
                                                </th>
                                                <th>Tanggal laporan </th>
                                                <th>Preview berkas</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datas as $i => $data)
                                                <tr>
                                                    <td>
                                                        {{ ++$i }}
                                                    </td>
                                                    <td>{{ Helper::dateIndo(explode(' ', $data->created_at, -1)[0]) }}</td>
                                                    <td>
                                                        <a href="{{ asset('upload/berkas/' . $data->nama_berkas) }}"
                                                            target="_blank" class="btn btn-icon btn-primary btn-sm">
                                                            Preview
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a href="" data-id="{{ $data->id }}" data-toggle="modal"
                                                            data-target="#uploadModal" class="btn btn-warning my-2"><i
                                                                class="fas fa-edit"></i></a>

                                                        <button onclick="deleteData({{ $data->id }}, 'berkas')"
                                                            class="btn btn-danger">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </section>
    </div>

    {{-- modal --}}
    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Upload berkas</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="#" id="submitForm" enctype="multipart/form-data">
                    @csrf
                    <input name="methodId" type="hidden" id="methodId" value="">
                    <input type="hidden" name="formId" id="formId" value="">
                    <div class="modal-body">
                        <div class="fallback">
                            <a href="{{ asset('upload/berkas/' . $data->nama_berkas) }}" target="_blank"
                                class="btn btn-icon btn-primary btn-sm btn-update-laporan mb-3">
                                Preview laporan
                            </a>
                            <input name="nama_berkas" required type="file" class="form-control" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" id="submitBtn" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('library/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('library/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('js/page/modules-datatables.js') }}"></script>
        <script src="{{ asset('js/page/bootstrap-modal.js') }}"></script>
        <!-- Page Specific JS File -->
        <script>
            $(document).ready(function() {
                $('.btn-update-laporan').hide();
                $('#uploadModal').on('hidden.bs.modal', function() {
                    $('#submitForm')[0].reset();
                    $('#formId').val('');
                    $('#methodId').val('');
                });

                // Handle edit button click
                $('.btn-warning').on('click', function(e) {
                    e.preventDefault();
                    const id = $(this).data('id');
                    let url = "{{ route('berkas.edit', ':id') }}"
                    url = url.replace(':id', id)

                    $('#formId').val(id);

                    $.ajax({
                        url: url,
                        method: 'GET',
                        success: function(response) {
                            $('#nama_berkas_old').val(response.data.nama_berkas);
                            $('#methodId').val('PUT');
                            if (id != null || id != '' || id != undefined) {
                                $('.btn-update-laporan').show();
                                $('.btn-update-laporan').attr('href',
                                    `{{ asset('upload/berkas/') }}/${response.data.nama_berkas}`
                                    );
                            } else {
                                $('.btn-update-laporan').hide();
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to fetch data'
                            });
                        }
                    });
                });

                // Handle form submission
                $('#submitBtn').on('click', function(e) {
                    e.preventDefault();

                    const formData = new FormData($('#submitForm')[0]);
                    const id = $('#formId').val();
                    const method = $('#methodId').val() || 'POST';

                    const url = method === 'PUT' ? '{{ route('berkas.update') }}' :
                        '{{ route('berkas.store') }}';
                    method === 'PUT' ? formData.append('_method', 'PUT') : ''

                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                'content')
                        },
                        url: url,
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $('#uploadModal').modal('hide');
                            swal({
                                icon: response.status || 'success',
                                title: response.status || 'Success',
                                text: response.message
                            }).then((result) => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            let errors = xhr.responseJSON.errors;
                            let errorMessage = '';

                            if (errors) {
                                errorMessage = Object.values(errors).flat().join('\n');
                            } else {
                                errorMessage = xhr.responseJSON.message;
                            }
                            swal({
                                icon: 'error',
                                title: 'Error',
                                text: 'Periksa kembali file yang anda upload (.pdf)'
                            });
                        }
                    });
                });
            })
        </script>
    @endpush
@endsection
