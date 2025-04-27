@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/profile.css') }}">
@endsection

@section('content')
<div class="profile-edit">
    <h1 class="profile-edit__title">プロフィール設定</h1>
    
    <form action="{{ route('mypage.profile.update') }}" method="POST" enctype="multipart/form-data" class="profile-edit__form">
        @csrf
        @method('PUT')
        
             <!-- バリデーションエラー表示 -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <!-- プロフィール画像 -->
        <div class="profile-edit__image">
            <div class="profile-edit__image-preview">
                @if($user->avatar)
                    <img class="profile-edit__image-content" src="{{asset('storage/'.$user->avatar) }}" alt="{{ $user->name }}">
                @else
                    <div class="profile-edit__image-placeholder">
                        <span class="profile-edit__image-icon">ユーザー</span>
                    </div>
                @endif
            </div>
            
            <div class="profile-edit__image-upload">
                <label for="avatar-upload" class="profile-edit__image-label">画像を選択する</label>
                <input type="file" name="avatar" id="avatar-upload" class="profile-edit__image-input" accept="image/*">
                @error('avatar')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>
        
        <!-- ユーザー名 -->
        <div class="profile-edit__field">
            <label for="name" class="profile-edit__label">ユーザー名</label>
            <input type="text" name="name" id="name" value="{{ $user->name }}" class="profile-edit__input @error('name') profile-edit__input--error @enderror" required>
            @error('name')
                <span class="profile-edit__error">{{ $message }}</span>
            @enderror
        </div>
        
        <!-- 郵便番号 -->
        <div class="profile-edit__field">
            <label for="postal_code" class="profile-edit__label">郵便番号</label>
            <input type="text" name="postal_code" id="postal_code" value="{{ $user->postal_code }}" class="profile-edit__input @error('postal_code') profile-edit__input--error @enderror" placeholder="例：1234567">
            @error('postal_code')
                <span class="profile-edit__error">{{ $message }}</span>
            @enderror
        </div>
        
        <!-- 住所 -->
        <div class="profile-edit__field">
            <label for="address" class="profile-edit__label">住所</label>
            <input type="text" name="address" id="address" value="{{ $user->address }}" class="profile-edit__input @error('address') profile-edit__input--error @enderror">
            @error('address')
                <span class="profile-edit__error">{{ $message }}</span>
            @enderror
        </div>
        
        <!-- 建物名 -->
        <div class="profile-edit__field">
            <label for="building_name" class="profile-edit__label">建物名</label>
            <input type="text" name="building_name" id="building_name" value="{{ $user->building_name }}" class="profile-edit__input @error('building_name') profile-edit__input--error @enderror">
            @error('building_name')
                <span class="profile-edit__error">{{ $message }}</span>
            @enderror
        </div>
        
        <!-- 更新ボタン -->
        <div class="profile-edit__action">
            <button type="submit" class="profile-edit__button">更新する</button>
        </div>
    </form>
</div>
@endsection