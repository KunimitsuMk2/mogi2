@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{asset('css/products/confirm.css')}}">
@endsection

@section('content')
<div class="purchase-container">
    <div class="purchase-content">
        <!-- 左側：商品情報 -->
        <div class="purchase-content__left">
            <!-- 商品情報セクション -->
            <div class="purchase-item">
                <div class="purchase-item__image">
                    @if($item->image_url)
                        <img src="{{ asset($item->image_url) }}" alt="{{ $item->name }}">
                    @else
                        <div class="purchase-item__image-placeholder">商品画像</div>
                    @endif
                </div>
                <div class="purchase-item__info">
                    <h1 class="purchase-item__name">{{ $item->name }}</h1>
                    <p class="purchase-item__price">¥{{ number_format($item->price) }}</p>
                </div>
            </div>

            <hr class="purchase-divider">

            <!-- 支払い方法選択（左側） -->
            <div class="purchase-payment">
                <h2 class="purchase-section__title">支払い方法</h2>
                <div class="purchase-payment__select">
                    <select name="payment_method" id="payment-method-select" class="purchase-payment__dropdown">
                        <option value="convenience_store" {{ old('payment_method', 'convenience_store') == 'convenience_store' ? 'selected' : '' }}>
                            コンビニ払い
                        </option>
                        <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>
                            カード支払い
                        </option>
                    </select>
                </div>
                @error('payment_method')
                    <p class="purchase-form__error">{{ $message }}</p>
                @enderror
            </div>

            <hr class="purchase-divider">

            <!-- 配送先情報 -->
            <div class="purchase-shipping">
                <div class="purchase-shipping__header">
                    <h2 class="purchase-section__title">配送先</h2>
                    <a href="{{ route('address.edit',['item_id'=>$item->id]) }}">住所を変更する</a>
                </div>
                <div class="purchase-shipping__info">
                    @if($user->postal_code && $user->address)
                        <p class="purchase-shipping__postal">〒 {{ $user->postal_code }}</p>
                        <p class="purchase-shipping__address">{{ $user->address }}{{ $user->building_name ? " ".$user->building_name : '' }}</p>
                    @else
                        <p class="purchase-shipping__postal">〒 XXX-YYYY</p>
                        <p class="purchase-shipping__address">ここには住所と建物が入ります</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- 右側：注文内容確認 -->
        <div class="purchase-content__right">
            <div class="purchase-summary">
                <div class="purchase-summary__row">
                    <p class="purchase-summary__label">商品代金</p>
                    <p class="purchase-summary__value">¥ {{ number_format($item->price) }}</p>
                </div>
                <div class="purchase-summary__row">
                    <p class="purchase-summary__label">支払い方法</p>
                    <p class="purchase-summary__value" id="payment-method-display">
                        コンビニ払い
                    </p>
                </div>
            </div>

            <form action="{{ route('purchase.complete', $item) }}" method="POST" class="purchase-form">
                @csrf
                <input type="hidden" name="payment_method" id="payment-method-input" value="{{ old('payment_method', 'convenience_store') }}">
                <button type="submit" class="purchase-form__button">購入する</button>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript for instant update -->
<script>
    // DOMが読み込まれたあとに実行
    document.addEventListener('DOMContentLoaded', function() {
        // 支払い方法の選択要素
        const paymentMethodSelect = document.getElementById('payment-method-select');
        const paymentMethodDisplay = document.getElementById('payment-method-display');
        const paymentMethodInput = document.getElementById('payment-method-input');
        
        // 支払い方法変更時の処理
        paymentMethodSelect.addEventListener('change', function() {
            const selectedMethod = this.value;
            paymentMethodInput.value = selectedMethod;
            
            // 表示テキストを更新
            if (selectedMethod === 'credit_card') {
                paymentMethodDisplay.textContent = 'カード支払い';
            } else {
                paymentMethodDisplay.textContent = 'コンビニ払い';
            }
        });
        
        // 初期状態での支払い方法表示を設定
        if (paymentMethodSelect.value === 'credit_card') {
            paymentMethodDisplay.textContent = 'カード支払い';
        } else {
            paymentMethodDisplay.textContent = 'コンビニ払い';
        }
    });
</script>
@endsection