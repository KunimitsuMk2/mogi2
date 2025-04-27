@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/products/index.css') }}">
@endsection

@section('content')
<div class="tab-container">
    <div class="tab-navigation">
        <a href="{{ route('products.index', ['tab' => 'recommended', 'search' => request('search')]) }}"
            class="tab-navigation__item {{ $tab === 'recommended' || !$tab ? 'tab-navigation__item--active' : '' }}">
            おすすめ
        </a>
        <a href="{{ route('products.index', ['tab' => 'mylist', 'search' => request('search')]) }}"
            class="tab-navigation__item {{ $tab === 'mylist' ? 'tab-navigation__item--active' : '' }}">
            マイリスト
        </a>
    </div>
</div>

<!-- 商品一覧 -->
<main class="items-container">
    <div class="items-grid">
        @foreach ($items as $item)
            <a href="{{ route('products.item', ['item'=>$item->id]) }}" class="item-card-link">
                <div class="item-card">
                    <div class="item-image-container">
                        @if($item->image_url)
                            <img src="{{ $item->image_url }}"
                                  alt="{{ $item->name }}"
                                  class="item-image">
                        @else
                            <div class="no-image">
                                商品画像
                            </div>
                        @endif
                        
                        @if($item->status === 'sold')
                            <div class="sold-overlay">
                                SOLD
                            </div>
                        @endif
                    </div>
                    <h3 class="item-name">{{ $item->name }}</h3>
                    <p class="item-price">¥{{ number_format($item->price) }}</p>
                </div>
            </a>
        @endforeach
    </div>
</main>

<!-- ページネーション -->
@if($items->hasPages())
    <div class="pagination">
        {{ $items->links() }}
    </div>
@endif
@endsection