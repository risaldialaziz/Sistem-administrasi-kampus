<?php
namespace App\Http\Controllers;
use App\Models\Faculty;
use Illuminate\Http\Request;
class FacultyController extends Controller
{
    public function index(Request $r){$items=Faculty::withCount('studyPrograms')->when($r->search,fn($q)=>$q->where('name','like','%'.$r->search.'%')->orWhere('code','like','%'.$r->search.'%'))->latest()->paginate(10)->withQueryString();return view('fakultas.index',compact('items')); }
    public function create(){return view('fakultas.form',['item'=>new Faculty]);}
    public function store(Request $r){$data=$r->validate(['code'=>'required|max:20|unique:faculties','name'=>'required|max:150','dean_name'=>'nullable|max:150','is_active'=>'boolean']);Faculty::create($data);return redirect()->route('fakultas.index')->with('success','Fakultas berhasil ditambahkan.');}
    public function edit(Faculty $fakultas){return view('fakultas.form',['item'=>$fakultas]);}
    public function update(Request $r,Faculty $fakultas){$data=$r->validate(['code'=>'required|max:20|unique:faculties,code,'.$fakultas->id,'name'=>'required|max:150','dean_name'=>'nullable|max:150','is_active'=>'boolean']);$fakultas->update($data);return redirect()->route('fakultas.index')->with('success','Fakultas berhasil diperbarui.');}
    public function destroy(Faculty $fakultas){$fakultas->delete();return back()->with('success','Fakultas berhasil dihapus.');}
}
