@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{asset('css/products/item.css')}}">
@endsection
@section('content')
<div class="item-detail-container">
    <div class="item-detail">
        <!-- 商品画像 -->
        <div class="item-image">
            @if($item->image_url)
                <img src="{{ asset($item->image_url) }}" alt="{{ $item->name }}">
            @else
                <div class="no-image">商品画像</div>
            @endif
        </div>

        <!-- 商品情報 -->
        <div class="item-info">
            <!-- 商品名 -->
            <h1 class="item-name">{{ $item->name }}</h1>
            
            <!-- ブランド名 -->
            <p class="item-brand">{{ $item->brand ?? 'ブランド名' }}</p>
            
            <!-- 価格 -->
            <p class="item-price">¥{{ number_format($item->price) }} <span class="tax">(税込)</span></p>
            
<!-- お気に入り・メッセージアイコン -->
<div class="item-actions">
    <div class="action-button favorite">
        @if(Auth::check())
            <form action="{{ route('favorites.toggle', $item) }}" method="POST" class="favorite-form">
                @csrf
                <button type="submit" class="favorite-button {{ Auth::user()->favoritedItems->contains($item->id) ? 'favorite-active' : '' }}">
                    ★
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" class="favorite-button">
                ★
            </a>
        @endif
        <div class="action-count">{{ $item->favorites->count() }}</div>
    </div>
    <div class="action-button message">
        <div class="message-icon">
            💬
        </div>
        <div class="action-count">{{ $item->comments->count() }}</div>
    </div>
</div>
            
            <!-- 購入ボタン -->
            @if(Auth::check() && Auth::id() != $item->seller_id)
               <form action="{{ route('products.confirm', $item) }}" method="GET">
                    <button type="submit" class="purchase-button">購入手続きへ</button>
            </form>
            @elseif(!Auth::check())
                <a href="{{ route('login') }}" class="purchase-button">ログインして購入</a>
            @endif
            
            <!-- 商品説明 -->
            <div class="item-description-section">
                <h2>商品説明</h2>
                <div class="item-description">
                    <p>{{ $item->description }}</p>
                    
                    <!-- 商品の詳細情報 -->
                    @if($item->condition)
                        @if($item->color)
                        <p>カラー：{{ $item->color}}</p>
                        @endif
                        <p>{{ $item->conditionName}}</p>
                        <p>商品の状態は1つだけです。値札ありません。</p>
                        <p>購入後、返品はしません。</p>
                    @endif
                </div>
            </div>
            
            <!-- 商品情報 -->
            <div class="item-details-section">
                <h2>商品の情報</h2>
                <table class="item-details-table">
                    <tr>
                        <th>カテゴリー</th>
                        <td>
                            @foreach ($item->categories as $category)
                                <span class="tag">{{ $category->name }}</span>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th>商品の状態</th>
                        <td>{{ $item->conditionName }}</td>
                    </tr>
                </table>
            </div>
            
           <!-- コメント一覧 -->
        <div class="comment-section">
            <h2>コメント({{ $item->comments->count() ?? 0 }})</h2>
    
            @if(isset($item->comments) && $item->comments->count() > 0)
                 @foreach($item->comments as $comment)
                    <div class="comment">
                        <div class="comment-user">
                             <div class="user-avatar">
                                @if(isset($comment->user) && $comment->user && isset($comment->user->avatar) && $comment->user->avatar)
                                    <img src="{{ asset('storage/'.$comment->user->avatar) }}" alt="{{ $comment->user->name }}">
                                @else
                                    <div class="avatar-placeholder"></div>
                                @endif
                            </div>
                            <span class="user-name">{{ isset($comment->user) && $comment->user ? $comment->user->name : '不明なユーザー'}}</span>
                        </div>
                        <div class="comment-content">
                            <p>{{ $comment->content }}</p>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="comment-empty">
                    <p>コメントはまだありません。</p>
                </div>
            @endif
    
    <!-- コメント投稿フォーム -->
            @if(Auth::check())
                <form action="{{ route('comments.store', $item) }}" method="POST" class="comment-form">
                    @csrf
                    <h3>商品へのコメント</h3>
                    <textarea name="content" rows="5" placeholder="コメントを入力してください" required>{{ old('content') }}</textarea>
                    @error('content')
                        <p class="form__error">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="comment-submit">コメントを送信する</button>
                </form>
            @else
                <div class="login-to-comment">
                    <a href="{{ route('login') }}">ログインしてコメントする</a>
                </div>
             @endif
                </div>
        </div>
    </div>
</div>
@endsection
<!-- ページの最後に追加 -->
 @section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // お気に入りフォームを取得
        const favoriteForm = document.querySelector('.favorite-form');

        if (favoriteForm) {
            favoriteForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // フォームデータ取得
                const formData = new FormData(this);

                // APIリクエスト
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // カウント更新
                        const favoriteCountElement = this.closest('.action-button.favorite').querySelector('.action-count');
                        if (favoriteCountElement) {
                            favoriteCountElement.textContent = data.count;
                        }

                        // ボタンのクラス切り替え
                        const favoriteButton = this.querySelector('.favorite-button');
                        favoriteButton.classList.toggle('favorite-active');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        }
    });
</script>
@endsection