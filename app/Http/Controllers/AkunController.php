<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;

class AkunController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $data = Admin::orderByDesc('id')->get();
        $data = Admin::select('id', 'name', 'username', 'role')
            ->orderByDesc('id')
            ->get();


        return view('pages.admin.akun.index', [
            'menu' => 'akun',
            'datas' => $data
        ]);
    }

    public function getAkunData(Request $request)
    {
        $query = Admin::select('id', 'name', 'username', 'role');

        // DataTables server-side processing
        if ($request->has('draw')) {
            $start = $request->get('start', 0);
            $length = $request->get('length', 10);
            $search = $request->get('search')['value'] ?? '';

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                });
            }

            $total = Admin::count();
            $filtered = $query->count();
            $data = $query->orderByDesc('id')
                ->skip($start)
                ->take($length)
                ->get();

            return response()->json([
                'draw' => intval($request->get('draw')),
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ]);
        }

        // Simple AJAX request
        return response()->json($query->orderByDesc('id')->get());
    }




    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $r)
    {

        $cek_username = Admin::where('username', $r->username)->where('role', $r->role)->first();
        if ($cek_username == null) {
            // dd($r);
            $r = $r->all();
            $r['password'] = bcrypt($r['password']);
            Admin::create($r);
            User::create($r);

            return redirect()->route('akun.index')->with('message', 'store');
        } else {
            return redirect()->route('akun.index')->with('message', 'username sudah ada');
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
        $data = Admin::find($id);

        return view('pages.admin.akun.edit', ['menu' => 'akun', 'datas' => $data]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //
        // $cek_username = Admin::where('username', $request->username)->where('role', $request->role)->first();
        // if($cek_username == null) {

        $r = $request->all();
        $data = Admin::find($r['id']);
        $dataUser = User::find($r['id']);
        // dump($r);
        $r['password'] = bcrypt($r['password']);

        $data->update($r);
        $dataUser->update($r);
        // dump($dataUser);
        // dd($data);
        return redirect()->route('akun.index')->with('message', 'update');
        // }
        // else {
        //     return redirect()->route('akun.index')->with('message', 'username sudah ada');
        // }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $data = Admin::find($id);
        $dataUser = User::find($id);
        $dataUser->delete();
        $data->delete();
        return response()->json($data);
    }



    public function regis(Request $r)
    {
        // $r = $request->all();
        // dd($r);
        $reg = [];
        $role = strtolower($r->role);
        $user = strtolower(str_replace(' ', '', $r->username));
        // dd($role);
        $reg['name'] = $r->name;
        $reg['username'] = $user;
        $reg['no_ktp'] = (string) $r->no_ktp;
        $reg['role'] = $role;
        $reg['password'] = bcrypt($r['password']);
        Admin::create($reg);
        User::create($reg);

        return response()->json([
            'status' => true,
            'data' => $reg
        ]);
        // return redirect()->route('akun.index')->with('message', 'store');
    }
}
