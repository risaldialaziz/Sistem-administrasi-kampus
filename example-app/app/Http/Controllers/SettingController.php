<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class SettingController extends Controller{
 public function edit(){abort_unless(in_array(auth()->user()->role,['super_admin','admin_kampus']),403);$settings=DB::table('settings')->pluck('value','key');return view('pengaturan.index',compact('settings')); }
 public function update(Request $r){abort_unless(in_array(auth()->user()->role,['super_admin','admin_kampus']),403);$d=$r->validate(['campus_name'=>'required|max:150','campus_address'=>'nullable|max:300','campus_phone'=>'nullable|max:30','campus_email'=>'nullable|email']);foreach($d as $key=>$value){DB::table('settings')->updateOrInsert(['key'=>$key],['value'=>$value,'updated_at'=>now(),'created_at'=>now()]);}return back()->with('success','Pengaturan berhasil disimpan.');}
}
