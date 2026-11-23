<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class ProfileController extends Controller
{
    public function edit(){return view('profile.edit',['user'=>auth()->user()]);}
    public function update(Request $request){
        $data=$request->validate(['name'=>'required|string|max:100','email'=>'required|email|max:150|unique:users,email,'.auth()->id(),'password'=>'nullable|min:8|confirmed']);
        $user=$request->user(); $user->name=$data['name']; $user->email=$data['email'];
        if(!empty($data['password'])) $user->password=Hash::make($data['password']);
        $user->save(); return back()->with('success','Profil berhasil diperbarui.');
    }
}
