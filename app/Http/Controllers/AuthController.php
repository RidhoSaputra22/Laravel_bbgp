<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function login()
    {
        return view('pages.auth.login', ['menu' => 'login']);
    }

    public function login_admin()
    {
        return view('pages.auth.login_admin', ['menu' => 'login']);
    }

    public function login_action(Request $request)
    {
        if ($request->role == null && $request->nik == 'admin') {
            $request->role = 'admin';
        }

        if ($request->role == null) {
            return redirect()->back()->with('message', 'gagal login');
        }

        $user = Admin::where('no_ktp', $request->nik)->where('role', $request->role)->first();
        $user1 = User::where('no_ktp', $request->nik)->where('role', $request->role)->first();
        // dd($request->all());

        $cek = Auth::attempt(['no_ktp' => $request->nik, 'password' => $request->password, 'role' => $request->role]);
        if ($cek) {
            $guru = Guru::where('no_ktp', $user->no_ktp)->first();

            Session::put('user_id', $user->id);
            Session::put('guru_id', $guru->id);
            Session::put('name', $user->name);
            Session::put('nip', $user->nip);
            Session::put('no_ktp', $user->no_ktp);
            Session::put('nik', $user->nik);
            Session::put('role', $user->role);
            Session::put('cek', true);

            if ($user->role == 'pegawai') {
                return redirect()->route('pegawai.show', $user->no_ktp)->with('message', 'sukses login');
            }

            if ($user->role == 'tenaga pendidik' || $user->role == 'tenaga kependidikan' || $user->role == 'stakeholder') {
                return redirect()->route('guru.show', $user->no_ktp)->with('message', 'sukses login');
            }

            return redirect()->route('dashboard')->with('message', 'sukses login');
        } else {
            return redirect()->back()->with('message', 'gagal login');
        }
    }

    public function login_action_admin(Request $request)
    {
        $request['role'] = 'superadmin';

        if ($request->role == null) {
            return redirect()->back()->with('message', 'gagal login');
        }

        $user = Admin::where('username', $request->username)->where('role', $request->role)->first();
        $user1 = User::where('username', $request->username)->where('role', $request->role)->first();


        $cek = Auth::attempt(['username' => $request->username, 'password' => $request->password, 'role' => $request->role]);
        if ($cek) {
            Session::put('user_id', $user->id);
            Session::put('name', $user->name);
            Session::put('nip', $user->nip);
            Session::put('no_ktp', $user->no_ktp);
            Session::put('username', $user->username);
            Session::put('role', $user->role);
            Session::put('cek', true);

            if ($user->role == 'pegawai') {
                return redirect()->route('pegawai.show', $user->no_ktp)->with('message', 'sukses login');
            }

            if ($user->role == 'tenaga pendidik' || $user->role == 'tenaga kependidikan' || $user->role == 'stakeholder') {
                return redirect()->route('guru.show', $user->no_ktp)->with('message', 'sukses login');
            }

            return redirect()->route('dashboard')->with('message', 'sukses login');
        } else {
            return redirect()->back()->with('message', 'gagal login');
        }
    }
}
