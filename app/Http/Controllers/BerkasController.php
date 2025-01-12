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

            $r['nama_berkas'] = !$r->hasFile('nama_berkas') ? $r->nama_link : $r['nama_berkas'];
            dd($r->all());

            $validator = Validator::make($r->all(), [
                'nama_berkas' => 'required|mimes:pdf|max:10024',
                'nama_kegiatan' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($r->hasFile('nama_berkas')) {
                $foto = $r->file('nama_berkas');
                $ext = $foto->getClientOriginalExtension();
                // $r['pas_foto'] = $request->file('pas_foto');

                $fileName = date('Y-m-d_H-i-s') . "." . $ext;
                $destinationPath = '/home/simbbgps/public_html/upload/berkas';

                $foto->move($destinationPath, $fileName);

                $berkas = new Berkas();
                $berkas->nama_berkas = $fileName;
            } else {
                $r['nama_berkas'] = $r->nama_link;
            }

            $berkas->nama_kegiatan = $r->nama_kegiatan;
            $berkas->metode_upload = $r->metode_upload;
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
        return response()->json([
            'data' => $data
        ]);
        // return view('pages.admin.berkas.index', ['menu' => 'berkas'])->with('datas', json_encode($data));
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
    public function update(Request $r)
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

            $data = Berkas::find($r->formId);
            if (!$r->hasFile('nama_berkas')) {
                $data->nama_berkas = $r->nama_berkas_old;
                $data->save();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cancel uploaded file'
                ]);
            }

            $foto = $r->file('nama_berkas');
            $ext = $foto->getClientOriginalExtension();
            $fileName = date('Y-m-d_H-i-s') . "." . $ext;
            $destinationPath = '/home/simbbgps/public_html/upload/berkas';

            $foto->move($destinationPath, $fileName);

            $data->update([
                'nama_berkas' => $fileName,
                'nik' => session('no_ktp'),
            ]);

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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $data = Berkas::find($id);
        $data->delete();
        return response()->json($data);
    }
}
