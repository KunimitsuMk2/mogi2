@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/mypage.css') }}">
@endsection

@section('content')
<div class="mypage">

    <!-- プロフィール部分 -->
    <div class="mypage__profile">
        <div class="mypage__profile-image">
            @if($user && $user->avatar)
                <img src="{{asset('storage/'.$user->avatar) }}" alt="{{ $user->name }}" class="mypage__avatar">
            @else
                <div class="mypage__avatar-placeholder"></div>
            @endif
        </div>
        
        <div class="mypage__profile-info">
            <h1 class="mypage__username">{{ $user->name }}</h1>
            <a href="{{ route('mypage.profile.edit') }}" class="mypage__edit-button">プロフィールを編集</a>
        </div>
    </div>

    <!-- タブナビゲーション -->
    <div class="mypage__tabs">
        <a href="{{ route('mypage', ['tab' => 'selling']) }}" class="mypage__tab {{ $activeTab == 'selling' ? 'mypage__tab--active' : '' }}">出品した商品</a>
        <a href="{{ route('mypage', ['tab' => 'purchased']) }}" class="mypage__tab {{ $activeTab == 'purchased' ? 'mypage__tab--active' : '' }}">購入した商品</a> 
    </div>

    <!-- 商品一覧 -->
    <div class="mypage__items">
        <div class="mypage__items-grid">
            @if($activeTab == 'selling')
                @if(count($sellingItems) > 0)
                    @foreach($sellingItems as $item)
                        <div class="mypage__item">
                            <a href="" class="mypage__item-link">
                                <div class="mypage__item-image">
                                    @if($item->image_url)
                                        <img src="{{ asset($item->image_url) }}" alt="{{ $item->name }}" class="mypage__item-img">
                                    @else
                                        <div class="mypage__item-placeholder">商品画像</div>
                                    @endif
                                </div>
                                <div class="mypage__item-info">
                                    <p class="mypage__item-name">{{ $item->name }}</p>
                                </div>
                            </a>
                        </div>
                    @endforeach
                @else
                    <div class="mypage__empty">
                        <p>出品した商品はありません</p>
                    </div>
                @endif
            @else
                @if(count($purchasedItems  ?? []) > 0)
                    @foreach($purchasedItems as $item)
                        <div class="mypage__item">
                            <a href="{{ route('products.item', $item->id) }}" class="mypage__item-link">
                                <div class="mypage__item-image">
                                    @if($item->image_url)
                                        <img src="{{ asset($item->image_url) }}" alt="{{ $item->name }}" class="mypage__item-img">
                                    @else
                                        <div class="mypage__item-placeholder">商品画像</div>
                                    @endif
                                </div>
                                <div class="mypage__item-info">
                                    <p class="mypage__item-name">{{ $item->name }}</p>
                                </div>
                            </a>
                        </div>
                    @endforeach
                @else
                    <div class="mypage__empty">
                        <p>購入した商品はありません</p>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection