<?php
namespace App\Http\Controllers;
use App\Models\User; use Illuminate\Http\Request; use Illuminate\Support\Facades\Hash;
class UserController extends Controller{
 public function index(Request $r){$items=User::when($r->search,fn($q)=>$q->where('name','like','%'.$r->search.'%')->orWhere('email','like','%'.$r->search.'%'))->when($r->role,fn($q)=>$q->where('role',$r->role))->paginate(10)->withQueryString();return view('users.index',compact('items')); }
 public function create(){return view('users.form',['item'=>new User]);}
 public function store(Request $r){$d=$r->validate(['name'=>'required|max:100','email'=>'required|email|unique:users,email','password'=>'required|min:8','role'=>'required|in:super_admin,admin_kampus,admin_akademik,admin_keuangan,dosen,mahasiswa','is_active'=>'boolean']);$d['password']=Hash::make($d['password']);User::create($d);return redirect()->route('users.index')->with('success','Pengguna berhasil ditambahkan.');}
 public function edit(User $user){return view('users.form',['item'=>$user]);}
 public function update(Request $r,User $user){$d=$r->validate(['name'=>'required|max:100','email'=>'required|email|unique:users,email,'.$user->id,'password'=>'nullable|min:8','role'=>'required|in:super_admin,admin_kampus,admin_akademik,admin_keuangan,dosen,mahasiswa','is_active'=>'boolean']);if(!empty($d['password']))$d['password']=Hash::make($d['password']);else unset($d['password']);$user->update($d);return redirect()->route('users.index')->with('success','Pengguna berhasil diperbarui.');}
 public function destroy(User $user){abort_if($user->id===auth()->id(),403,'Anda tidak dapat menghapus akun sendiri.');$user->delete();return back()->with('success','Pengguna berhasil dihapus.');}
}
