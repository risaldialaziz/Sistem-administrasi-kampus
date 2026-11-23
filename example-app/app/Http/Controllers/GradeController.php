<?php
namespace App\Http\Controllers;
use App\Models\{Grade,Krs,Student}; use Illuminate\Http\Request;
class GradeController extends Controller{
 public function index(Request $r){$items=Grade::with(['krs.student','krs.campusClass.course'])->when($r->search,fn($q)=>$q->whereHas('krs.student',fn($s)=>$s->where('name','like','%'.$r->search.'%')))->paginate(10)->withQueryString();return view('nilai.index',compact('items')); }
 public function store(Request $r){$d=$r->validate(['krs_id'=>'required|exists:krs,id','score'=>'nullable|numeric|min:0|max:100','letter_grade'=>'nullable|max:2','grade_point'=>'nullable|numeric|min:0|max:4']);Grade::updateOrCreate(['krs_id'=>$d['krs_id']],$d);return back()->with('success','Nilai berhasil disimpan.');}
 public function update(Request $r,Grade $nilai){$d=$r->validate(['score'=>'nullable|numeric|min:0|max:100','letter_grade'=>'nullable|max:2','grade_point'=>'nullable|numeric|min:0|max:4']);$nilai->update($d);return back()->with('success','Nilai berhasil diperbarui.');}
 public function destroy(Grade $nilai){$nilai->delete();return back()->with('success','Nilai berhasil dihapus.');}
 public function khs(){ $student=Student::where('email',auth()->user()->email)->first(); $items=$student?Krs::with(['campusClass.course','grade'])->where('student_id',$student->id)->get():collect(); return view('khs.index',compact('items')); }
}
