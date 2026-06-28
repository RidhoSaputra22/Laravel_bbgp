<?php

namespace Database\Seeders;

use App\Models\Assessment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AssessmentProtofolioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assessments = [
            [
                'kode_assessment' => 'ASM-PORTOFOLIO-001',
                'judul' => 'Instrumen Portofolio Kompetensi Guru',
                'deskripsi' => 'Instrumen portofolio untuk memetakan identitas, riwayat pendidikan, pengalaman, prestasi, karya, dan refleksi diri responden.',
                'petunjuk' => 'Isilah data berikut secara jujur dan lengkap. Sertakan bukti dokumen pendukung pada setiap bagian yang relevan.',
                'status' => 'publish',
                'is_active' => true,
                'forms' => [
                    [
                        'judul_form' => 'Identitas Responden',
                        'kode_form' => 'FORM-IDENTITAS',
                        'deskripsi' => 'Data identitas dasar responden dalam instrumen portofolio.',
                        'urutan' => 1,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Nama Lengkap',
                                'deskripsi' => 'Tuliskan nama lengkap sesuai identitas resmi.',
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
                                'label' => 'NIP/NUPTK',
                                'deskripsi' => 'Masukkan Nomor Induk Pegawai atau Nomor Unik Pendidik dan Tenaga Kependidikan.',
                                'nama_field' => 'nip_nuptk',
                                'tipe_field' => 'text',
                                'placeholder' => 'Masukkan NIP atau NUPTK',
                                'bantuan' => 'Isi salah satu nomor identitas kepegawaian yang tersedia.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Pangkat / Golongan',
                                'deskripsi' => 'Tuliskan pangkat atau golongan terakhir, jika ada.',
                                'nama_field' => 'pangkat_golongan',
                                'tipe_field' => 'text',
                                'placeholder' => 'Contoh: Pembina Utama Muda / IV-c',
                                'bantuan' => 'Isi sesuai data kepegawaian terbaru.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => false],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 3,
                                'is_required' => false,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Jabatan',
                                'deskripsi' => 'Pilih jabatan utama Anda saat ini.',
                                'nama_field' => 'jabatan',
                                'tipe_field' => 'select',
                                'placeholder' => 'Pilih jabatan',
                                'bantuan' => 'Pilih jabatan yang paling sesuai.',
                                'opsi_field' => [
                                    'Guru',
                                    'Kepala Sekolah',
                                    'Pengawas Sekolah',
                                    'GTK Lainnya',
                                ],
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 4,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Satuan Pendidikan',
                                'deskripsi' => 'Tuliskan nama satuan pendidikan tempat bertugas.',
                                'nama_field' => 'satuan_pendidikan',
                                'tipe_field' => 'text',
                                'placeholder' => 'Contoh: SMP Negeri 1 Jambi',
                                'bantuan' => 'Isi nama sekolah atau instansi saat ini.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 5,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Kabupaten/Kota',
                                'deskripsi' => 'Tuliskan kabupaten atau kota lokasi satuan pendidikan.',
                                'nama_field' => 'kabupaten_kota',
                                'tipe_field' => 'text',
                                'placeholder' => 'Contoh: Kota Jambi',
                                'bantuan' => 'Gunakan nama wilayah secara lengkap.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 6,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Lama Mengajar (Tahun)',
                                'deskripsi' => 'Masukkan total pengalaman mengajar dalam tahun.',
                                'nama_field' => 'lama_mengajar',
                                'tipe_field' => 'number',
                                'placeholder' => '0',
                                'bantuan' => 'Isi jumlah tahun pengalaman mengajar.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true, 'min' => 0],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 7,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],

                    [
                        'judul_form' => 'Riwayat Pendidikan Formal',
                        'kode_form' => 'FORM-PENDIDIKAN',
                        'deskripsi' => 'Riwayat pendidikan formal, sertifikasi, dan kualifikasi akademik responden.',
                        'urutan' => 2,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Riwayat Pendidikan Formal',
                                'deskripsi' => 'Masukkan data pendidikan S1, sertifikasi, S2, S3, atau pendidikan relevan lainnya.',
                                'nama_field' => 'riwayat_pendidikan_formal',
                                'tipe_field' => 'repeater',
                                'placeholder' => null,
                                'bantuan' => 'Tambahkan satu baris untuk setiap jenjang pendidikan atau sertifikasi.',
                                'opsi_field' => [
                                    'min_rows' => 1,
                                    'max_rows' => 10,
                                    'columns' => [
                                        [
                                            'label' => 'Jenjang',
                                            'nama_field' => 'jenjang',
                                            'tipe_field' => 'select',
                                            'opsi_field' => ['S1', 'Sertifikasi', 'S2', 'S3'],
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Gelar',
                                            'nama_field' => 'gelar',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Contoh: S.Pd., M.Pd.',
                                            'is_required' => false,
                                        ],
                                        [
                                            'label' => 'Program Studi',
                                            'nama_field' => 'program_studi',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Contoh: Pendidikan Bahasa Indonesia',
                                            'is_required' => false,
                                        ],
                                        [
                                            'label' => 'Lembaga',
                                            'nama_field' => 'lembaga',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Nama perguruan tinggi/lembaga',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Tahun Perolehan',
                                            'nama_field' => 'tahun_perolehan',
                                            'tipe_field' => 'number',
                                            'placeholder' => '2020',
                                            'is_required' => false,
                                        ],
                                    ],
                                ],
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],

                    [
                        'judul_form' => 'Pengalaman Pelatihan',
                        'kode_form' => 'FORM-PELATIHAN',
                        'deskripsi' => 'Pengalaman pelatihan yang relevan dengan profesi dalam lima tahun terakhir.',
                        'urutan' => 3,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Pengalaman Pelatihan Relevan',
                                'deskripsi' => 'Cantumkan pelatihan, workshop, bimtek, seminar, atau program pengembangan profesi dalam lima tahun terakhir.',
                                'nama_field' => 'pengalaman_pelatihan',
                                'tipe_field' => 'repeater',
                                'placeholder' => null,
                                'bantuan' => 'Tambahkan satu baris untuk setiap pelatihan.',
                                'opsi_field' => [
                                    'min_rows' => 0,
                                    'max_rows' => 20,
                                    'columns' => [
                                        [
                                            'label' => 'Nama Pelatihan',
                                            'nama_field' => 'nama_pelatihan',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Nama pelatihan',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Penyelenggara',
                                            'nama_field' => 'penyelenggara',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Nama penyelenggara',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Tahun',
                                            'nama_field' => 'tahun',
                                            'tipe_field' => 'number',
                                            'placeholder' => '2025',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Durasi (JP)',
                                            'nama_field' => 'durasi_jp',
                                            'tipe_field' => 'number',
                                            'placeholder' => '32',
                                            'is_required' => false,
                                        ],
                                        [
                                            'label' => 'Dampak terhadap Sekolah',
                                            'nama_field' => 'dampak_sekolah',
                                            'tipe_field' => 'textarea',
                                            'placeholder' => 'Jelaskan dampak pelatihan terhadap sekolah',
                                            'is_required' => false,
                                        ],
                                    ],
                                ],
                                'nilai_default' => null,
                                'validasi' => ['required' => false],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => false,
                                'is_active' => true,
                            ],
                        ],
                    ],

                    [
                        'judul_form' => 'Pengalaman Mengajar',
                        'kode_form' => 'FORM-PENGALAMAN-MENGAJAR',
                        'deskripsi' => 'Riwayat pengalaman mengajar responden pada satuan pendidikan atau lembaga terkait.',
                        'urutan' => 4,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Riwayat Pengalaman Mengajar',
                                'deskripsi' => 'Masukkan pengalaman mengajar yang pernah atau sedang dijalankan.',
                                'nama_field' => 'pengalaman_mengajar',
                                'tipe_field' => 'repeater',
                                'placeholder' => null,
                                'bantuan' => 'Tambahkan satu baris untuk setiap pengalaman mengajar.',
                                'opsi_field' => [
                                    'min_rows' => 1,
                                    'max_rows' => 20,
                                    'columns' => [
                                        [
                                            'label' => 'Pengalaman',
                                            'nama_field' => 'pengalaman',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Contoh: Guru Mata Pelajaran IPA',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Lembaga',
                                            'nama_field' => 'lembaga',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Nama sekolah/lembaga',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Tahun',
                                            'nama_field' => 'tahun',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Contoh: 2019–2024',
                                            'is_required' => true,
                                        ],
                                    ],
                                ],
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],

                    [
                        'judul_form' => 'Prestasi dan Penghargaan',
                        'kode_form' => 'FORM-PRESTASI',
                        'deskripsi' => 'Prestasi atau penghargaan profesional yang pernah diperoleh responden.',
                        'urutan' => 5,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Prestasi / Penghargaan',
                                'deskripsi' => 'Cantumkan prestasi atau penghargaan yang relevan dengan profesi.',
                                'nama_field' => 'prestasi_penghargaan',
                                'tipe_field' => 'repeater',
                                'placeholder' => null,
                                'bantuan' => 'Tambahkan satu baris untuk setiap prestasi atau penghargaan.',
                                'opsi_field' => [
                                    'min_rows' => 0,
                                    'max_rows' => 20,
                                    'columns' => [
                                        [
                                            'label' => 'Nama Prestasi / Penghargaan',
                                            'nama_field' => 'nama_prestasi',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Nama prestasi atau penghargaan',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Deskripsi Singkat',
                                            'nama_field' => 'deskripsi_singkat',
                                            'tipe_field' => 'textarea',
                                            'placeholder' => 'Jelaskan prestasi secara singkat',
                                            'is_required' => false,
                                        ],
                                        [
                                            'label' => 'Dampak terhadap Sekolah',
                                            'nama_field' => 'dampak_sekolah',
                                            'tipe_field' => 'textarea',
                                            'placeholder' => 'Jelaskan dampaknya',
                                            'is_required' => false,
                                        ],
                                    ],
                                ],
                                'nilai_default' => null,
                                'validasi' => ['required' => false],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => false,
                                'is_active' => true,
                            ],
                        ],
                    ],

                    [
                        'judul_form' => 'Karya, Inovasi, dan Best Practice',
                        'kode_form' => 'FORM-KARYA-INOVASI',
                        'deskripsi' => 'Dokumentasi karya, inovasi, atau praktik baik yang dihasilkan responden.',
                        'urutan' => 6,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Karya / Inovasi / Best Practice',
                                'deskripsi' => 'Masukkan karya, inovasi, atau best practice yang pernah diterapkan.',
                                'nama_field' => 'karya_inovasi_best_practice',
                                'tipe_field' => 'repeater',
                                'placeholder' => null,
                                'bantuan' => 'Tambahkan satu baris untuk setiap karya atau inovasi.',
                                'opsi_field' => [
                                    'min_rows' => 0,
                                    'max_rows' => 20,
                                    'columns' => [
                                        [
                                            'label' => 'Judul Karya',
                                            'nama_field' => 'judul_karya',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Masukkan judul karya atau inovasi',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Deskripsi Singkat',
                                            'nama_field' => 'deskripsi_singkat',
                                            'tipe_field' => 'textarea',
                                            'placeholder' => 'Jelaskan karya atau inovasi',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Dampak terhadap Sekolah',
                                            'nama_field' => 'dampak_sekolah',
                                            'tipe_field' => 'textarea',
                                            'placeholder' => 'Jelaskan dampak terhadap sekolah',
                                            'is_required' => false,
                                        ],
                                    ],
                                ],
                                'nilai_default' => null,
                                'validasi' => ['required' => false],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => false,
                                'is_active' => true,
                            ],
                        ],
                    ],

                    [
                        'judul_form' => 'Refleksi Diri',
                        'kode_form' => 'FORM-REFLEKSI-DIRI',
                        'deskripsi' => 'Refleksi responden mengenai kekuatan dan area pengembangan sebagai guru.',
                        'urutan' => 7,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Kekuatan dan Area Pengembangan Diri',
                                'deskripsi' => 'Tuliskan kekuatan utama serta area pengembangan Anda sebagai guru.',
                                'nama_field' => 'refleksi_diri',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan kekuatan, tantangan, dan kebutuhan pengembangan diri Anda.',
                                'bantuan' => 'Jelaskan secara reflektif berdasarkan pengalaman mengajar dan kebutuhan pengembangan profesional.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true, 'min_length' => 100],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
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
