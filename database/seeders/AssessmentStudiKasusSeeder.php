<?php

namespace Database\Seeders;

use App\Models\Assessment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AssessmentStudiKasusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assessments = [
            [
                'kode_assessment' => 'ASM-STUDI-KASUS-004',
                'judul' => 'Studi Kasus Pemetaan Kompetensi Guru',
                'deskripsi' => 'Assessment berbasis studi kasus untuk memetakan kompetensi pedagogik, kepribadian, sosial, dan profesional guru.',
                'petunjuk' => 'Bacalah setiap kasus dengan saksama. Jawablah seluruh pertanyaan secara analitis, sistematis, dan sesuai dengan konteks tugas seorang guru.',
                'status' => 'publish',
                'is_active' => true,
                'forms' => [
                    [
                        'judul_form' => 'Studi Kasus 1 – Kompetensi Pedagogik',
                        'kode_form' => 'FORM-SK-PEDAGOGIK',
                        'deskripsi' => "Fokus: Pembelajaran berpusat pada peserta didik.\n\nKasus:\nSeorang guru mengajar dengan metode ceramah hampir di setiap pertemuan. Siswa cenderung pasif, hanya mencatat dan mendengarkan. Hasil asesmen menunjukkan sebagian besar siswa memahami materi secara dangkal dan kesulitan menerapkan konsep dalam situasi nyata. Guru merasa metode tersebut sudah efektif karena materi dapat disampaikan dengan cepat.",
                        'urutan' => 1,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => '1. Identifikasi Permasalahan Utama',
                                'deskripsi' => 'Identifikasi masalah utama yang terjadi dalam proses pembelajaran pada kasus tersebut.',
                                'nama_field' => 'identifikasi_masalah_pedagogik',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan permasalahan utama yang Anda identifikasi.',
                                'bantuan' => 'Jelaskan masalah yang berkaitan dengan metode pembelajaran, keterlibatan peserta didik, dan hasil belajar.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '2. Analisis Penyebab Berdasarkan Prinsip Pedagogik',
                                'deskripsi' => 'Analisis faktor penyebab masalah berdasarkan prinsip pembelajaran yang berpusat pada peserta didik.',
                                'nama_field' => 'analisis_penyebab_pedagogik',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Jelaskan penyebab masalah berdasarkan prinsip pedagogik.',
                                'bantuan' => 'Uraikan keterkaitan metode ceramah, aktivitas belajar, pemahaman konsep, dan kebutuhan peserta didik.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '3. Rancang Strategi Pembelajaran Berpusat pada Peserta Didik',
                                'deskripsi' => 'Rancang strategi pembelajaran alternatif yang lebih aktif dan berpusat pada peserta didik.',
                                'nama_field' => 'strategi_pembelajaran_pedagogik',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan rancangan strategi pembelajaran yang Anda usulkan.',
                                'bantuan' => 'Sertakan metode, aktivitas peserta didik, peran guru, media atau sumber belajar, serta tahapan pelaksanaan.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '4. Jelaskan Indikator Keberhasilan Strategi',
                                'deskripsi' => 'Jelaskan indikator yang menunjukkan bahwa strategi pembelajaran yang dirancang berhasil.',
                                'nama_field' => 'indikator_keberhasilan_pedagogik',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan indikator keberhasilan strategi pembelajaran.',
                                'bantuan' => 'Pertimbangkan keterlibatan peserta didik, pemahaman konsep, penerapan konsep, serta hasil asesmen.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 4,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],

                    [
                        'judul_form' => 'Studi Kasus 2 – Kompetensi Kepribadian',
                        'kode_form' => 'FORM-SK-KEPRIBADIAN',
                        'deskripsi' => "Fokus: Integritas, emosi, dan refleksi diri.\n\nKasus:\nSeorang guru diketahui memberikan perlakuan berbeda kepada siswa tertentu karena faktor kedekatan pribadi. Selain itu, guru tersebut mudah terpancing emosi ketika siswa melakukan kesalahan kecil di kelas. Meskipun demikian, guru merasa tindakannya wajar dan belum melakukan refleksi terhadap perilakunya.",
                        'urutan' => 2,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => '1. Identifikasi Aspek Kepribadian yang Perlu Diperbaiki',
                                'deskripsi' => 'Identifikasi aspek kompetensi kepribadian guru yang perlu diperbaiki berdasarkan kasus.',
                                'nama_field' => 'identifikasi_aspek_kepribadian',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan aspek kepribadian yang perlu diperbaiki.',
                                'bantuan' => 'Pertimbangkan integritas, keadilan, pengendalian emosi, objektivitas, dan kesadaran diri.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '2. Analisis Dampak Perilaku Guru terhadap Peserta Didik',
                                'deskripsi' => 'Analisis dampak perlakuan berbeda dan pengelolaan emosi yang kurang tepat terhadap peserta didik.',
                                'nama_field' => 'analisis_dampak_perilaku_kepribadian',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Jelaskan dampak perilaku guru terhadap peserta didik.',
                                'bantuan' => 'Bahas dampak terhadap rasa aman, motivasi, kepercayaan, keadilan, serta iklim belajar di kelas.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '3. Rancang Langkah Refleksi Diri Guru',
                                'deskripsi' => 'Rancang langkah refleksi diri yang dapat dilakukan guru untuk memperbaiki perilakunya.',
                                'nama_field' => 'langkah_refleksi_diri_kepribadian',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan langkah refleksi diri yang Anda sarankan.',
                                'bantuan' => 'Sertakan langkah mengenali perilaku, menerima umpan balik, mengevaluasi dampak, dan menyusun perbaikan.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '4. Jelaskan Penerapan Perilaku Sesuai Kode Etik',
                                'deskripsi' => 'Jelaskan cara guru menunjukkan perilaku yang sesuai dengan kode etik profesi guru.',
                                'nama_field' => 'penerapan_kode_etik_kepribadian',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Jelaskan perilaku profesional yang sesuai kode etik.',
                                'bantuan' => 'Uraikan prinsip objektivitas, keadilan, penghormatan kepada peserta didik, profesionalitas, dan pengendalian emosi.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 4,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],

                    [
                        'judul_form' => 'Studi Kasus 3 – Kompetensi Sosial',
                        'kode_form' => 'FORM-SK-SOSIAL',
                        'deskripsi' => "Fokus: Kolaborasi dan keterlibatan pihak lain.\n\nKasus:\nDi sebuah sekolah, guru jarang berkolaborasi dengan rekan sejawat. Komunikasi dengan orang tua hanya dilakukan saat pembagian rapor. Selain itu, potensi lingkungan sekitar belum dimanfaatkan sebagai sumber belajar. Akibatnya, pembelajaran kurang kontekstual dan dukungan terhadap siswa menjadi terbatas.",
                        'urutan' => 3,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => '1. Identifikasi Masalah dalam Kompetensi Sosial Guru',
                                'deskripsi' => 'Identifikasi permasalahan kompetensi sosial guru dalam kasus tersebut.',
                                'nama_field' => 'identifikasi_masalah_sosial',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan masalah kompetensi sosial yang Anda identifikasi.',
                                'bantuan' => 'Fokuskan pada kolaborasi rekan sejawat, komunikasi orang tua, dan keterlibatan masyarakat.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '2. Analisis Pentingnya Kolaborasi dan Kemitraan',
                                'deskripsi' => 'Analisis pentingnya kolaborasi dengan guru, orang tua, dan masyarakat dalam mendukung pembelajaran.',
                                'nama_field' => 'analisis_kolaborasi_kemitraan',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Jelaskan pentingnya kolaborasi dan kemitraan.',
                                'bantuan' => 'Uraikan kontribusi tiap pihak terhadap pembelajaran kontekstual, dukungan belajar, dan perkembangan peserta didik.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '3. Rancang Strategi Peningkatan Kolaborasi',
                                'deskripsi' => 'Rancang strategi untuk meningkatkan kolaborasi guru dengan rekan sejawat, orang tua, dan masyarakat.',
                                'nama_field' => 'strategi_kolaborasi_sosial',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan rancangan strategi kolaborasi.',
                                'bantuan' => 'Sertakan kegiatan, bentuk komunikasi, peran para pihak, sumber daya, dan jadwal pelaksanaan.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '4. Jelaskan Dampak yang Diharapkan',
                                'deskripsi' => 'Jelaskan dampak yang diharapkan setelah strategi kolaborasi diterapkan.',
                                'nama_field' => 'dampak_strategi_sosial',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan dampak yang diharapkan.',
                                'bantuan' => 'Pertimbangkan dampak terhadap kualitas pembelajaran, dukungan peserta didik, hubungan sekolah-keluarga, dan keterlibatan masyarakat.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 4,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],

                    [
                        'judul_form' => 'Studi Kasus 4 – Kompetensi Profesional',
                        'kode_form' => 'FORM-SK-PROFESIONAL',
                        'deskripsi' => "Fokus: Penguasaan materi dan implementasi kurikulum.\n\nKasus:\nSeorang guru mengajar sesuai buku teks tanpa mengembangkan materi lebih lanjut. Pembelajaran tidak dikaitkan dengan konteks kehidupan siswa dan tidak sepenuhnya mengacu pada capaian pembelajaran dalam kurikulum. Akibatnya, siswa kurang memahami relevansi materi yang dipelajari.",
                        'urutan' => 4,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => '1. Identifikasi Kelemahan Kompetensi Profesional Guru',
                                'deskripsi' => 'Identifikasi kelemahan kompetensi profesional guru berdasarkan kasus.',
                                'nama_field' => 'identifikasi_kelemahan_profesional',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan kelemahan kompetensi profesional yang Anda identifikasi.',
                                'bantuan' => 'Perhatikan penguasaan materi, pengembangan bahan ajar, relevansi konteks, dan pemahaman kurikulum.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '2. Analisis Keterkaitan Penguasaan Materi dan Kurikulum',
                                'deskripsi' => 'Analisis hubungan antara penguasaan materi, capaian pembelajaran, dan implementasi kurikulum.',
                                'nama_field' => 'analisis_materi_kurikulum_profesional',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Jelaskan keterkaitan penguasaan materi dan kurikulum.',
                                'bantuan' => 'Uraikan pentingnya menurunkan capaian pembelajaran ke tujuan, aktivitas, asesmen, dan materi kontekstual.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '3. Rancang Strategi Peningkatan Kualitas Pembelajaran',
                                'deskripsi' => 'Rancang strategi yang dapat meningkatkan kualitas pembelajaran pada kasus tersebut.',
                                'nama_field' => 'strategi_peningkatan_pembelajaran_profesional',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan strategi peningkatan kualitas pembelajaran.',
                                'bantuan' => 'Sertakan pengembangan materi, penggunaan konteks nyata, variasi metode, sumber belajar, dan asesmen.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '4. Jelaskan Integrasi Materi, Strategi, dan Kebutuhan Peserta Didik',
                                'deskripsi' => 'Jelaskan cara guru mengintegrasikan materi ajar, strategi pembelajaran, dan kebutuhan peserta didik.',
                                'nama_field' => 'integrasi_materi_strategi_kebutuhan_siswa',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Jelaskan bentuk integrasi materi, strategi, dan kebutuhan peserta didik.',
                                'bantuan' => 'Jelaskan kesesuaian antara capaian pembelajaran, karakteristik peserta didik, konteks kehidupan nyata, aktivitas, dan asesmen.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 4,
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
