<?php

namespace App\Http\Controllers;

use App\Models\CampusClass;
use App\Models\Krs;
use App\Models\Lecturer;
use App\Models\Student;
use Illuminate\Http\Request;

class KrsController extends Controller
{
    public function index(Request $request)
    {
        $query = Krs::with(['student', 'campusClass.course', 'campusClass.lecturer'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')));

        if ($request->user()->role === 'mahasiswa') {
            $student = Student::where('user_id', $request->user()->id)->firstOrFail();
            $query->where('student_id', $student->id);
        } elseif ($request->user()->role === 'dosen') {
            $lecturer = Lecturer::where('user_id', $request->user()->id)->firstOrFail();
            $query->whereHas('campusClass', fn ($classes) => $classes->where('lecturer_id', $lecturer->id));
        }

        $items = $query->latest()->paginate(10)->withQueryString();

        return view('krs.index', compact('items'));
    }

    public function create(Request $request)
    {
        $student = $request->user()->role === 'mahasiswa'
            ? Student::where('user_id', $request->user()->id)->firstOrFail()
            : null;

        $students = $student ? collect([$student]) : Student::where('status', 'aktif')->orderBy('name')->get();
        $classes = CampusClass::with('course')
            ->when($student, fn ($query) => $query->whereHas('course', fn ($courses) => $courses->where('study_program_id', $student->study_program_id)))
            ->orderBy('name')
            ->get();

        return view('krs.form', [
            'students' => $students,
            'classes' => $classes,
            'isStudent' => $student !== null,
        ]);
    }

    public function store(Request $request)
    {
        $isStudent = $request->user()->role === 'mahasiswa';
        $student = $isStudent
            ? Student::where('user_id', $request->user()->id)->firstOrFail()
            : Student::findOrFail($request->validate([
                'student_id' => ['required', 'exists:students,id'],
            ])['student_id']);

        $data = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'academic_year' => ['required', 'string', 'max:20'],
            'term' => ['required', 'in:ganjil,genap'],
            'status' => [$isStudent ? 'prohibited' : 'nullable', 'in:draft,diajukan,disetujui,ditolak,dibatalkan'],
        ]);

        $class = CampusClass::with('course')->findOrFail($data['class_id']);
        abort_unless(
            $class->academic_year === $data['academic_year']
            && $class->term === $data['term']
            && (! $isStudent || $class->course->study_program_id === $student->study_program_id),
            422,
            'Kelas tidak tersedia untuk program studi atau periode akademik yang dipilih.'
        );

        if (Krs::where('student_id', $student->id)->where('class_id', $class->id)->exists()) {
            return back()->withInput()->withErrors(['class_id' => 'Mahasiswa sudah terdaftar pada kelas ini.']);
        }

        $activeEnrollmentCount = Krs::where('class_id', $class->id)
            ->whereNotIn('status', ['ditolak', 'dibatalkan'])
            ->count();

        if ($activeEnrollmentCount >= $class->capacity) {
            return back()->withInput()->withErrors(['class_id' => 'Kapasitas kelas sudah penuh.']);
        }

        Krs::create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'academic_year' => $data['academic_year'],
            'term' => $data['term'],
            'status' => $isStudent ? 'diajukan' : ($data['status'] ?? 'draft'),
        ]);

        return redirect()->route('krs.index')->with('success', 'KRS berhasil ditambahkan.');
    }

    public function show(Request $request, Krs $krs)
    {
        if ($request->user()->role === 'mahasiswa') {
            abort_unless($krs->student()->where('user_id', $request->user()->id)->exists(), 404);
        } elseif ($request->user()->role === 'dosen') {
            $lecturer = Lecturer::where('user_id', $request->user()->id)->firstOrFail();
            abort_unless($krs->campusClass()->where('lecturer_id', $lecturer->id)->exists(), 404);
        }

        return view('krs.show', ['item' => $krs->load(['student', 'campusClass.course', 'campusClass.lecturer', 'grade'])]);
    }

    public function status(Request $request, Krs $krs)
    {
        $data = $request->validate([
            'status' => ['required', 'in:draft,diajukan,disetujui,ditolak,dibatalkan'],
        ]);

        $krs->update($data);

        return back()->with('success', 'Status KRS diperbarui.');
    }
}
