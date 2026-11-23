<?php
namespace App\Http\Controllers;
use App\Models\{StudyProgram,Faculty}; use Illuminate\Http\Request;
class StudyProgramController extends Controller{
 public function index(Request $r){$items=StudyProgram::with('faculty')->when($r->search,fn($q)=>$q->where('name','like','%'.$r->search.'%')->orWhere('code','like','%'.$r->search.'%'))->paginate(10)->withQueryString();return view('program-studi.index',compact('items'));}
 public function create(){return view('program-studi.form',['item'=>new StudyProgram,'faculties'=>Faculty::where('is_active',true)->get()]);}
 public function store(Request $r){$d=$r->validate(['faculty_id'=>'required|exists:faculties,id','code'=>'required|max:20|unique:study_programs','name'=>'required|max:150','degree'=>'required|max:30','is_active'=>'boolean']);StudyProgram::create($d);return redirect()->route('program-studi.index')->with('success','Program studi berhasil ditambahkan.');}
 public function edit(StudyProgram $programStudi){return view('program-studi.form',['item'=>$programStudi,'faculties'=>Faculty::where('is_active',true)->get()]);}
 public function update(Request $r,StudyProgram $programStudi){$d=$r->validate(['faculty_id'=>'required|exists:faculties,id','code'=>'required|max:20|unique:study_programs,code,'. $programStudi->id,'name'=>'required|max:150','degree'=>'required|max:30','is_active'=>'boolean']);$programStudi->update($d);return redirect()->route('program-studi.index')->with('success','Program studi berhasil diperbarui.');}
 public function destroy(StudyProgram $programStudi){$programStudi->delete();return back()->with('success','Program studi berhasil dihapus.');}
}
