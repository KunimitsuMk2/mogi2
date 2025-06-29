@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{asset('css/products/confirm.css')}}">
    <style>
        .konbini-payment {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .konbini-title {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 24px;
        }
        
        .konbini-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .konbini-step {
            margin-bottom: 15px;
            padding: 15px;
            border-left: 4px solid #ff6b6b;
            background-color: #fff5f5;
        }
        
        .payment-details {
            background-color: #e8f5e8;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        
        .back-button {
            text-align: center;
            margin-top: 30px;
        }
        
        .back-link {
            display: inline-block;
            padding: 12px 24px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .back-link:hover {
            background-color: #5a6268;
            color: white;
        }
    </style>
@endsection

@section('content')
<div class="konbini-payment">
    <h1 class="konbini-title">🏪 コンビニ決済手続き完了</h1>
    
    <div class="payment-details">
        <h3>購入商品</h3>
        <p><strong>商品名:</strong> {{ $item->name }}</p>
        <p><strong>金額:</strong> ¥{{ number_format($item->price) }}</p>
        <p><strong>決済ID:</strong> {{ $payment_intent->id }}</p>
    </div>
    
    <div class="konbini-info">
        <h3>📋 お支払い手順</h3>
        
        <div class="konbini-step">
            <strong>STEP 1:</strong> お近くのコンビニエンスストアにお越しください
        </div>
        
        <div class="konbini-step">
            <strong>STEP 2:</strong> 店内の端末で以下の情報を入力してください
            <ul style="margin-top: 10px;">
                <li>決済番号: {{ $payment_intent->id }}</li>
                <li>お支払い金額: ¥{{ number_format($item->price) }}</li>
            </ul>
        </div>
        
        <div class="konbini-step">
            <strong>STEP 3:</strong> レジにてお支払いを完了してください
        </div>
        
        <div class="konbini-step">
            <strong>STEP 4:</strong> お支払い完了後、商品の配送手続きが開始されます
        </div>
    </div>
    
    <div style="background-color: #fff3cd; padding: 15px; border-radius: 4px; border-left: 4px solid #ffc107;">
        <strong>⚠️ 重要な注意事項</strong>
        <ul style="margin-top: 10px;">
            <li>お支払い期限: {{ now()->addDays(3)->format('Y年m月d日') }}</li>
            <li>期限内にお支払いがない場合、注文は自動的にキャンセルされます</li>
            <li>領収書は必ず保管してください</li>
        </ul>
    </div>
    
    <div class="back-button">
        <a href="{{ route('products.index') }}" class="back-link">
            商品一覧に戻る
        </a>
    </div>
</div>
@endsection