<?php
namespace App\Http\Controllers;
use App\Models\{Krs,Student,CampusClass}; use Illuminate\Http\Request;
class KrsController extends Controller{
 public function index(Request $r){$q=Krs::with(['student','campusClass.course','campusClass.lecturer'])->when($r->status,fn($q)=>$q->where('status',$r->status));if(auth()->user()->role==='mahasiswa'){$student=Student::where('email',auth()->user()->email)->first();$q->when($student,fn($x)=>$x->where('student_id',$student->id));}$items=$q->latest()->paginate(10)->withQueryString();return view('krs.index',compact('items')); }
 public function create(){return view('krs.form',['students'=>Student::where('status','aktif')->get(),'classes'=>CampusClass::with('course')->get()]);}
 public function store(Request $r){$d=$r->validate(['student_id'=>'required|exists:students,id','class_id'=>'required|exists:classes,id','academic_year'=>'required|max:20','term'=>'required|in:ganjil,genap','status'=>'required|in:draft,diajukan,disetujui,ditolak,dibatalkan']);Krs::create($d);return redirect()->route('krs.index')->with('success','KRS berhasil ditambahkan.');}
 public function show(Krs $krs){return view('krs.show',['item'=>$krs->load(['student','campusClass.course','campusClass.lecturer','grade'])]);}
 public function status(Request $r,Krs $krs){$r->validate(['status'=>'required|in:draft,diajukan,disetujui,ditolak,dibatalkan']);$krs->update(['status'=>$r->status]);return back()->with('success','Status KRS diperbarui.');}
}
