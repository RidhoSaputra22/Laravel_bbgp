<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            margin-bottom: 20px;
        }

        .kop-surat img {
            width: 80px;
            height: auto;
        }

        .kop-surat .kop-text {
            flex-grow: 1;
            padding: 0 10px;
        }

        .kop-surat h1,
        .kop-surat h2 {
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .page-break {
            page-break-after: always;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 18px;
            text-transform: uppercase;
        }

        .biodata-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .biodata-table td {
            padding: 5px;
            vertical-align: top;
        }

        .signature {
            text-align: right;
            margin-top: 50px;
        }

        /* Style untuk halaman Pakta Integritas */
        .pakta-container {
            padding: 20px 40px;
            font-size: 12px;
            line-height: 1.8;
        }

        .pakta-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .pakta-header img {
            width: 100px;
            margin-bottom: 10px;
        }

        .pakta-title {
            font-weight: bold;
            font-size: 14px;
            margin: 10px 0;
            text-align: center;
        }

        .pakta-content {
            text-align: justify;
            margin: 20px 0;
        }

        .pakta-list {
            margin-top: -20px;
            margin-left: 20px;
        }

        .pakta-list ol {
            margin: 10px 0;
            padding-left: 20px;
        }

        .pakta-list li {
            /* margin: 10px 0; */
            text-align: justify;
        }

        .pakta-identity {
            margin: 20px 0;
        }

        .pakta-identity table {
            border: none;
        }

        .pakta-identity td {
            padding: 3px 5px;
            border: none;
        }

        .pakta-footer {
            margin-top: 40px;
            text-align: right;
            padding-right: 50px;
        }

        .materai-box {
            border: 0px solid #000;
            /* width: 100px;
            height: 100px; */
            display: inline-block;
            text-align: center;
            line-height: 50px;
            margin-right: 100px !important;
            margin: 20px 0;
        }

        /* Style untuk Surat Pernyataan Sehat */
        .surat-sehat {
            margin-top: 60px;
            padding-top: 40px;
        }
    </style>
</head>

<body>
    <!-- HALAMAN 1: BIODATA PESERTA -->
    <div class="kop-surat" style="position: relative;">
        <img style="position: absolute; left: 0; width: 110px" src="{{ asset('img_template/iconbbgp.png') }}"
            alt="Logo Kiri">
        <div class="kop-text">
            <?php
            setlocale(LC_TIME, 'id_ID.UTF-8');
            $tgl_lahir = strftime('%d %B %Y', strtotime($getById->tgl_lahir ?? date('d-m-Y')));
            ?>
            <div style="margin: 50px 0 0 100px; width:500px">
                <h2>{{ strtoupper($namaKegiatan) }}</h2>
            </div>
        </div>
    </div>

    <img style="position: absolute; top: -25; right: 0; width: 220px"
        src="{{ public_path(
            'img_template/biodata/bio-' .
                ($peserta->status_keikutpesertaan == 'peserta'
                    ? 'peserta'
                    : ($peserta->status_keikutpesertaan == 'panitia'
                        ? 'panitia'
                        : 'narasumber')) .
                '.png',
        ) }}"
        alt="Logo Kanan">

    <div style="margin-top: 20px">
        <div class="container">
            <table cellspacing="0" cellpadding="0" border="0" style="border: none !important;"
                class="biodata-table">
                <tr>
                    <td>1. Nama</td>
                    <td>: {{ $peserta->nama }}</td>
                </tr>
                <tr>
                    <td>2. N I P</td>
                    <td>: {{ $peserta->nip }}</td>
                </tr>
                <tr>
                    <td>3. Jenis Kelamin</td>
                    <td>: {{ $peserta->jkl }}</td>
                </tr>
                <tr>
                    <td>4. Tempat, Tanggal Lahir</td>
                    <td>: {{ $getById->tempat_lahir ?? 'Tidak terdata' }}, {{ $tgl_lahir }}</td>
                </tr>
                <tr>
                    <td>5. Agama</td>
                    <td>: {{ $getById->agama }}</td>
                </tr>
                <tr>
                    <td>6. Pangkat/Golongan</td>
                    <td>: {{ $peserta->golongan }}</td>
                </tr>
                <tr>
                    <td>7. Asal Kabupaten/Kota</td>
                    <td>: {{ $peserta->kabupaten }}</td>
                </tr>
                <tr>
                    <td>8. Instansi</td>
                    <td>: {{ $peserta->instansi }}</td>
                </tr>
                <tr>
                    <td>9. No. HP dan Whatsapp</td>
                    <td>: HP : {{ $peserta->no_hp }} <br> <span style="margin-left: 10px;"> WA : {{ $peserta->no_wa }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>10. Pendidikan Terakhir</td>
                    <td>: {{ $getById->pendidikan }}</td>
                </tr>
                @if ($getById->eksternal_jabatan == 'Stakeholder')
                    <tr>
                        <td>11. Jabatan</td>
                        <td>: {{ $getById->jenis_jabatan }}</td>
                    </tr>
                @endif
            </table>
            <footer>
                <div style="font-size: 16px; margin-right:20px" class="signature">
                    <p>...................., .................... {{ date('Y') }}</p>
                    <br><br><br><br>
                    <p>{{ $peserta->nama }}</p>
                    <p>NIP. {{ $peserta->nip }}</p>
                </div>
            </footer>
        </div>
    </div>

    <!-- PAGE BREAK -->
    <div class="page-break"></div>

    <!-- HALAMAN 2: PAKTA INTEGRITAS DAN SURAT PERNYATAAN SEHAT -->
    <div class="pakta-container">
        <!-- Header dengan Logo -->
        <div class="pakta-header" style="margin-top: -15px !important;">
            <img src="{{ asset('img_template/iconbbgp.png') }}" alt="Logo">
        </div>

        <!-- Judul Pakta Integritas -->
        <div class="pakta-title">
            PAKTA INTEGRITAS
        </div>
        <div class="pakta-title" style="margin-top: -10px !important;">
            {{-- {{ strtoupper($namaKegiatan) }} --}}
            <i>IN SERVICE TRAINING</i> 2, TUJUH JURUS BK HEBAT BAGI GURU WALI<br>
            BALAI BESAR GURU DAN TENAGA KEPENDIDIKAN (BBGTK)<br>
            PROVINSI SULAWESI SELATAN<br>
            TAHUN 2025
        </div>

        <!-- Identitas Pembuat Pernyataan -->
        <div class="pakta-content" style="margin-top: -10px !important;">
            <p>Saya yang bertanda tangan dibawah ini:</p>
            <div style="margin-top: -10px !important;" class="pakta-identity">
                <table>
                    <tr>
                        <td width="200">Nama</td>
                        <td>: {{ $peserta->nama }}</td>
                    </tr>
                    <tr>
                        <td>Jabatan</td>
                        <td>: {{ $getById->jenis_jabatan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Instansi/Unit Kerja</td>
                        <td>: {{ $peserta->instansi }}</td>
                    </tr>
                    <tr>
                        <td>Kabupaten/Kota</td>
                        <td>: {{ $peserta->kabupaten }}</td>
                    </tr>
                    <tr>
                        <td>Provinsi</td>
                        <td>: <strong>SULAWESI SELATAN</strong></td>
                    </tr>
                </table>
            </div>

            <p style="margin-top: -10px !important;">Dengan ini menyatakan bahwa saya:</p>

            <div class="pakta-list">
                <ol>
                    <li>Wajib menaati kewajiban dan menghindari larangan, menaati ketentuan peraturan Perundang
                        undangan, menunjukan integritas dan keteladanan sikap, perilaku, ucapan dan tindakan kepada
                        setiap orang, baik di dalam maupun di luar kedinasan, berdasarkan Peraturan Pemerintah Nomor 94
                        Tahun 2021 tentang Disiplin Pegawai Negeri Sipil.</li>

                    <li>Bersedia mengikuti secara penuh seluruh rangkaian kegiatan, sesuai jadwal yang telah ditentukan,
                        tidak meninggalkan kegiatan kecuali dengan izin tertulis dari Pimpinan/PIC kegiatan.</li>

                    <li>Akan menjaga sikap profesional, disiplin, serta berpartisipasi aktif dalam semua sesi pelatihan.
                    </li>

                    <li>Berkomitmen untuk menyelesaikan tugas-tugas, proyek, serta evaluasi yang diberikan selama
                        kegiatan berlangsung.</li>

                    <li>Tidak membawa anggota keluarga, teman, dan/ atau siapapun dan dengan alasan apapun, ke lokasi
                        kegiatan pelatihan.</li>

                    <li>Apabila melakukan pelanggaran sebagaimana yang tercantum dari angka 1 s.d 5, bersedia menerima
                        konsekuensi dan sanksi sesuai aturan/ketentuan yang berlaku.</li>
                </ol>
            </div>

            <p>Demikian Pakta Integritas ini saya buat dengan penuh kesadaran dan tanggung jawab tanpa paksaan dari
                pihak manapun.</p>
        </div>

        <!-- Footer dengan Tanda Tangan -->
        <div class="pakta-footer" style="margin-top: -50px !important;">
            <p>.............., ........................ 2025</p>
            <p>Pembuat Pernyataan,</p>
            <div class="materai-box">
                Materai Rp. 10.000
            </div>
            <div>.............................................</div>
            {{-- <p style="margin-top: 10px;">{{ $peserta->nama }}</p> --}}
        </div>

    </div>

    <!-- PAGE BREAK -->
    <div class="page-break"></div>

    <!-- HALAMAN 3: SURAT PERNYATAAN SEHAT -->
    <div class="pakta-container">
        <!-- Header dengan Logo -->
        <div class="pakta-header">
            <img src="{{ asset('img_template/iconbbgp.png') }}" alt="Logo">
        </div>

        <div class="pakta-title">
            SURAT PERNYATAAN SEHAT
        </div>

        <div class="pakta-content">
            <p>Yang bertanda tangan di bawah ini:</p>
            <div class="pakta-identity">
                <table>
                    <tr>
                        <td width="200">Nama</td>
                        <td>: {{ $peserta->nama }}</td>
                    </tr>
                    <tr>
                        <td>Tempat/Tanggal Lahir</td>
                        <td>: {{ $getById->tempat_lahir ?? 'Tidak terdata' }}, {{ $tgl_lahir }}</td>
                    </tr>
                    <tr>
                        <td>Instansi/Unit Kerja</td>
                        <td>: {{ $peserta->instansi }}</td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td>: {{ $getById->alamat ?? '-' }}</td>
                    </tr>
                </table>
            </div>

            <p>Dengan ini menyatakan bahwa saya dalam kondisi sehat untuk mengikuti <i>In Service Training</i> 2 Tujuh
                Jurus Bk Hebat Bagi Guru Wali pada waktu dan tempat yang ditetapkan.</p>

            <p>Demikian surat pernyataan sehat ini saya buat dengan sungguh-sungguh dan penuh rasa tanggung jawab.</p>
        </div>

        <!-- Footer Surat Sehat -->
        <div class="pakta-footer">
            <p>.............., ........................ 2025</p>
            <p>Pembuat pernyataan</p>
            <br><br><br>
            <p>{{ $peserta->nama }}</p>
        </div>
    </div>

</body>

</html>
