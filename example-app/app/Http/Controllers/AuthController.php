<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{
    public function showLogin(){return view('auth.login');}
    public function login(Request $request){
        $credentials=$request->validate(['email'=>'required|email','password'=>'required|string']);
        $user=User::where('email',$credentials['email'])->first();
        if(!$user || !$user->is_active || !Hash::check($credentials['password'],$user->password)) {
            return back()->withErrors(['email'=>'Email atau password salah, atau akun tidak aktif.'])->onlyInput('email');
        }
        Auth::login($user,$request->boolean('remember'));
        $request->session()->regenerate();
        \App\Models\AuditLog::create([
            'user_id' => $user->id,
            'action' => 'login',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);
        return redirect()->intended(route('dashboard'));
    }
    public function logout(Request $request){Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('login');}
}
