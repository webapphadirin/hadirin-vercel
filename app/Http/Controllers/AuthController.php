<?php
namespace App\Http\Controllers;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller {
    public function form() { return view('auth.login'); }
    public function login(Request $request) {
        $credentials=$request->validate(['email'=>['required','email'],'password'=>['required','string']]);
        if (!Auth::attempt($credentials+['active'=>true])) return back()->withErrors(['email'=>'Email atau kata sandi tidak sesuai.'])->onlyInput('email');
        $request->session()->regenerate(); ActivityLog::create(['user_id'=>Auth::id(),'action'=>'login']);
        return redirect()->intended(Auth::user()->role==='admin'?route('admin.dashboard'):route('dashboard'));
    }
    public function logout(Request $request) { ActivityLog::create(['user_id'=>Auth::id(),'action'=>'logout']); Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('login'); }
}
