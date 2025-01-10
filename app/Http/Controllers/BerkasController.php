<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Berkas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class BerkasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Berkas::orderByDesc('id')->get();
        return view('pages.admin.berkas.index', ['menu' => 'berkas', 'datas' => $data]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $r)
    {
        try {
            $validator = Validator::make($r->all(), [
                'nama_berkas' => 'mimes:pdf|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $foto = $r->file('nama_berkas');
            $ext = $foto->getClientOriginalExtension();
            // $r['pas_foto'] = $request->file('pas_foto');

            $fileName = date('Y-m-d_H-i-s') . "." . $ext;
            $destinationPath = '/home/simbbgps/public_html/upload/berkas';

            $foto->move($destinationPath, $fileName);

            $berkas = new Berkas();
            $berkas->nama_berkas = $fileName;
            $berkas->nik = session('no_ktp');
            $berkas->save();

            return response()->json([
                'status' => 'success',
                'message' => 'File uploaded successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload file'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = Berkas::find($id);

        return view('pages.admin.berkas.edit', ['menu' => 'berkas', 'datas' => $data]);
    }

    // public function verifikasi(string $id)
    // {

    //     $data = Berkas::find($id);
    //     $getData = Berkas::find($id);
    //     $data->is_verif = 'sudah';
    //     $data->save();
    //     return response()->json([
    //         'status' => $data,
    //         'data' => $getData,
    //     ]);
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //
        // $cek_username = Berkas::where('username', $request->username)->where('role', $request->role)->first();
        // if($cek_username == null) {

        $r = $request->all();
        $data = Berkas::find($r['id']);
        $dataUser = User::find($r['id']);
        // dump($r);
        $r['password'] = bcrypt($r['password']);

        $data->update($r);
        $dataUser->update($r);
        // dump($dataUser);
        // dd($data);
        return redirect()->route('berkas.index')->with('message', 'update');
        // }
        // else {
        //     return redirect()->route('berkas.index')->with('message', 'username sudah ada');
        // }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $data = Berkas::find($id);
        $data->delete();
        return response()->json($data);
    }
}
