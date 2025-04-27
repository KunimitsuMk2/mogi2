@extends('layouts.app')

@section('title', 'メール認証')

@section('content')
<div class="container">
    <h1 class="title">メール認証</h1>
    
    <div class="verify-email">
        <p class="verify-email__text">
            ご登録いただいたメールアドレスに認証リンクを送信しました。<br>
            メール内のリンクをクリックして、メールアドレスの認証を完了してください。
        </p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="button button--primary">
                認証メールを再送信
            </button>
        </form>

        <div class="verify-email__logout">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="button button--secondary">
                    ログアウト
                </button>
            </form>
        </div>
    </div>
</div>
@endsection 