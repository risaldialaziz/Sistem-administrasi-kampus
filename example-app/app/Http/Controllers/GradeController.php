<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Krs;
use App\Models\Lecturer;
use App\Models\Student;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $query = Grade::with(['krs.student', 'krs.campusClass.course']);

        if ($request->user()->role === 'mahasiswa') {
            $student = Student::where('user_id', $request->user()->id)->firstOrFail();
            $query->whereHas('krs', fn ($krs) => $krs->where('student_id', $student->id));
        } elseif ($request->user()->role === 'dosen') {
            $lecturer = Lecturer::where('user_id', $request->user()->id)->firstOrFail();
            $query->whereHas('krs.campusClass', fn ($classes) => $classes->where('lecturer_id', $lecturer->id));
        }

        $items = $query
            ->when($request->filled('search'), fn ($query) => $query->whereHas('krs.student', fn ($students) => $students->where('name', 'like', '%'.$request->string('search').'%')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $pendingKrs = collect();
        if (in_array($request->user()->role, ['super_admin', 'admin_akademik', 'dosen'], true)) {
            $pendingQuery = Krs::with(['student', 'campusClass.course'])
                ->where('status', 'disetujui')
                ->whereDoesntHave('grade');

            if ($request->user()->role === 'dosen') {
                $lecturer = Lecturer::where('user_id', $request->user()->id)->firstOrFail();
                $pendingQuery->whereHas('campusClass', fn ($classes) => $classes->where('lecturer_id', $lecturer->id));
            }

            $pendingKrs = $pendingQuery->orderBy('id')->get();
        }

        return view('nilai.index', compact('items', 'pendingKrs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'krs_id' => ['required', 'exists:krs,id'],
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);
        $krs = Krs::with('campusClass')->findOrFail($data['krs_id']);
        $this->authorizeKrs($request, $krs);
        abort_unless($krs->status === 'disetujui', 422, 'Nilai hanya dapat diberikan pada KRS yang disetujui.');

        Grade::updateOrCreate(
            ['krs_id' => $krs->id],
            $this->gradeValues($data['score'])
        );

        return back()->with('success', 'Nilai berhasil disimpan.');
    }

    public function update(Request $request, Grade $nilai)
    {
        $data = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);
        $nilai->load('krs.campusClass');
        $this->authorizeKrs($request, $nilai->krs);
        $nilai->update($this->gradeValues($data['score']));

        return back()->with('success', 'Nilai berhasil diperbarui.');
    }

    public function destroy(Request $request, Grade $nilai)
    {
        $nilai->load('krs.campusClass');
        $this->authorizeKrs($request, $nilai->krs);
        $nilai->delete();

        return back()->with('success', 'Nilai berhasil dihapus.');
    }

    public function khs(Request $request)
    {
        $student = Student::where('user_id', $request->user()->id)->firstOrFail();
        $items = Krs::with(['campusClass.course', 'grade'])
            ->where('student_id', $student->id)
            ->where('status', 'disetujui')
            ->get();

        return view('khs.index', compact('items'));
    }

    private function authorizeKrs(Request $request, Krs $krs): void
    {
        if ($request->user()->role === 'dosen') {
            $lecturer = Lecturer::where('user_id', $request->user()->id)->firstOrFail();
            abort_unless($krs->campusClass->lecturer_id === $lecturer->id, 404);
        }
    }

    private function gradeValues(float|string $score): array
    {
        $score = (float) $score;
        [$letter, $point] = match (true) {
            $score >= 85 => ['A', 4.00],
            $score >= 80 => ['A-', 3.70],
            $score >= 75 => ['B+', 3.30],
            $score >= 70 => ['B', 3.00],
            $score >= 65 => ['B-', 2.70],
            $score >= 60 => ['C+', 2.30],
            $score >= 55 => ['C', 2.00],
            $score >= 50 => ['D', 1.00],
            default => ['E', 0.00],
        };

        return ['score' => $score, 'letter_grade' => $letter, 'grade_point' => $point];
    }
}
