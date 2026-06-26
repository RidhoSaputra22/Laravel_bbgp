<?php

namespace Database\Seeders;

use App\Models\Assessment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AssessmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assessments = [
            [
                'kode_assessment' => 'ASM-KOMP-001',
                'judul' => 'Assessment Kompetensi Fasilitator',
                'deskripsi' => 'Form assessment untuk memetakan kompetensi awal fasilitator sebelum mengikuti program.',
                'petunjuk' => 'Isi setiap field sesuai kondisi terbaru. Field dapat dikembangkan sesuai kebutuhan unit kerja.',
                'status' => 'publish',
                'is_active' => true,
                'forms' => [
                    [
                        'judul_form' => 'Profil Peserta',
                        'kode_form' => 'FORM-PROFIL',
                        'deskripsi' => 'Data identitas dasar peserta assessment.',
                        'urutan' => 1,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Nama Lengkap',
                                'deskripsi' => 'Tuliskan nama lengkap peserta sesuai identitas resmi atau data administrasi yang berlaku.',
                                'nama_field' => 'nama_lengkap',
                                'tipe_field' => 'text',
                                'placeholder' => 'Masukkan nama lengkap',
                                'bantuan' => 'Gunakan nama sesuai data administrasi.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Email Aktif',
                                'deskripsi' => 'Gunakan alamat email yang aktif dipakai untuk menerima informasi lanjutan assessment.',
                                'nama_field' => 'email_aktif',
                                'tipe_field' => 'email',
                                'placeholder' => 'contoh@bbgtk.go.id',
                                'bantuan' => 'Email digunakan untuk tindak lanjut assessment.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Jabatan Saat Ini',
                                'deskripsi' => 'Pilih jabatan utama yang saat ini paling menggambarkan peran Anda.',
                                'nama_field' => 'jabatan_saat_ini',
                                'tipe_field' => 'select',
                                'placeholder' => 'Pilih jabatan',
                                'bantuan' => 'Pilih jabatan yang paling sesuai.',
                                'opsi_field' => ['Guru', 'Pengawas', 'Kepala Sekolah', 'Staf BBGTK'],
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Lama Pengalaman (tahun)',
                                'deskripsi' => 'Isi total pengalaman kerja yang relevan dalam satuan tahun penuh.',
                                'nama_field' => 'lama_pengalaman',
                                'tipe_field' => 'number',
                                'placeholder' => '0',
                                'bantuan' => 'Tuliskan jumlah tahun pengalaman kerja.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => false, 'min' => 0],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 4,
                                'is_required' => false,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => 'Refleksi Kompetensi',
                        'kode_form' => 'FORM-REFLEKSI',
                        'deskripsi' => 'Isian refleksi diri untuk melihat kesiapan fasilitator.',
                        'urutan' => 2,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Area Kompetensi Terkuat',
                                'deskripsi' => 'Pilih satu area kompetensi yang menurut Anda paling menonjol saat ini.',
                                'nama_field' => 'area_kompetensi_terkuat',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Pilih satu area yang paling dominan.',
                                'opsi_field' => [
                                    ['label' => 'A', 'value' => 'Perencanaan'],
                                    ['label' => 'B', 'value' => 'Fasilitasi'],
                                    ['label' => 'C', 'value' => 'Evaluasi'],
                                    ['label' => 'D', 'value' => 'Pelaporan'],
                                ],
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Topik yang Ingin Diperdalam',
                                'deskripsi' => 'Anda dapat memilih lebih dari satu topik sesuai kebutuhan pengembangan diri.',
                                'nama_field' => 'topik_yang_ingin_diperdalam',
                                'tipe_field' => 'checkbox',
                                'placeholder' => null,
                                'bantuan' => 'Bisa memilih lebih dari satu topik.',
                                'opsi_field' => ['Manajemen Kelas', 'Penyusunan Modul', 'Literasi Digital', 'Coaching'],
                                'nilai_default' => null,
                                'validasi' => ['required' => false],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => false,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Catatan Refleksi',
                                'deskripsi' => 'Jelaskan pengalaman, tantangan, atau kebutuhan belajar yang ingin Anda sampaikan.',
                                'nama_field' => 'catatan_refleksi',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan refleksi singkat Anda',
                                'bantuan' => 'Jelaskan tantangan utama yang sedang dihadapi.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => false],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => false,
                                'is_active' => true,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'kode_assessment' => 'ASM-MONEV-002',
                'judul' => 'Assessment Monitoring Kegiatan',
                'deskripsi' => 'Template assessment untuk monitoring kegiatan lapangan dan pengumpulan eviden.',
                'petunjuk' => 'Lengkapi form sesuai kebutuhan monitoring. Gunakan Tipe Pertanyaan yang tersedia untuk menyusun form baru.',
                'status' => 'draft',
                'is_active' => true,
                'forms' => [
                    [
                        'judul_form' => 'Data Pelaksanaan',
                        'kode_form' => 'FORM-PELAKSANAAN',
                        'deskripsi' => 'Ringkasan kegiatan yang sedang dimonitor.',
                        'urutan' => 1,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Nama Kegiatan',
                                'deskripsi' => 'Masukkan nama kegiatan atau program yang sedang dipantau pada assessment ini.',
                                'nama_field' => 'nama_kegiatan',
                                'tipe_field' => 'text',
                                'placeholder' => 'Masukkan nama kegiatan',
                                'bantuan' => 'Isi sesuai judul kegiatan resmi.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-8',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Tanggal Pelaksanaan',
                                'deskripsi' => 'Tentukan tanggal utama pelaksanaan kegiatan yang menjadi objek assessment.',
                                'nama_field' => 'tanggal_pelaksanaan',
                                'tipe_field' => 'date',
                                'placeholder' => null,
                                'bantuan' => 'Tanggal utama pelaksanaan assessment.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-4',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Unggah Dokumen Eviden',
                                'deskripsi' => 'Lampirkan dokumen pendukung yang relevan seperti laporan, foto, atau eviden kegiatan lainnya.',
                                'nama_field' => 'dokumen_eviden',
                                'tipe_field' => 'file',
                                'placeholder' => null,
                                'bantuan' => 'Contoh: PDF laporan, foto, atau lampiran pendukung.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => false],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => false,
                                'is_active' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($assessments as $item) {
            $forms = $item['forms'];
            unset($item['forms']);

            $assessment = Assessment::updateOrCreate(
                ['kode_assessment' => $item['kode_assessment']],
                [
                    'judul' => $item['judul'],
                    'slug' => Str::slug($item['judul']),
                    'deskripsi' => $item['deskripsi'],
                    'petunjuk' => $item['petunjuk'],
                    'status' => $item['status'],
                    'is_active' => $item['is_active'],
                ]
            );

            $assessment->forms()->delete();

            foreach ($forms as $formData) {
                $fields = $formData['fields'];
                unset($formData['fields']);

                $form = $assessment->forms()->create($formData);

                foreach ($fields as $fieldData) {
                    $form->fields()->create($fieldData);
                }
            }
        }
    }
}
