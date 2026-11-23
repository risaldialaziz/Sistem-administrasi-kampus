<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $query = Announcement::query()->latest();
        $role = auth()->user()->role;

        if (in_array($role, ['super_admin', 'admin_kampus', 'admin_akademik'], true)) {
            $items = $query->paginate(10);
        } else {
            $audience = match ($role) {
                'mahasiswa' => 'mahasiswa',
                'dosen' => 'dosen',
                default => 'staf',
            };
            $items = $query->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->whereIn('audience', ['semua', $audience])
                ->paginate(10);
        }

        return view('pengumuman.index', compact('items'));
    }

    public function create()
    {
        return view('pengumuman.form', ['item' => new Announcement]);
    }

    public function store(Request $r)
    {
        $d = $r->validate(['title' => 'required|max:150', 'content' => 'required|max:5000', 'audience' => 'required|in:semua,mahasiswa,dosen,staf', 'published_at' => 'nullable|date']);
        $d['created_by'] = auth()->id();
        Announcement::create($d);

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil ditambahkan.');
    }

    public function edit(Announcement $pengumuman)
    {
        return view('pengumuman.form', ['item' => $pengumuman]);
    }

    public function update(Request $r, Announcement $pengumuman)
    {
        $d = $r->validate(['title' => 'required|max:150', 'content' => 'required|max:5000', 'audience' => 'required|in:semua,mahasiswa,dosen,staf', 'published_at' => 'nullable|date']);
        $pengumuman->update($d);

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $pengumuman)
    {
        $pengumuman->delete();

        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}
