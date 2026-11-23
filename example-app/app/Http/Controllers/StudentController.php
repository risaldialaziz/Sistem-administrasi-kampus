<?php
namespace App\Http\Controllers;
use App\Models\{Student,StudyProgram};
use Illuminate\Http\Request;
class StudentController extends Controller
{
    public function index(Request $r){
        $items=Student::with('studyProgram')->when($r->search,fn($q)=>$q->where('name','like','%'.$r->search.'%')->orWhere('nim','like','%'.$r->search.'%'))->when($r->status,fn($q)=>$q->where('status',$r->status))->latest()->paginate(10)->withQueryString();
        return view('mahasiswa.index',compact('items'));
    }
    public function create(){return view('mahasiswa.form',['item'=>new Student,'programs'=>StudyProgram::where('is_active',true)->get()]);}
    public function store(Request $r){
        $data=$r->validate(['study_program_id'=>'required|exists:study_programs,id','nim'=>'required|max:30|unique:students,nim','name'=>'required|max:150','email'=>'required|email|unique:students,email','phone'=>'nullable|max:30','gender'=>'nullable|in:L,P','birth_date'=>'nullable|date','address'=>'nullable|max:1000','admission_year'=>'required|integer|min:2000|max:2100','status'=>'required|in:aktif,cuti,lulus,dikeluarkan']);
        Student::create($data);return redirect()->route('mahasiswa.index')->with('success','Mahasiswa berhasil ditambahkan.');
    }
    public function show(Student $mahasiswa){return view('mahasiswa.show',['item'=>$mahasiswa->load(['studyProgram','krs.campusClass.course','payments'])]);}
    public function edit(Student $mahasiswa){return view('mahasiswa.form',['item'=>$mahasiswa,'programs'=>StudyProgram::where('is_active',true)->get()]);}
    public function update(Request $r,Student $mahasiswa){$data=$r->validate(['study_program_id'=>'required|exists:study_programs,id','nim'=>'required|max:30|unique:students,nim,'.$mahasiswa->id,'name'=>'required|max:150','email'=>'required|email|unique:students,email,'.$mahasiswa->id,'phone'=>'nullable|max:30','gender'=>'nullable|in:L,P','birth_date'=>'nullable|date','address'=>'nullable|max:1000','admission_year'=>'required|integer|min:2000|max:2100','status'=>'required|in:aktif,cuti,lulus,dikeluarkan']);$mahasiswa->update($data);return redirect()->route('mahasiswa.index')->with('success','Mahasiswa berhasil diperbarui.');}
    public function destroy(Student $mahasiswa){$mahasiswa->delete();return back()->with('success','Mahasiswa berhasil dihapus.');}
}
