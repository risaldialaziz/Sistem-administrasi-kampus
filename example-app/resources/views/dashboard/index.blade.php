@extends('layouts.app')
@section('title','Dashboard')
@section('content')
<div class="mb-6"><h1 class="text-2xl font-bold">Ringkasan Kampus</h1><p class="text-sm text-slate-500">Selamat datang, {{ auth()->user()->name }}</p></div>
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
 @php
 $cards = match(auth()->user()->role) {
     'mahasiswa' => [['KRS Saya',$stats['krs'],'text-violet-700','bg-violet-50'],['UKT Lunas','Rp '.number_format($stats['paid'],0,',','.'),'text-teal-700','bg-teal-50'],['Tagihan Saya',$stats['pending'],'text-amber-700','bg-amber-50']],
     'dosen' => [['Kelas Diampu',$stats['classes'],'text-indigo-700','bg-indigo-50'],['Peserta KRS',$stats['krs'],'text-violet-700','bg-violet-50']],
     default => [['Mahasiswa',$stats['students'],'text-indigo-700','bg-indigo-50'],['Dosen',$stats['lecturers'],'text-emerald-700','bg-emerald-50'],['Program Studi',$stats['programs'],'text-sky-700','bg-sky-50'],['Mata Kuliah',$stats['courses'],'text-amber-700','bg-amber-50'],['KRS Aktif',$stats['krs'],'text-violet-700','bg-violet-50'],['Pembayaran Lunas','Rp '.number_format($stats['paid'],0,',','.'),'text-teal-700','bg-teal-50'],['Tagihan Perlu Tindak',$stats['pending'],'text-red-700','bg-red-50']],
 };
 @endphp
 @foreach($cards as $card)
 <div class="{{ $card[3] }} rounded-xl border border-white p-5 shadow-sm"><p class="text-sm font-medium text-slate-500">{{ $card[0] }}</p><p class="mt-2 text-2xl font-bold {{ $card[2] }}">{{ $card[1] }}</p></div>
 @endforeach
</div>
@if(in_array(auth()->user()->role,['super_admin','admin_kampus','admin_akademik','admin_keuangan','mahasiswa']))
<div class="mt-6 grid gap-6 xl:grid-cols-2">
<section class="rounded-xl bg-white shadow-sm"><div class="border-b px-5 py-4 font-semibold">Mahasiswa Terbaru</div><div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">NIM</th><th>Nama</th><th>Program Studi</th></tr></thead><tbody>@forelse($recentStudents as $s)<tr class="border-t"><td class="px-5 py-3">{{ $s->nim }}</td><td>{{ $s->name }}</td><td>{{ $s->studyProgram->name ?? '-' }}</td></tr>@empty<tr><td colspan="3" class="px-5 py-6 text-center text-slate-400">Belum ada data.</td></tr>@endforelse</tbody></table></div></section>
<section class="rounded-xl bg-white shadow-sm"><div class="border-b px-5 py-4 font-semibold">Pembayaran Terbaru</div><div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Invoice</th><th>Mahasiswa</th><th>Status</th></tr></thead><tbody>@forelse($recentPayments as $p)<tr class="border-t"><td class="px-5 py-3">{{ $p->invoice_number }}</td><td>{{ $p->student->name ?? '-' }}</td><td><span class="rounded-full bg-slate-100 px-2 py-1 text-xs">{{ $p->status }}</span></td></tr>@empty<tr><td colspan="3" class="px-5 py-6 text-center text-slate-400">Belum ada data.</td></tr>@endforelse</tbody></table></div></section>
</div>
@endif
@endsection
