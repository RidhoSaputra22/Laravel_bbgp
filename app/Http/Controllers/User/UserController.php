<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Agenda;
use App\Models\Artikel;
use App\Models\Berita;
use App\Models\Guru;
use App\Models\Internal;
use App\Models\Jabatan;
use App\Models\JabatanKependidikan;
use App\Models\JabatanPendidik;
use App\Models\JabatanStakeHolder;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kegiatan;
use App\Models\Kepegawaian;
use App\Models\Pegawai;
use App\Models\Pendamping;
use App\Models\Pendidikan;
use App\Models\PesertaKegiatan;
use App\Models\SatuanPendidikan;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $datas = array(
            'berita' => Berita::orderByDesc('id')->skip(0)->take(10)->get(),
            'agenda' => Agenda::orderByDesc('id')->skip(0)->take(10)->get(),
            'artikel' => Artikel::orderByDesc('id')->skip(0)->take(10)->get(),
        );

        // dd($datas);
        // return view('pages.user.index', ['menu' => 'profil']);
        return view('pages.landing.index', ['menu' => 'profil'], compact('datas'));
    }

    public function kontak()
    {
        return view('pages.landing.kontak', ['menu' => 'kontak']);
        // return view('pages.user.kontak', ['menu' => 'kontak']);
    }


    public function detail($jenis, $id)
    {
        // dd($jenis);
        if ($jenis == 'berita') {
            $data = Berita::find($id);
            $latest_post = Berita::orderByDesc('id')->skip(0)->take(5)->get();
        } else if ($jenis == 'artikel') {
            $data = Artikel::find($id);
            $latest_post = Artikel::orderByDesc('id')->skip(0)->take(5)->get();
        } else if ($jenis == 'agenda') {
            $data = Agenda::find($id);
            $latest_post = Agenda::orderByDesc('id')->skip(0)->take(5)->get();
            return view('pages.landing.detail-agenda', [
                'menu' => 'detail post',
                'data' => $data,
                'jenis' => $jenis,
                'latest_post' => $latest_post
            ]);
        }

        return view('pages.landing.detail-post', [
            'menu' => 'detail post',
            'data' => $data,
            'jenis' => $jenis,
            'latest_post' => $latest_post
        ]);
        // return view('pages.user.kontak', ['menu' => 'kontak']);
    }


    public function guru()
    {
        $datas = Guru::where('is_verif', 'sudah')->orderBy('id', 'DESC')->get();
        // $sekolahs = [];
        // Sekolah::select('npsn_sekolah', 'nama_sekolah', 'kecamatan', 'kabupaten')
        // ->chunk(500, function ($sekolahChunk) use (&$sekolahs) {
        //     foreach ($sekolahChunk as $sekolah) {
        //         $sekolahs[] = $sekolah;
        //     }
        // });
        // $datas = array(
        //     's_kepegawaian' => Kepegawaian::get(),
        //     's_kependidikan' => SatuanPendidikan::get(),
        //     's_gelar' => Pendidikan::get(),
        //     's_jabatan' => Jabatan::get(),
        //     's_kabupaten' => Kabupaten::get(),
        //     's_kecamatan' => Kecamatan::get(),
        //     's_sekolah' => Sekolah::get(),

        //     // 's_sekolah' => $sekolahs,
        //     // 's_jabPendidik' => JabatanPendidik::get(),
        //     // 's_jabKependidikan' => JabatanKependidikan::get(),
        //     // 's_jabStakeholder' => JabatanStakeHolder::get(),
        //     // 's_jabKategori' => ['GP (Guru Penggerak)', 'NoN GP (Guru Penggerak)'],
        //     // 's_jabTugas' => ['GP (Guru Penggerak)', 'PP (Pengajar Praktik)', 'Fasil (Fasilitator)', 'Instruktur'],

        // );

        $status = array(
            's_jabPendidik' => JabatanPendidik::get(),
            's_jabKependidikan' => JabatanKependidikan::get(),
            's_jabStakeholder' => JabatanStakeHolder::get(),
            's_jabKategori' => ['GP (Guru Penggerak)', 'NoN GP (Guru Penggerak)'],
            's_jabKategoriPengawas' => ['Sertifikat GP (Guru Penggerak)', 'Diklat Cawas', 'Lainnya'],
            's_jabKategoriKepsek' => ['Sertifikat GP (Guru Penggerak)', 'Diklat Cakep', 'Lainnya'],
            's_jabTugas' => ['GP (Guru Penggerak)', 'PP (Pengajar Praktik)', 'Fasil (Fasilitator)', 'Instruktur'],
        );
        return view('pages.landing.eksternal.index', ['menu' => 'data', 'datas' => $datas, 'status' => $status]);
        // return view('pages.user.guru', ['menu' => 'guru', 'datas' => $datas, 'status' => $status]);
    }

    public function pegawai()
    {
        // $data = Pegawai::where('is_verif', 'sudah')->orderBy('id', 'DESC')->get();
        $kota = Kabupaten::get();
        // $data = Internal::get();
        $data = array(

            'dataPenugasanPegawai' => Internal::where('jenis', 'Penugasan Pegawai')->get(),
            'dataPenugasanPpnpn' => Internal::where('jenis', 'Penugasan PPNPN')->get(),
        );
        $dataPendamping = Pendamping::get();
        // $merge = $data->merge($dataPendamping);
        return view('pages.landing.internal.index', ['menu' => 'data', 'datas' => $data, 'dataPendamping' => $dataPendamping]);
        // return view('pages.user.pegawai', ['menu' => 'pegawai', 'datas' => $data, 'dataPendamping' => $dataPendamping]);
    }
    public function form_pegawai()
    {
        $data = Pegawai::get();
        return view('pages.landing.eksternal.form', ['menu' => 'data']);
        // return view('pages.user.formPegawai', ['menu' => 'pegawai']);
    }
    public function daftar_pegawai(Request $request)
    {
        $r = $request->all();
        // $foto = $request->file('pas_foto');
        // $ext = $foto->getClientOriginalExtension();
        // // $r['pas_foto'] = $request->file('pas_foto');

        // $nameFoto = date('Y-m-d_H-i-s_') . $r['no_ktp'] . "." . $ext;
        // $destinationPath = public_path('upload/pegawai');

        // $foto->move($destinationPath, $nameFoto);

        // $fileUrl = asset('upload/pegawai/' . $nameFoto);

        // $r['pas_foto'] = $nameFoto;
        // dd($r);
        $r['pas_foto'] = '';
        $r['status'] = 'Belum Kawin';
        $r['alamat_satuan'] = '';
        $r['eksternal_jabatan'] = $r['jenisJabatan'];
        $r['jenis_jabatan'] = $r['jabJenis'];
        $r['kategori_jabatan'] = $r['jabKategori'];
        $r['tugas_jabatan'] = $r['jabTugas'];
        $r['is_verif'] = 'belum';

        Pegawai::create($r);

        return redirect()->route('user.pegawai')->with('message', 'user daftar');
    }
    public function form_guru($jenis)
    {

        $sekolahs = [];
        Sekolah::select('npsn_sekolah', 'nama_sekolah', 'kecamatan', 'kabupaten')
            ->chunk(500, function ($sekolahChunk) use (&$sekolahs) {
                foreach ($sekolahChunk as $sekolah) {
                    $sekolahs[] = $sekolah;
                }
            });

        $datas = array(
            's_kepegawaian' => Kepegawaian::get(),
            's_kependidikan' => SatuanPendidikan::get(),
            's_gelar' => Pendidikan::get(),
            's_jabatan' => Jabatan::get(),
            's_kabupaten' => Kabupaten::get(),
            's_kecamatan' => Kecamatan::get(),
            // 's_sekolah' => Sekolah::get(),

            's_sekolah' => $sekolahs,
            's_jabPendidik' => JabatanPendidik::get(),
            's_jabKependidikan' => JabatanKependidikan::get(),
            's_jabStakeholder' => JabatanStakeHolder::get(),
            's_jabKategori' => ['GP (Guru Penggerak)', 'NoN GP (Guru Penggerak)'],
            's_jabKategoriPengawas' => ['Sertifikat GP (Guru Penggerak)', 'Diklat Cawas', 'Lainnya'],
            's_jabKategoriKepsek' => ['Sertifikat GP (Guru Penggerak)', 'Diklat Cakep', 'Lainnya'],
            's_jabTugas' => ['GP (Guru Penggerak)', 'PP (Pengajar Praktik)', 'Fasil (Fasilitator)', 'Instruktur'],

        );
        $data = Guru::get();
        return view('pages.landing.eksternal.form', ['menu' => 'guru', 'status' => $datas, 'jenis' => $jenis]);
        // return view('pages.user.formGuru', ['menu' => 'guru', 'status' => $datas, 'jenis' => $jenis]);
    }
    public function daftar_guru(Request $request)
    {
        $r = $request->all();
        // $foto = $request->file('pas_foto');
        // $ext = $foto->getClientOriginalExtension();
        // // $r['pas_foto'] = $request->file('pas_foto');

        // $nameFoto = date('Y-m-d_H-i-s_') . $r['no_ktp'] . "." . $ext;
        // $destinationPath = public_path('upload/guru');

        // $foto->move($destinationPath, $nameFoto);

        // $fileUrl = asset('upload/guru/' . $nameFoto);

        // $r['pas_foto'] = $nameFoto;
        $getNik = Guru::where('no_ktp', $r['no_ktp'])->first();
        // dd($getNik);
        if ($getNik == null) {
            $r['jabatan'] = '';
            $r['pas_foto'] = '';
            $r['status'] = 'Belum Kawin';
            $r['alamat_satuan'] = '';
            $r['eksternal_jabatan'] = $r['jenisJabatan'] ?? '';

            if ($r['jabJenis'] == 'Lainnya' && $r['jabLainnya'] != null) {
                $r['jabJenis'] = $r['jabLainnya'];
                $r['jenis_jabatan'] = $r['jabJenis'];
            } else {
                $r['jenis_jabatan'] = $r['jabJenis'];
            }

            if ($r['kabupaten'] == 'Tidak ada' && $r['diluarKab'] != null) {
                $r['kabupaten'] = $r['diluarKab'];
            }

            $r['kategori_jabatan'] = $r['jabKategori'] ?? '';
            $r['tugas_jabatan'] = $r['jabTugas'] ?? '';
            $r['latar_jabatan'] = $r['jabLatar'] ?? '';
            $r['is_verif'] = 'sudah';

            $role = strtolower($r['jenisJabatan']);

            $user = strtolower(str_replace(' ', '', $r['nama_lengkap']));
            // dd($role);
            $reg['name'] = $r['nama_lengkap'];
            $reg['username'] = $user;
            $reg['no_ktp'] = (string) $r['no_ktp'];
            $reg['role'] = $role;
            $reg['password'] = bcrypt('12345');

            // dump($r);
            // dd($reg);



            User::create($reg);
            Admin::create($reg);
            Guru::create($r);

            // akun login



            return redirect()->route('user.guru')->with('message', 'user daftar');
        } else {
            return redirect()->route('user.guru')->with('message', 'nik daftar');
        }
    }

    public function getPenugasanDetail(Request $request)
    {
        $pesertaId = $request->input('id');
        $peserta = Internal::find($pesertaId);

        return response()->json($peserta);
    }

    public function getPenugasanAll()
    {
        $data = array(

            'dataPenugasanPegawai' => Internal::where('jenis', 'Penugasan Pegawai')->get(),
            'dataPenugasanPpnpn' => Internal::where('jenis', 'Penugasan PPNPN')->get(),
        );

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function getPenugasanDetailLoka(Request $request)
    {
        $pesertaId = $request->input('id');
        $peserta = Pendamping::find($pesertaId);

        return response()->json($peserta);
    }

    public function getPenugasanDetailEksternal(Request $request)
    {
        $pesertaId = $request->input('id');
        $peserta = Guru::find($pesertaId);

        return response()->json([
            'data' => $peserta,
            'sekolah' => $peserta->sekolah
        ]);
    }

    public function statistik()
    {
        // Data untuk Statistik Eksternal
        $datas = array(
            'GP' => Guru::where('kategori_jabatan', 'GP (Guru Penggerak)')->count(),
            'nonGP' => Guru::where('kategori_jabatan', 'NoN GP (Guru Penggerak)')->count(),
        );

        // Ambil daftar kegiatan untuk filter
        $activities = Kegiatan::all();

        return view('pages.landing.statistik.index', [
            'menu' => 'statistik',
            'datas' => $datas,
            'activities' => $activities,
        ]);
    }

    // API endpoint untuk mendapatkan statistik kegiatan berdasarkan bulan
    public function getMonthStatistics($month)
    {
        $jumlah_kegiatan = Kegiatan::whereMonth('tgl_kegiatan', $month)->count();
        // \Log::info('Fetching statistics for month: ' . $month);
        return response()->json(['jumlah_kegiatan' => $jumlah_kegiatan]);
    }

    // API endpoint untuk mendapatkan daftar kegiatan berdasarkan bulan
    public function getActivitiesByMonth($month)
    {
        $activities = Kegiatan::whereMonth('tgl_kegiatan', $month)->get();

        return response()->json($activities);
    }

    // API endpoint untuk mendapatkan statistik kegiatan berdasarkan ID dan jenis partisipasi
    public function getActivityStatistics($activityId, $participantType)
    {
        $jumlah = PesertaKegiatan::where('id_kegiatan', $activityId)
            ->where('status_keikutpesertaan', $participantType)
            ->count();

        return response()->json(['jumlah' => $jumlah]);
    }

    public function analisisPelatihan()
    {
        return view('pages.landing.analisisPelatihan.index', [
            'menu' => 'analisisPelatihan',
        ]);
    }
}
