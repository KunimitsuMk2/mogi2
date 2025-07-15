@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/transactions/show.css') }}">
@endsection

@section('content')
<div class="transaction-chat">
    <!-- サイドバー -->
    <div class="transaction-sidebar">
        <h3 class="sidebar-title">その他の取引</h3>
        <div class="sidebar-items">
            <!-- 出品者の場合のみ他の商品を表示 -->
            @if(Auth::id() === $transaction->seller_id)
                @foreach($otherItems ?? [] as $item)
                    <div class="sidebar-item">
                        <div class="sidebar-item-image">
                            @if($item->image_url)
                                <img src="{{ asset($item->image_url) }}" alt="{{ $item->name }}">
                            @else
                                <div class="item-placeholder">商品画像</div>
                            @endif
                        </div>
                        <div class="sidebar-item-name">{{ $item->name }}</div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- メインコンテンツ -->
    <div class="transaction-main">
        <!-- ヘッダー -->
        <div class="transaction-header">
            <div class="chat-partner">
                <div class="partner-avatar">
                    @if($chatPartner->avatar)
                        <img src="{{ asset('storage/'.$chatPartner->avatar) }}" alt="{{ $chatPartner->name }}">
                    @else
                        <div class="avatar-placeholder"></div>
                    @endif
                </div>
                <span class="partner-name">「{{ $chatPartner->name }}」さんとの取引画面</span>
            </div>
            <button class="complete-transaction-btn" onclick="completeTransaction({{ $transaction->id }})">
                取引を完了する
            </button>
        </div>

        <!-- 商品情報 -->
        <div class="product-info">
            <div class="product-image">
                @if($transaction->item->image_url)
                    <img src="{{ asset($transaction->item->image_url) }}" alt="{{ $transaction->item->name }}">
                @else
                    <div class="product-placeholder">商品画像</div>
                @endif
            </div>
            <div class="product-details">
                <h2 class="product-name">{{ $transaction->item->name }}</h2>
                <p class="product-price">¥{{ number_format($transaction->item->price) }}</p>
            </div>
        </div>

        <!-- チャットエリア -->
        <div class="chat-area">
            @foreach($transaction->messages ?? [] as $message)
                <div class="message {{ $message->user_id === Auth::id() ? 'message-own' : 'message-other' }}">
                    @if($message->user_id !== Auth::id())
                        <div class="message-avatar">
                            @if($message->user->avatar)
                                <img src="{{ asset('storage/'.$message->user->avatar) }}" alt="{{ $message->user->name }}">
                            @else
                                <div class="avatar-placeholder"></div>
                            @endif
                        </div>
                        <div class="message-info">
                            <div class="message-user">{{ $message->user->name }}</div>
                            <div class="message-content">
                                <div class="message-bubble">
                                    {{ $message->message }}
                                    @if($message->image_path)
                                        <img src="{{ asset('storage/'.$message->image_path) }}" alt="添付画像" class="message-image">
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="message-info">
                            <div class="message-user">{{ $message->user->name }}</div>
                            <div class="message-content">
                                <div class="message-bubble">
                                    {{ $message->message }}
                                    @if($message->image_path)
                                        <img src="{{ asset('storage/'.$message->image_path) }}" alt="添付画像" class="message-image">
                                    @endif
                                    <div class="message-actions">
                                        <button class="message-edit">編集</button>
                                        <button class="message-delete">削除</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="message-avatar">
                            @if($message->user->avatar)
                                <img src="{{ asset('storage/'.$message->user->avatar) }}" alt="{{ $message->user->name }}">
                            @else
                                <div class="avatar-placeholder"></div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- メッセージ入力フォーム -->
        <form action="{{ route('transaction.messages.store', $transaction) }}" method="POST" enctype="multipart/form-data" class="message-form">
            @csrf
            <div class="form-group">
                <input type="text" name="message" placeholder="取引メッセージを記入してください" class="message-input" required>
                <label for="image-upload" class="image-upload-btn">画像を追加</label>
                <input type="file" name="image" id="image-upload" accept="image/*" style="display: none;">
                <button type="submit" class="send-btn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2 21L23 12L2 3V10L17 12L2 14V21Z" fill="currentColor"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 取引完了モーダル -->
<div id="completeModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>取引が完了しました。</h3>
        </div>
        <form action="{{ route('user.rating.store', $transaction) }}" method="POST" class="rating-form">
            @csrf
            <div class="rating-section">
                <p>今回の取引相手はどうでしたか？</p>
                <div class="star-rating">
                    <label for="star1" data-rating="1">★</label>
                    <input type="radio" name="rating" value="1" id="star1">
                    <label for="star2" data-rating="2">★</label>
                    <input type="radio" name="rating" value="2" id="star2">
                    <label for="star3" data-rating="3">★</label>
                    <input type="radio" name="rating" value="3" id="star3">
                    <label for="star4" data-rating="4">★</label>
                    <input type="radio" name="rating" value="4" id="star4">
                    <label for="star5" data-rating="5">★</label>
                    <input type="radio" name="rating" value="5" id="star5">
                </div>
            </div>
            <button type="submit" class="submit-rating-btn">送信する</button>
        </form>
    </div>
</div>

<script>
// 取引完了処理
function completeTransaction(transactionId) {
    if (!confirm('取引を完了しますか？')) {
        return;
    }
    
    // 取引完了のPOSTリクエストを送信
    fetch(`/transactions/${transactionId}/complete`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 取引完了後にモーダルを表示
            openCompleteModal();
        } else {
            alert('取引完了処理でエラーが発生しました。');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('取引完了処理でエラーが発生しました。');
    });
}

function openCompleteModal() {
    document.getElementById('completeModal').style.display = 'block';
}

// モーダル外クリックで閉じる
window.onclick = function(event) {
    const modal = document.getElementById('completeModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}

// 星評価の処理
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.star-rating label').forEach(label => {
        label.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const rating = parseInt(this.getAttribute('data-rating'));
            console.log('Star clicked:', rating); // デバッグ用
            
            const radioInput = document.getElementById(`star${rating}`);
            
            if (radioInput) {
                // ラジオボタンを選択
                radioInput.checked = true;
                
                // 星の表示を更新
                updateStarDisplay(rating);
            }
        });
    });
});

function updateStarDisplay(selectedRating) {
    console.log('Updating star display:', selectedRating); // デバッグ用
    const labels = document.querySelectorAll('.star-rating label');
    
    labels.forEach((label, index) => {
        const starNumber = parseInt(label.getAttribute('data-rating'));
        if (starNumber <= selectedRating) {
            label.classList.add('active');
        } else {
            label.classList.remove('active');
        }
    });
}
</script>
@endsection