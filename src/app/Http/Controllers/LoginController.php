<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * ログイン処理
     */
    public function login(LoginRequest $request)
    {
        // 認証を試みる
        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            // 認証成功時にセッションを再生成
            $request->session()->regenerate();

            // 管理画面にリダイレクト
            return redirect()->intended('/');
        }

        
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ])->onlyInput('email');
    }

    /**
     * ログアウト処理
     */
    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}