<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','Rokania') - Administrasi Kampus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-100 text-slate-800" x-data="{open:true,mobile:false}">
<div class="min-h-screen lg:flex">
    <aside class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-950 text-white transition lg:relative" :class="mobile?'translate-x-0':'-translate-x-full lg:translate-x-0'">
        <div class="flex h-16 items-center border-b border-slate-800 px-5"><div class="mr-3 grid h-9 w-9 place-items-center rounded-lg bg-indigo-500 font-bold">R</div><div><p class="font-semibold">Rokania</p><p class="text-xs text-slate-400">Administrasi Kampus</p></div></div>
        <nav class="space-y-1 p-3 text-sm">
            <a href="{{ route('dashboard') }}" class="block rounded-lg px-3 py-2.5 hover:bg-slate-800">Dashboard</a>
            @if(in_array(auth()->user()->role,['super_admin','admin_kampus','admin_akademik']))
            <p class="px-3 pt-4 text-xs font-bold uppercase text-slate-500">Data Akademik</p>
            <a href="{{ route('mahasiswa.index') }}" class="block rounded-lg px-3 py-2.5 hover:bg-slate-800">Mahasiswa</a>
            <a href="{{ route('dosen.index') }}" class="block rounded-lg px-3 py-2.5 hover:bg-slate-800">Dosen</a>
            <a href="{{ route('fakultas.index') }}" class="block rounded-lg px-3 py-2.5 hover:bg-slate-800">Fakultas</a>
            <a href="{{ route('program-studi.index') }}" class="block rounded-lg px-3 py-2.5 hover:bg-slate-800">Program Studi</a>
            <a href="{{ route('mata-kuliah.index') }}" class="block rounded-lg px-3 py-2.5 hover:bg-slate-800">Mata Kuliah</a>
            <a href="{{ route('jadwal.index') }}" class="block rounded-lg px-3 py-2.5 hover:bg-slate-800">Jadwal Kuliah</a>
            <a href="{{ route('tahun-akademik.index') }}" class="block rounded-lg px-3 py-2.5 hover:bg-slate-800">Tahun Akademik</a>
            @endif
            <a href="{{ route('krs.index') }}" class="block rounded-lg px-3 py-2.5 hover:bg-slate-800">KRS</a>
            <a href="{{ route('khs.index') }}" class="block rounded-lg px-3 py-2.5 hover:bg-slate-800">Nilai & KHS</a>
            @if(in_array(auth()->user()->role,['super_admin','admin_kampus','admin_keuangan','mahasiswa']))
            <a href="{{ route('pembayaran.index') }}" class="block rounded-lg px-3 py-2.5 hover:bg-slate-800">Pembayaran UKT</a>
            @endif
            <a href="{{ route('documents.index') }}" class="block rounded-lg px-3 py-2.5 hover:bg-slate-800">Surat & Dokumen</a>
            <a href="{{ route('pengumuman.index') }}" class="block rounded-lg px-3 py-2.5 hover:bg-slate-800">Pengumuman</a>
            @if(in_array(auth()->user()->role,['super_admin','admin_kampus','admin_akademik','admin_keuangan']))
            <p class="px-3 pt-4 text-xs font-bold uppercase text-slate-500">Sistem</p>
            @if(in_array(auth()->user()->role,['super_admin','admin_kampus','admin_akademik']))<a href="{{ route('users.index') }}" class="block rounded-lg px-3 py-2.5 hover:bg-slate-800">Pengguna & Role</a>@endif
            <a href="{{ route('reports.academic') }}" class="block rounded-lg px-3 py-2.5 hover:bg-slate-800">Laporan Akademik</a>
            @if(in_array(auth()->user()->role,['super_admin','admin_kampus','admin_keuangan']))<a href="{{ route('reports.finance') }}" class="block rounded-lg px-3 py-2.5 hover:bg-slate-800">Laporan Keuangan</a>@endif
            <a href="{{ route('audit.index') }}" class="block rounded-lg px-3 py-2.5 hover:bg-slate-800">Audit Log</a>
            @if(in_array(auth()->user()->role,['super_admin','admin_kampus']))<a href="{{ route('settings.edit') }}" class="block rounded-lg px-3 py-2.5 hover:bg-slate-800">Pengaturan Sistem</a>@endif
            @endif
        </nav>
    </aside>
    <div class="min-w-0 flex-1">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b bg-white px-4 shadow-sm lg:px-8">
            <button class="rounded-lg p-2 hover:bg-slate-100 lg:hidden" @click="mobile=!mobile">☰</button>
            <div class="hidden lg:block text-sm text-slate-500">@yield('title','Dashboard')</div>
            <div class="flex items-center gap-3">
                <a href="{{ route('notifications.index') }}" class="rounded-full p-2 hover:bg-slate-100">🔔</a>
                <a href="{{ route('profile.edit') }}" class="rounded-full bg-indigo-100 px-3 py-2 text-sm font-semibold text-indigo-700">{{ auth()->user()->name }}</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-sm text-slate-500 hover:text-red-600">Keluar</button></form>
            </div>
        </header>
        <main class="p-4 lg:p-8">
            @if(session('success'))<div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </main>
    </div>
</div>
</body></html>
