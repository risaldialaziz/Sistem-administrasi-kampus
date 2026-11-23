<?php
namespace App\Http\Controllers;
use App\Models\{CampusClass,Course,Lecturer}; use Illuminate\Http\Request;
class ScheduleController extends Controller{
 public function index(Request $r){$items=CampusClass::with(['course','lecturer'])->when($r->search,fn($q)=>$q->where('name','like','%'.$r->search.'%')->orWhere('room','like','%'.$r->search.'%'))->paginate(10)->withQueryString();return view('jadwal.index',compact('items')); }
 public function create(){return view('jadwal.form',['item'=>new CampusClass,'courses'=>Course::where('is_active',true)->get(),'lecturers'=>Lecturer::where('status','aktif')->get()]);}
 public function store(Request $r){$d=$r->validate(['course_id'=>'required|exists:courses,id','lecturer_id'=>'nullable|exists:lecturers,id','name'=>'required|max:50','academic_year'=>'required|max:20','term'=>'required|in:ganjil,genap','room'=>'nullable|max:50','schedule'=>'nullable|max:100','capacity'=>'required|integer|min:1|max:500']);CampusClass::create($d);return redirect()->route('jadwal.index')->with('success','Jadwal berhasil ditambahkan.');}
 public function edit(CampusClass $jadwal){return view('jadwal.form',['item'=>$jadwal,'courses'=>Course::where('is_active',true)->get(),'lecturers'=>Lecturer::where('status','aktif')->get()]);}
 public function update(Request $r,CampusClass $jadwal){$d=$r->validate(['course_id'=>'required|exists:courses,id','lecturer_id'=>'nullable|exists:lecturers,id','name'=>'required|max:50','academic_year'=>'required|max:20','term'=>'required|in:ganjil,genap','room'=>'nullable|max:50','schedule'=>'nullable|max:100','capacity'=>'required|integer|min:1|max:500']);$jadwal->update($d);return redirect()->route('jadwal.index')->with('success','Jadwal berhasil diperbarui.');}
 public function destroy(CampusClass $jadwal){$jadwal->delete();return back()->with('success','Jadwal berhasil dihapus.');}
}
