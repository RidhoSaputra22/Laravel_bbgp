<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Provinsi;
use Illuminate\Http\Request;

class SekolahController extends Controller
{
    public function index() {
        // dd(1);
        $alamat = array(
            // 'provinsi' => Provinsi::all(),
            'kabupaten' => Kabupaten::all(),
            'kecamatan' => Kecamatan::all()
        );
        return view('pages.landing.sekolah.index', [
            'menu' => 'sekolah',
            'alamat' => $alamat,
            // 'activities' => $activities,
        ]);
    }

    public function store(Request $request) {
        dd($request->all());
    }
}
