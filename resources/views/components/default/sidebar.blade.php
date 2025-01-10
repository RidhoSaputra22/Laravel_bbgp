<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="/">BBGP Sulsel</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="/">BBGP</a>
        </div>

        <ul class="sidebar-menu">

            <li class="menu-header">Dashboard</li>

            <li class="nav-item  {{ $menu == 'dashboard' ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="nav-link "><i
                        class="fas fa-fire"></i><span>Dashboard</span></a>
            </li>

            @if (session('role') == 'admin' || session('role') == 'superadmin' || session('role') == 'kepala')
                <li class="nav-item dropdown {{ $menu == 'pegawai' ? 'active' : '' }}">
                    <a href="#" class="nav-link has-dropdown"><i class="fas fa-sitemap"></i>
                        <span>Master Data</span></a>
                    <ul class="dropdown-menu">

                        <li class="{{ $menu == 'pegawai' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('pegawai.index') }}">
                                Data Pegawai BBPG
                            </a>
                        </li>

                    </ul>
                </li>

                <li class="{{ $menu == 'internal' ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('internal.index') }}">
                        <i class="fas fa-sign-out-alt"></i> <span>Data Internal</span>
                    </a>
                </li>

                <li class="{{ $menu == 'guru' ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('guru.index') }}">
                        <i class="fas fa-chalkboard-teacher"></i> <span>Data Eksternal</span>
                    </a>
                </li>


                <li class="nav-item dropdown {{ $menu == 'kegiatan' || $menu == 'peserta' ? 'active' : '' }}">
                    <a href="#" class="nav-link has-dropdown"><i class="fas fa-calendar-week"></i>
                        <span>Data Kegiatan</span></a>
                    <ul class="dropdown-menu">

                        <li class="{{ $menu == 'kegiatan' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('kegiatan.index') }}">
                                Kegiatan</span>
                            </a>
                        </li>

                        <li class="{{ $menu == 'peserta' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('peserta.index') }}">
                                Peserta Kegiatan
                            </a>
                        </li>

                    </ul>
                </li>

                <li class="nav-item dropdown {{ $menu == 'honor' ? 'active' : '' }}">
                    <a href="#" class="nav-link has-dropdown"><i class="fas fa-th"></i>
                        <span>Data Keuangan</span></a>
                    <ul class="dropdown-menu">

                        {{-- <li class="{{ $title == 'Data Honor Kegiatan' ? '' : '' }}">
                            <a class="nav-link" href="{{ route('honor.index') }}">
                                Penomoran 
                            </a>
                        </li> --}}

                        <li class="{{ $title == 'Data Honor Kegiatan' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('honor.index') }}">
                                Honor
                            </a>
                        </li>
                        <li class="{{ $title == 'Data Kuitansi' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('kuitansi.index') }}">
                                <span>Kuitansi Kegiatan</span>
                            </a>
                        </li>
                        <li class="{{ $title == 'Data Kuitansi Lokakarya' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('kuitansiLoka.index') }}">
                                <span>Kuitansi Lokakarya</span>
                            </a>
                        </li>

                    </ul>
                </li>

                {{-- <li class="{{ $menu == 'peserta' ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('kegiatan.index') }}">
                        <i class="fas fa-users"></i> <span>Data Peserta Kegiatan</span>
                    </a>
                </li> --}}



                <li class="{{ $menu == 'akun' ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('akun.index') }}">
                        <i class="fas fa-user"></i> <span>Data Akun</span>
                    </a>
                </li>
                
                <li class="menu-header">Landing Page</li>
                <li class="nav-item  {{ $menu == 'agenda' ? 'active' : '' }}">
                    <a href="{{ route('agenda.index') }}" class="nav-link "><i class="fas fa-thumbtack"></i>
                        <span>Data Agenda</span>
                    </a>
                </li>
                <li class="nav-item  {{ $menu == 'berita' ? 'active' : '' }}">
                    <a href="{{ route('berita.index') }}" class="nav-link "><i class="fas fa-newspaper"></i>
                        <span>Data Berita</span>
                    </a>
                </li>
                <li class="nav-item  {{ $menu == 'artikel' ? 'active' : '' }}">
                    <a href="{{ route('artikel.index') }}" class="nav-link "><i class="fas fa-window-maximize"></i>
                        <span>Data Artikel</span>
                    </a>
                </li>
            @endif

            @if (session('role') == 'keuangan')
                <li class="{{ $menu == 'internal' ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('internal.index') }}">
                        <i class="fas fa-sign-out-alt"></i> <span>Data Internal</span>
                    </a>
                </li>

                <li class="{{ $menu == 'guru' ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('guru.index') }}">
                        <i class="fas fa-chalkboard-teacher"></i> <span>Data Eksternal</span>
                    </a>
                </li>

                <li class="nav-item dropdown {{ $menu == 'honor' ? 'active' : '' }}">
                    <a href="#" class="nav-link has-dropdown"><i class="fas fa-th"></i>
                        <span>Data Keuangan</span></a>
                    <ul class="dropdown-menu">

                        {{-- <li class="{{ $title == 'Data Honor Kegiatan' ? '' : '' }}">
                        <a class="nav-link" href="{{ route('honor.index') }}">
                            Penomoran 
                        </a>
                    </li> --}}

                        <li class="{{ $title == 'Data Honor Kegiatan' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('honor.index') }}">
                                Honor
                            </a>
                        </li>
                        <li class="{{ $title == 'Data Kuitansi' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('kuitansi.index') }}">
                                <span>Kuitansi Kegiatan</span>
                            </a>
                        </li>
                        <li class="{{ $title == 'Data Kuitansi Lokakarya' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('kuitansiLoka.index') }}">
                                <span>Kuitansi Lokakarya</span>
                            </a>
                        </li>

                    </ul>
                </li>
            @endif

            @if (session('role') == 'kepegawaian')
                <li class="nav-item dropdown {{ $menu == 'pegawai' ? 'active' : '' }}">
                    <a href="#" class="nav-link has-dropdown"><i class="fas fa-sitemap"></i>
                        <span>Master Data</span></a>
                    <ul class="dropdown-menu">

                        <li class="{{ $menu == 'pegawai' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('pegawai.index') }}">
                                Data Pegawai BBPG
                            </a>
                        </li>

                    </ul>
                </li>

                <li class="{{ $menu == 'internal' ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('internal.index') }}">
                        <i class="fas fa-sign-out-alt"></i> <span>Data Internal</span>
                    </a>
                </li>

                <li class="{{ $menu == 'guru' ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('guru.index') }}">
                        <i class="fas fa-chalkboard-teacher"></i> <span>Data Eksternal</span>
                    </a>
                </li>
            @endif

            @if (session('role') == 'kegiatan')
                <li class="{{ $menu == 'internal' ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('internal.index') }}">
                        <i class="fas fa-sign-out-alt"></i> <span>Data Internal</span>
                    </a>
                </li>

                <li class="{{ $menu == 'guru' ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('guru.index') }}">
                        <i class="fas fa-chalkboard-teacher"></i> <span>Data Eksternal</span>
                    </a>
                </li>

                <li class="nav-item dropdown {{ $menu == 'kegiatan' || $menu == 'peserta' ? 'active' : '' }}">
                    <a href="#" class="nav-link has-dropdown"><i class="fas fa-calendar-week"></i>
                        <span>Data Kegiatan</span></a>
                    <ul class="dropdown-menu">

                        <li class="{{ $menu == 'kegiatan' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('kegiatan.index') }}">
                                Kegiatan</span>
                            </a>
                        </li>

                        <li class="{{ $menu == 'peserta' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('peserta.index') }}">
                                Peserta Kegiatan
                            </a>
                        </li>

                    </ul>
                </li>
            @endif

            @if (Session('role') == 'pegawai')
                <li class="{{ $menu == 'pegawai' ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('pegawai.show', session('no_ktp')) }}">
                        <i class="fas fa-sign-out-alt"></i> <span>Data Internal</span>
                    </a>
                </li>
                <li class="{{ $menu == 'berkas' ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('berkas.index', session('no_ktp')) }}">
                        <i class="fas fa-sign-out-alt"></i> <span>Laporan Kegiatan</span>
                    </a>
                </li>
            @endif

            @if (Session('role') == 'tenaga pendidik' ||
                    Session('role') == 'tenaga kependidikan' ||
                    Session('role') == 'stakeholder')
                <li class="{{ $menu == 'guru' ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('guru.show', session('no_ktp')) }}">
                        <i class="fas fa-chalkboard-teacher"></i> <span>Data Eksternal</span>
                    </a>
                </li>
            @endif


            {{-- @if (Session('role') != 'guru' || Session('role' != 'pegawai')) --}}
            {{-- <li class="{{ $menu == 'kepegawaian' ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('kepegawaian.index') }}">
                        <i class="fas fa-briefcase"></i> <span>Status Kepegawaian</span></a>
                </li>

                <li class="{{ $menu == 'kependidikan' ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('kependidikan.index') }}">
                        <i class="fas fa-user-graduate"></i> <span>Satuan Pendidikan</span></a>
                </li> --}}

            {{-- <li class="{{ $menu == 'kependidikan' ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('kependidikan.index') }}">
                        <i class="fas fa-chalkboard-teacher"></i> <span>Data Sekolah</span></a>
                </li> --}}
            {{-- @endif --}}




        </ul>

        <div class="mt-4 mb-4 p-3 hide-sidebar-mini">
            <a href="{{ route('logout') }}" class="btn btn-danger btn-lg btn-block btn-icon-split">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </aside>
</div>
