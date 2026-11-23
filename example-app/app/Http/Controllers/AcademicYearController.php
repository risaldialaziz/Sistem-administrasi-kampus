<?php
namespace App\Http\Controllers;
use App\Models\AcademicYear; use Illuminate\Http\Request;
class AcademicYearController extends Controller{
 public function index(){return view('tahun-akademik.index',['items'=>AcademicYear::latest()->paginate(10)]);}
 public function create(){return view('tahun-akademik.form',['item'=>new AcademicYear]);}
 public function store(Request $r){$d=$r->validate(['name'=>'required|max:20|unique:academic_years,name','term'=>'required|in:ganjil,genap','is_active'=>'boolean']);if(!empty($d['is_active']))AcademicYear::where('is_active',true)->update(['is_active'=>false]);AcademicYear::create($d);return redirect()->route('tahun-akademik.index')->with('success','Tahun akademik berhasil ditambahkan.');}
 public function edit(AcademicYear $tahunAkademik){return view('tahun-akademik.form',['item'=>$tahunAkademik]);}
 public function update(Request $r,AcademicYear $tahunAkademik){$d=$r->validate(['name'=>'required|max:20|unique:academic_years,name,'.$tahunAkademik->id,'term'=>'required|in:ganjil,genap','is_active'=>'boolean']);if(!empty($d['is_active']))AcademicYear::where('is_active',true)->update(['is_active'=>false]);$tahunAkademik->update($d);return redirect()->route('tahun-akademik.index')->with('success','Tahun akademik berhasil diperbarui.');}
 public function destroy(AcademicYear $tahunAkademik){$tahunAkademik->delete();return back()->with('success','Tahun akademik berhasil dihapus.');}
}
