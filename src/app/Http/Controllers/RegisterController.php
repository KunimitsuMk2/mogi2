<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    //
    public function showRegistrationForm()
    {
        return view('auth.register');
    }
     // 会員登録処理
    public function register(RegisterRequest $request)
    {
    
        // ユーザーを登録
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // ログイン後にリダイレクト
        return redirect()->route('login')->with('success', '登録が完了しました');
    }
}
