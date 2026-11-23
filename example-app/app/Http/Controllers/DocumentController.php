<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::with('student');

        if ($request->user()->role === 'mahasiswa') {
            $student = Student::where('user_id', $request->user()->id)->firstOrFail();
            $query->where('student_id', $student->id);
        }

        $items = $query->latest()->paginate(10)->withQueryString();
        $students = Student::where('status', 'aktif')->orderBy('name')->get();

        return view('surat.index', compact('items', 'students'));
    }

    public function store(Request $request)
    {
        $isStudent = $request->user()->role === 'mahasiswa';
        $student = $isStudent
            ? Student::where('user_id', $request->user()->id)->firstOrFail()
            : null;

        $data = $request->validate([
            'student_id' => [$isStudent ? 'prohibited' : 'nullable', 'exists:students,id'],
            'type' => ['required', 'string', 'max:50'],
            'number' => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:150'],
            'status' => [$isStudent ? 'prohibited' : 'nullable', 'in:draft,diproses,siap,diterbitkan,ditolak'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        $file = $request->file('file');
        unset($data['file']);

        if ($file) {
            $data['file_path'] = $file->store('documents');
        }

        $data['student_id'] = $isStudent ? $student->id : ($data['student_id'] ?? null);
        $data['status'] = $isStudent ? 'draft' : ($data['status'] ?? 'draft');
        $data['number'] = $isStudent ? null : ($data['number'] ?? null);
        $data['created_by'] = $request->user()->id;

        Document::create($data);

        return back()->with('success', 'Permohonan surat berhasil dibuat.');
    }

    public function download(Request $request, Document $document): StreamedResponse
    {
        $this->authorizeDocument($request, $document);
        abort_unless($document->file_path && Storage::exists($document->file_path), 404);

        return Storage::download($document->file_path);
    }

    public function status(Request $request, Document $document)
    {
        $data = $request->validate([
            'status' => ['required', 'in:draft,diproses,siap,diterbitkan,ditolak'],
        ]);
        $document->update($data);

        return back()->with('success', 'Status surat diperbarui.');
    }

    public function destroy(Document $document)
    {
        if ($document->file_path) {
            Storage::delete($document->file_path);
        }
        $document->delete();

        return back()->with('success', 'Surat berhasil dihapus.');
    }

    private function authorizeDocument(Request $request, Document $document): void
    {
        if ($request->user()->role === 'mahasiswa') {
            abort_unless($document->student()->where('user_id', $request->user()->id)->exists(), 404);
        }
    }
}
