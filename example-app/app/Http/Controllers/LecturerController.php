<?php
namespace App\Http\Controllers;
use App\Models\{Lecturer,StudyProgram}; use Illuminate\Http\Request;
class LecturerController extends Controller{
 public function index(Request $r){$items=Lecturer::with('studyProgram')->when($r->search,fn($q)=>$q->where('name','like','%'.$r->search.'%')->orWhere('nidn','like','%'.$r->search.'%'))->paginate(10)->withQueryString();return view('dosen.index',compact('items')); }
 public function create(){return view('dosen.form',['item'=>new Lecturer,'programs'=>StudyProgram::where('is_active',true)->get()]);}
 public function store(Request $r){$d=$r->validate(['study_program_id'=>'nullable|exists:study_programs,id','nidn'=>'required|max:30|unique:lecturers,nidn','name'=>'required|max:150','email'=>'required|email|unique:lecturers,email','phone'=>'nullable|max:30','status'=>'required|in:aktif,tidak_aktif']);Lecturer::create($d);return redirect()->route('dosen.index')->with('success','Dosen berhasil ditambahkan.');}
 public function edit(Lecturer $dosen){return view('dosen.form',['item'=>$dosen,'programs'=>StudyProgram::where('is_active',true)->get()]);}
 public function update(Request $r,Lecturer $dosen){$d=$r->validate(['study_program_id'=>'nullable|exists:study_programs,id','nidn'=>'required|max:30|unique:lecturers,nidn,'.$dosen->id,'name'=>'required|max:150','email'=>'required|email|unique:lecturers,email,'.$dosen->id,'phone'=>'nullable|max:30','status'=>'required|in:aktif,tidak_aktif']);$dosen->update($d);return redirect()->route('dosen.index')->with('success','Dosen berhasil diperbarui.');}
 public function destroy(Lecturer $dosen){$dosen->delete();return back()->with('success','Dosen berhasil dihapus.');}
}
