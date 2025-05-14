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
            /* Sesuaikan ukuran gambar */
            height: auto;
        }

        .kop-surat .kop-text {
            flex-grow: 1;
            /* Biarkan teks tumbuh dan menempati ruang */
            padding: 0 10px;
            /* Beri sedikit jarak antara teks dan gambar */
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
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="kop-surat" style="position: relative;">
        <img style="position: absolute; left: 0; width: 110px" src="{{ asset('img_template/iconbbgp.png') }}"
            alt="Logo Kiri">
        <div class="kop-text">
            {{-- <h3>DAFTAR HADIR PESERTA</h3> --}}
            {{-- <h4>DAFTAR HADIR PESERSTA</h4> --}}
            <?php
            setlocale(LC_TIME, 'id_ID.UTF-8');
            
            $tgl_kegiatan = strftime('%d %B', strtotime($kegiatan->tgl_kegiatan));
            $tgl_selesai = strftime('%d %B %Y', strtotime($kegiatan->tgl_selesai));
            ?>
            <div style="margin: 0 0 0 120px; width:500px">
                <h4>{{ strtoupper($kegiatan->nama_kegiatan) }}  <br> {{ strtoupper($kegiatan->tempat_kegiatan) }} <br> {{ strtoupper($tgl_kegiatan) }} -
                    {{ strtoupper($tgl_selesai) }} </h4>

            </div>
        </div>
        <img style="position: absolute; top: -25; right: 0; width: 120px" src="{{ asset('img_template/absenPeserta.png') }}"
            alt="Logo Kanan">
    </div>
    <table style="margin-top: 50px">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Instansi</th>
                <th> Kabupaten / Kota</th>
                <th>TTD</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $key => $peserta)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $peserta->nama }}</td>
                    <td>Kantor BBGTK SulSel</td>
                    <td>Kota Makassar</td>
                    <td style="height: 25px; width:100px;"></td>
                </tr>
                {{-- @if (($key + 1) % 25 == 0) --}}
                @endforeach
        </tbody>
    </table>
    {{-- <div class="page-break"></div> --}}
    {{-- <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Instansi</th>
                <th>Asal Kabupaten</th>
                <th>TTD</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table> --}}
    {{-- @endif --}}
    {{-- @endforeach --}}

    {{-- footer --}}
    <footer style="text-align: right; margin-right: 150px">
        <p>Panitia, </p>
    </footer>
</body>

</html>
