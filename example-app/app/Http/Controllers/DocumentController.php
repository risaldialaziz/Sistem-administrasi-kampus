<?php
namespace App\Http\Controllers;
use App\Models\{Document,Student}; use Illuminate\Http\Request; use Illuminate\Support\Facades\Storage;
class DocumentController extends Controller{
 public function index(Request $r){$items=Document::with('student')->latest()->paginate(10)->withQueryString();return view('surat.index',['items'=>$items,'students'=>Student::where('status','aktif')->get()]);}
 public function store(Request $r){$d=$r->validate(['student_id'=>'nullable|exists:students,id','type'=>'required|max:50','number'=>'nullable|max:100','title'=>'required|max:150','status'=>'required|in:draft,diproses,siap,diterbitkan,ditolak','file'=>'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048']);if($r->hasFile('file')){$d['file_path']=$r->file('file')->store('documents','public');}unset($d['file']);$d['created_by']=auth()->id();Document::create($d);return back()->with('success','Surat berhasil dibuat.');}
 public function status(Request $r,Document $document){$d=$r->validate(['status'=>'required|in:draft,diproses,siap,diterbitkan,ditolak']);$document->update($d);return back()->with('success','Status surat diperbarui.');}
 public function destroy(Document $document){if($document->file_path)Storage::disk('public')->delete($document->file_path);$document->delete();return back()->with('success','Surat berhasil dihapus.');}
}
