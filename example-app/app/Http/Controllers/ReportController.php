<?php
namespace App\Http\Controllers;
use App\Models\{Student,TuitionPayment,Krs};
use Symfony\Component\HttpFoundation\StreamedResponse;
class ReportController extends Controller{
 public function academic(){return view('laporan.akademik',['students'=>Student::withCount('krs')->latest()->limit(100)->get()]);}
 public function exportAcademic(): StreamedResponse{return response()->streamDownload(function(){echo "NIM,Nama,Status,Tahun Masuk\n";foreach(Student::with('studyProgram')->get() as $s){echo implode(',',[$s->nim,str_replace(',',' ', $s->name),$s->status,$s->admission_year])."\n";}},'laporan-mahasiswa.csv',['Content-Type'=>'text/csv']);}
 public function finance(){return view('laporan.keuangan',['payments'=>TuitionPayment::with('student')->latest()->limit(100)->get(),'total'=>TuitionPayment::where('status','lunas')->sum('amount'),'pending'=>TuitionPayment::whereIn('status',['belum_bayar','menunggu_verifikasi'])->count()]);}
}
