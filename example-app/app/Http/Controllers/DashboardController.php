<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Krs;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\StudyProgram;
use App\Models\TuitionPayment;

class DashboardController extends Controller
{
    public function index()
    {
        $role = auth()->user()->role;

        if ($role === 'mahasiswa') {
            $student = Student::where('user_id', auth()->id())->firstOrFail();
            $stats = [
                'krs' => $student->krs()->count(),
                'paid' => $student->payments()->where('status', 'lunas')->sum('amount'),
                'pending' => $student->payments()->whereIn('status', ['belum_bayar', 'menunggu_verifikasi'])->count(),
            ];
            $recentStudents = collect();
            $recentPayments = $student->payments()->latest()->limit(5)->get();

            return view('dashboard.index', compact('stats', 'recentStudents', 'recentPayments'));
        }

        if ($role === 'dosen') {
            $lecturer = Lecturer::where('user_id', auth()->id())->firstOrFail();
            $stats = [
                'classes' => $lecturer->classes()->count(),
                'krs' => Krs::whereHas('campusClass', fn ($query) => $query->where('lecturer_id', $lecturer->id))->count(),
            ];
            $recentStudents = collect();
            $recentPayments = collect();

            return view('dashboard.index', compact('stats', 'recentStudents', 'recentPayments'));
        }

        $stats = [
            'students' => Student::count(), 'lecturers' => Lecturer::count(), 'programs' => StudyProgram::count(),
            'courses' => Course::count(), 'krs' => Krs::count(),
            'paid' => TuitionPayment::where('status', 'lunas')->sum('amount'),
            'pending' => TuitionPayment::whereIn('status', ['belum_bayar', 'menunggu_verifikasi'])->count(),
        ];
        $recentStudents = Student::with('studyProgram')->latest()->limit(5)->get();
        $recentPayments = TuitionPayment::with('student')->latest()->limit(5)->get();

        return view('dashboard.index', compact('stats', 'recentStudents', 'recentPayments'));
    }
}
