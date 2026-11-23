<?php
namespace App\Http\Controllers;
use App\Models\{Course,StudyProgram}; use Illuminate\Http\Request;
class CourseController extends Controller{
 public function index(Request $r){$items=Course::with('studyProgram')->when($r->search,fn($q)=>$q->where('name','like','%'.$r->search.'%')->orWhere('code','like','%'.$r->search.'%'))->paginate(10)->withQueryString();return view('mata-kuliah.index',compact('items')); }
 public function create(){return view('mata-kuliah.form',['item'=>new Course,'programs'=>StudyProgram::where('is_active',true)->get()]);}
 public function store(Request $r){$d=$r->validate(['study_program_id'=>'required|exists:study_programs,id','code'=>'required|max:20|unique:courses','name'=>'required|max:150','credits'=>'required|integer|min:1|max:6','semester'=>'required|integer|min:1|max:14','is_active'=>'boolean']);Course::create($d);return redirect()->route('mata-kuliah.index')->with('success','Mata kuliah berhasil ditambahkan.');}
 public function edit(Course $mataKuliah){return view('mata-kuliah.form',['item'=>$mataKuliah,'programs'=>StudyProgram::where('is_active',true)->get()]);}
 public function update(Request $r,Course $mataKuliah){$d=$r->validate(['study_program_id'=>'required|exists:study_programs,id','code'=>'required|max:20|unique:courses,code,'.$mataKuliah->id,'name'=>'required|max:150','credits'=>'required|integer|min:1|max:6','semester'=>'required|integer|min:1|max:14','is_active'=>'boolean']);$mataKuliah->update($d);return redirect()->route('mata-kuliah.index')->with('success','Mata kuliah berhasil diperbarui.');}
 public function destroy(Course $mataKuliah){$mataKuliah->delete();return back()->with('success','Mata kuliah berhasil dihapus.');}
}
