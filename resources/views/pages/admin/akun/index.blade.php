@extends('layouts.app', ['title' => 'Data Akun'])

@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data Akun</h1>
            </div>


            <div class="section-body">


                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <div class="row">

                                    <div class="col-md-12 col-lg-12">
                                        <form action="{{ route('akun.store') }}" method="POST">
                                            @csrf
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Nomor KTP</label>
                                                                <input name="no_ktp" required
                                                                    placeholder="Masukkan nomor ktp" type="number"
                                                                    class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Nama</label>
                                                                <input name="name" required
                                                                    placeholder="Masukkan Nama Akun" type="text"
                                                                    class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Username</label>
                                                                <input name="username" required
                                                                    placeholder="Masukkan Usernam untuk login"
                                                                    type="text" class="form-control">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Password</label>
                                                                <input name="password" required
                                                                    placeholder="Masukkan Password" type="password"
                                                                    class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Role</label>
                                                                <select name="role" required placeholder="Masukkan Akun"
                                                                    class="form-control selectric">
                                                                    <option value="">-- Pilih Role Akun --</option>
                                                                    {{-- <option value="kepala">Kepala Balai</option> --}}
                                                                    {{-- <option value="keuangan">Keuangan</option> --}}
                                                                    {{-- <option value="kepegawaian">Kepegawaian</option> --}}
                                                                    {{-- <option value="kegiatan">Kegiatan</option> --}}
                                                                    <option value="tenaga pendidik">Tenaga Pendidik</option>
                                                                    <option value="tenaga kependidikan">Tenaga Kependidikan</option>
                                                                    <option value="stakeholder">Stakeholder</option>
                                                                    <option value="pegawai">Pegawai BBGTK</option>
                                                                    {{-- <option value="database">Database</option> --}}
                                                                    <option value="superadmin">Super Admin</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">

                                                        <div class="col-md-4">

                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="fas fa-plus"></i>
                                                                Tambah Data Akun
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                        </form>
                                    </div>

                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-temp">
                                        <thead>
                                            <tr>
                                                <th class="text-center">
                                                    #
                                                </th>
                                                <th>Nama </th>
                                                <th>Username </th>
                                                <th>Role</th>
                                                <th></th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datas as $i => $data)
                                                <tr>
                                                    <td>
                                                        {{ ++$i }}
                                                    </td>
                                                    <td>{{ $data->name }}</td>
                                                    <td>{{ $data->username }}</td>
                                                    <td>{{ $data->role }}</td>
                                                    <td></td>
                                                    <td>
                                                        <a href="{{ route('akun.edit', $data->id) }} "
                                                            class="btn btn-warning my-2"><i class="fas fa-edit"></i></a>

                                                        <button onclick="deleteData({{ $data->id }}, 'akun')"
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

    @push('scripts')
        <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('library/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('library/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('js/page/modules-datatables.js') }}"></script>
        <!-- Page Specific JS File -->
    @endpush
@endsection
