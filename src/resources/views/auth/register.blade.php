@extends('layouts.app')

@section('title', '会員登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
<div class="register-form__content">
    <h1 class="register-form__heading">会員登録</h1>

    <form method="POST" action="{{ route('register') }}" class="form">
        @csrf

        <div class="form__group">
            <div class="form__group-title">
                <label for="name">名前</label>
            </div>
            <div class="form__input--text">
                <input type="text" name="name" id="name" value="{{ old('name') }}">
            </div>
            @error('name')
                <p class="form__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form__group">
            <div class="form__group-title">
                <label for="email">メールアドレス</label>
            </div>
            <div class="form__input--text">
                <input type="email" name="email" id="email" value="{{ old('email') }}">
            </div>
            @error('email')
                <p class="form__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form__group">
            <div class="form__group-title">
                <label for="password">パスワード</label>
            </div>
            <div class="form__input--text">
                <input type="password" name="password" id="password">
            </div>
            @error('password')
                <p class="form__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form__group">
            <div class="form__group-title">
                <label for="password_confirmation">パスワード（確認）</label>
            </div>
            <div class="form__input--text">
                <input type="password" name="password_confirmation" id="password_confirmation">
            </div>
            @error('password_confirmation')
                <p class="form__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form__button">
            <button type="submit" class="form__button-submit">登録する</button>
        </div>
    </form>

    <div class="login__link">
        <a href="{{ route('login') }}">ログインはこちら</a>
    </div>
</div>
@endsection