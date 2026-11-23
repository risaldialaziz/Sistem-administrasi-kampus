@extends('layouts.app') @section('title',$item->exists?'Edit Fakultas':'Tambah Fakultas') @section('content')
<div class="mx-auto max-w-2xl rounded-xl bg-white p-6 shadow-sm"><h1 class="text-xl font-bold">{{ $item->exists?'Edit':'Tambah' }} Fakultas</h1><form method="POST" action="{{ $item->exists?route('fakultas.update',$item):route('fakultas.store') }}" class="mt-5 space-y-4">@csrf @if($item->exists)@method('PUT')@endif
<label class="block text-sm font-semibold">Kode<input name="code" value="{{ old('code',$item->code) }}" required class="mt-2 w-full rounded-lg border px-3 py-2"></label>
<label class="block text-sm font-semibold">Nama Fakultas<input name="name" value="{{ old('name',$item->name) }}" required class="mt-2 w-full rounded-lg border px-3 py-2"></label>
<label class="block text-sm font-semibold">Nama Dekan<input name="dean_name" value="{{ old('dean_name',$item->dean_name) }}" class="mt-2 w-full rounded-lg border px-3 py-2"></label>
<label class="block text-sm"><input type="checkbox" name="is_active" value="1" {{ old('is_active',$item->is_active??true)?'checked':'' }}> Aktif</label><div class="flex gap-3"><button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white">Simpan</button><a href="{{ route('fakultas.index') }}" class="rounded-lg border px-5 py-2.5 text-sm">Batal</a></div></form></div>
@endsection
