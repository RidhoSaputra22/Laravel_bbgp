<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $akun = [
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => bcrypt('admin'),
                'role' => 'admin',
            ],
            [
                'name' => 'Kepala',
                'username' => 'kepala',
                'password' => bcrypt('kepala'),
                'role' => 'kepala',
            ],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'password' => bcrypt('superadmin'),
                'role' => 'superadmin',
            ],
            [
                'name' => 'Tenaga Pendidik ',
                'username' => 'tenaga pendidik',
                'password' => bcrypt('tenaga_pendidik'),
                'role' => 'tenaga pendidik',
            ],
            [
                'name' => 'Tenaga Kependidikan ',
                'username' => 'tenaga kependidikan',
                'password' => bcrypt('tenaga_kependidikan'),
                'role' => 'tenaga kependidikan',
            ],
            [
                'name' => 'Stakeholder ',
                'username' => 'stakeholder',
                'password' => bcrypt('stakeholder'),
                'role' => 'stakeholder',
            ],
            [
                'name' => 'Pegawai Internal BBGTK',
                'username' => 'pegawai',
                'password' => bcrypt('pegawai'),
                'role' => 'pegawai',
            ],

        ];

        foreach ($akun as $key => $v) {
            Admin::create([
                'name' => $v['name'],
                'username' => $v['username'],
                'password' => $v['password'],
                'role' => $v['role'],
            ]);

            User::create([
                'name' => $v['name'],
                'username' => $v['username'],
                'password' => $v['password'],
                'role' => $v['role'],
            ]);
        }
    }
}
