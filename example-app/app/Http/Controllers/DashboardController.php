<?php
namespace App\Http\Controllers;
use App\Models\{Student, Lecturer, Course, TuitionPayment, StudyProgram, Krs};
class DashboardController extends Controller
{
    public function index(){
        $stats=[
            'students'=>Student::count(), 'lecturers'=>Lecturer::count(), 'programs'=>StudyProgram::count(),
            'courses'=>Course::count(), 'krs'=>Krs::count(),
            'paid'=>TuitionPayment::where('status','lunas')->sum('amount'),
            'pending'=>TuitionPayment::whereIn('status',['belum_bayar','menunggu_verifikasi'])->count(),
        ];
        $recentStudents=Student::with('studyProgram')->latest()->limit(5)->get();
        $recentPayments=TuitionPayment::with('student')->latest()->limit(5)->get();
        return view('dashboard.index',compact('stats','recentStudents','recentPayments'));
    }
}
