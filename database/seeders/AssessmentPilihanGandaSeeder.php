<?php

namespace Database\Seeders;

use App\Enum\LevelKompetensi;
use App\Models\Assessment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AssessmentPilihanGandaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assessments = [
            [
                'kode_assessment' => 'ASM-KOMP-GURU-003',
                'judul' => 'Tes Pilihan Ganda Kompleks Kompetensi Guru',
                'deskripsi' => 'Instrumen pemetaan kompetensi guru yang mencakup kompetensi pedagogik, kepribadian, sosial, dan profesional melalui 123 soal situasional.',
                'petunjuk' => 'Pilihlah jawaban yang paling sesuai dengan kondisi atau pemahaman Anda saat ini secara jujur. Tidak ada jawaban yang salah; setiap pilihan merepresentasikan level kompetensi dari Level 1 (Paham) hingga Level 5 (Ahli).',
                'status' => 'publish',
                'is_active' => true,
                'forms' => [

                    // Kompetensi 1: Pedagogik
                    [
                        'judul_form' => '1.1.1 Pengelolaan Perilaku Peserta Didik yang Sulit',
                        'kode_form' => 'FORM-PED-111',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'urutan' => 1,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 1. Sebagai guru, tindakan yang paling tepat untuk menangani perilaku Bima secara edukatif adalah ....',
                                'deskripsi' => 'Saat kegiatan diskusi berlangsung, Bima beberapa kali memotong pembicaraan teman-temannya dan selalu ingin mendominasi kelompok. Akibatnya, anggota kelompok lain menjadi enggan menyampaikan pendapat dan suasana diskusi kurang kondusif.',
                                'nama_field' => 'soal_001',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.1.1 — Pengelolaan Perilaku Peserta Didik yang Sulit. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali faktor yang memengaruhi perilaku Bima melalui pengamatan selama diskusi serta mengarahkan partisipasinya secara lebih proporsional. ',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan kembali aturan diskusi kepada Bima serta mengarahkan pentingnya memberi kesempatan yang setara kepada seluruh anggota kelompok. ',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Melaksanakan strategi pengelolaan diskusi yang terstruktur bersama kelompok serta mengarahkan setiap anggota berpartisipasi secara seimbang. ',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah berbagai faktor yang menyebabkan dominasi Bima dalam kelompok serta mengarahkan langkah pendampingan yang lebih sesuai. ',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan pendekatan pembinaan perilaku kolaboratif bersama peserta didik sehingga interaksi kelompok menjadi lebih positif dan berkelanjutan. ',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 2. Sebagai guru, tindakan yang paling tepat untuk membantu Rani memperbaiki perilakunya adalah ....',
                                'deskripsi' => 'Rani sering meninggalkan tempat duduknya tanpa izin ketika pembelajaran berlangsung. Meskipun telah beberapa kali diingatkan, perilaku tersebut masih sering terjadi dan mulai mengganggu konsentrasi teman-temannya.',
                                'nama_field' => 'soal_002',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.1.1 — Pengelolaan Perilaku Peserta Didik yang Sulit. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mencermati situasi yang mendorong Rani meninggalkan tempat duduknya serta mengarahkan perilaku yang lebih sesuai selama pembelajaran berlangsung. ',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan kembali harapan perilaku di kelas kepada Rani serta mengarahkan pentingnya mengikuti aturan yang telah disepakati bersama. ',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan pengelolaan perilaku yang konsisten bersama Rani serta mengarahkan keterlibatannya secara positif dalam kegiatan belajar. ',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengkaji faktor-faktor yang memengaruhi perilaku Rani bersama pihak terkait serta mengarahkan bentuk pendampingan yang lebih tepat. ',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merekonstruksi strategi pembinaan perilaku yang berkelanjutan bersama peserta didik sehingga tercipta kebiasaan belajar yang lebih positif. ',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 3. Sebagai guru, tindakan yang paling tepat untuk menangani perilaku Andi secara konstruktif adalah ....',
                                'deskripsi' => 'Selama kegiatan pembelajaran berlangsung, Andi sering mengganggu teman di sebelahnya dengan mengajak berbicara dan bercanda secara berlebihan. Meskipun suasana kelas sedang fokus pada tugas yang diberikan, Andi tetap mengulangi perilaku tersebut sehingga mengganggu konsentrasi beberapa peserta didik lainnya.',
                                'nama_field' => 'soal_003',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.1.1 — Pengelolaan Perilaku Peserta Didik yang Sulit. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati pola perilaku Andi selama pembelajaran serta mengarahkan keterlibatannya pada kegiatan belajar yang lebih positif. ',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan harapan perilaku yang sesuai kepada Andi serta mengarahkan pentingnya menjaga kenyamanan belajar bersama. ',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan strategi pengelolaan kelas yang konsisten kepada Andi serta mengarahkan partisipasinya secara lebih produktif. ',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menilai berbagai faktor yang memengaruhi perilaku Andi serta mengarahkan bentuk pendampingan yang lebih sesuai. ',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Menciptakan program pembinaan perilaku yang melibatkan berbagai pihak serta mengarahkan perubahan perilaku secara berkelanjutan. ',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
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

    private function withCompetencyLevels(array $options): array
    {
        return collect($options)
            ->values()
            ->map(function (array $option, int $index) {
                return [
                    ...$option,
                    'level_kompetensi' => LevelKompetensi::tryFromSequence($index + 1)?->value,
                ];
            })
            ->all();
    }
}
