<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
// ★ 追加：取引モデル
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Checkout\Session;
use Stripe\Exception\CardException;

class PurchaseController extends Controller
{
    public function __construct()
    {
        // Stripe Secret Key を設定
        Stripe::setApiKey(config('stripe.secret_key'));
    }

    // 購入確認画面を表示
    public function confirm(Item $item)
    {
        // 自分の商品は購入できないようにする
        if ($item->seller_id == Auth::id()) {
            return redirect()->route('products.item', $item)
                ->with('error', '自分の出品した商品は購入できません。');
        }
        
        // すでに売れている商品は購入できないようにする
        if($item->status === 'sold'){
            return redirect()->route('products.item',$item)
                ->with('error','この商品はすでに購入されています');
        }
        
        // 認証済みユーザーの情報を取得
        $user = Auth::user();
        
        // Stripe Public Key をビューに渡す
        $stripePublicKey = config('stripe.public_key');
        
        return view('products.confirm', compact('item', 'user', 'stripePublicKey'));
    }
    
    // 購入処理を実行
    public function complete(Request $request, Item $item)
    {
        Log::info('Purchase complete method called', [
            'item_id' => $item->id,
            'payment_method' => $request->input('payment_method'),
            'user_id' => Auth::id()
        ]);

        try {
            // すでに売れている商品は購入できないようにする
            if ($item->status === 'sold') {
                Log::warning('Attempted to purchase sold item', ['item_id' => $item->id]);
                return redirect()->route('products.item', $item)
                    ->with('error', 'この商品はすでに購入されています。');
            }

            $user = Auth::user();
            $paymentMethod = $request->input('payment_method', 'convenience_store');
            
            Log::info('Payment method determined', ['payment_method' => $paymentMethod]);
            
            if ($paymentMethod === 'credit_card') {
                Log::info('Processing credit card payment via Stripe Checkout');
                return $this->processStripeCheckout($request, $item, $user);
            } elseif ($paymentMethod === 'convenience_store') {
                Log::info('Processing konbini payment');
                return $this->processStripeKonbiniPayment($request, $item, $user);
            }
            
            Log::error('Invalid payment method', ['payment_method' => $paymentMethod]);
            return redirect()->back()->with('error', '無効な支払い方法です。');
            
        } catch (\Exception $e) {
            Log::error('Purchase error: ' . $e->getMessage(), [
                'item_id' => $item->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', '決済処理でエラーが発生しました: ' . $e->getMessage());
        }
    }
    
    /**
     * Stripe Checkout決済処理（決済画面に遷移）
     */
    private function processStripeCheckout(Request $request, Item $item, $user)
    {
        try {
            Log::info('Creating Stripe Checkout session', [
                'item_id' => $item->id,
                'user_id' => $user->id,
                'amount' => $item->price
            ]);

            // ルート名を使用して確実にURLを生成
            try {
                $successUrl = route('purchase.checkout.success', $item) . '?session_id={CHECKOUT_SESSION_ID}';
                $cancelUrl = route('products.confirm', $item);
            } catch (\Exception $e) {
                Log::error('Route generation error: ' . $e->getMessage());
                // フォールバック: 絶対URLを使用
                $baseUrl = config('app.url', 'http://localhost');
                $successUrl = $baseUrl . '/purchase/' . $item->id . '/checkout/success?session_id={CHECKOUT_SESSION_ID}';
                $cancelUrl = $baseUrl . '/purchase/' . $item->id;
            }

            Log::info('URLs for Stripe Checkout', [
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl
            ]);

            // Stripe Checkoutセッションを作成
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'jpy',
                            'product_data' => [
                                'name' => $item->name,
                                'description' => $item->description,
                            ],
                            'unit_amount' => $item->price,
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => [
                    'item_id' => $item->id,
                    'user_id' => $user->id,
                ],
            ]);
            
            Log::info('Stripe Checkout session created successfully', [
                'session_id' => $session->id,
                'checkout_url' => $session->url
            ]);
            
            // Stripeの決済画面にリダイレクト
            return redirect($session->url);
            
        } catch (\Exception $e) {
            Log::error('Stripe Checkout error: ' . $e->getMessage(), [
                'item_id' => $item->id,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'カード決済処理エラー: ' . $e->getMessage());
        }
    }
    
    /**
     * Stripeコンビニ決済処理（元の方式）
     */
    private function processStripeKonbiniPayment(Request $request, Item $item, $user)
    {
        try {
            Log::info('Creating konbini payment intent', [
                'item_id' => $item->id,
                'user_id' => $user->id
            ]);

            // コンビニ決済用PaymentIntent を作成
            $paymentIntent = PaymentIntent::create([
                'amount' => $item->price,
                'currency' => 'jpy',
                'payment_method_types' => ['konbini'],
                'payment_method_data' => [
                    'type' => 'konbini',
                    'billing_details' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'address' => [
                            'postal_code' => $user->postal_code,
                            'line1' => $user->address ?? '住所未設定',
                            'line2' => $user->building_name,
                            'country' => 'JP',
                        ],
                    ],
                ],
                'confirmation_method' => 'manual',
                'confirm' => true,
            ]);
            
            Log::info('Konbini payment intent created', ['payment_intent_id' => $paymentIntent->id]);

            // 購入記録を保存
            $this->savePurchaseRecord($item, $user, 'convenience_store', $paymentIntent->id);
            
            // コンビニ決済画面を表示
            return view('products.konbini-payment', [
                'item' => $item,
                'payment_intent' => $paymentIntent,
                'konbini_data' => $paymentIntent->next_action->konbini_display_details ?? null
            ]);
            
        } catch (\Exception $e) {
            Log::error('Konbini payment error: ' . $e->getMessage(), [
                'item_id' => $item->id,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'コンビニ決済エラー: ' . $e->getMessage());
        }
    }
    
    /**
     * 購入記録を保存
     * ★ 修正：取引レコード作成を追加
     */
    private function savePurchaseRecord(Item $item, $user, $paymentMethod, $stripePaymentId = null)
    {
        Log::info('Saving purchase record', [
            'item_id' => $item->id,
            'user_id' => $user->id,
            'payment_method' => $paymentMethod,
            'stripe_payment_id' => $stripePaymentId
        ]);

        $purchase = new Purchase();
        $purchase->user_id = $user->id;
        $purchase->item_id = $item->id;
        $purchase->price = $item->price;
        $purchase->payment_method = $paymentMethod;
        
        if ($stripePaymentId) {
            $purchase->stripe_payment_id = $stripePaymentId;
        }
        
        $purchase->shipping_postal_code = $user->postal_code;
        $purchase->shipping_address = $user->address ?? '住所未設定';
        $purchase->shipping_building_name = $user->building_name;
        $purchase->status = 'completed';
        $purchase->purchased_at = now();
        $purchase->save();
        
        // 商品を「購入済み」に更新
        $item->status = 'sold';
        $item->save();

        // ★ 追加：取引レコードを作成
        $transaction = Transaction::create([
            'item_id' => $item->id,
            'seller_id' => $item->seller_id,
            'buyer_id' => $user->id,
            'status' => 'in_progress'
        ]);

        Log::info('Purchase record and transaction created successfully', [
            'purchase_id' => $purchase->id,
            'transaction_id' => $transaction->id
        ]);
    }
    
    /**
     * Stripe Checkout成功後の処理
     */
    public function checkoutSuccess(Request $request, Item $item)
    {
        Log::info('Checkout success callback', [
            'item_id' => $item->id,
            'session_id' => $request->get('session_id')
        ]);

        try {
            $sessionId = $request->get('session_id');
            
            if (!$sessionId) {
                Log::error('No session_id provided in checkout success');
                return redirect()->route('products.index')
                    ->with('error', 'セッション情報が見つかりません。');
            }
            
            // Stripe Checkoutセッションを取得
            $session = Session::retrieve($sessionId);
            
            Log::info('Retrieved Stripe session', [
                'session_id' => $session->id,
                'payment_status' => $session->payment_status
            ]);
            
            if ($session->payment_status === 'paid') {
                $user = Auth::user();
                $paymentMethod = 'credit_card'; // Checkoutはカード決済
                
                // 購入記録を保存
                $this->savePurchaseRecord($item, $user, $paymentMethod, $session->payment_intent);
                
                return redirect()->route('products.index')
                    ->with('success', 'カード決済が完了しました！');
            }
            
            Log::warning('Payment not completed', ['payment_status' => $session->payment_status]);
            return redirect()->route('products.confirm', $item)
                ->with('error', '決済が完了していません。');
                
        } catch (\Exception $e) {
            Log::error('Checkout success error: ' . $e->getMessage(), [
                'item_id' => $item->id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('products.index')
                ->with('error', '決済処理でエラーが発生しました。');
        }
    }
    
    /**
     * 決済成功後のリダイレクト先
     */
    public function success(Item $item)
    {
        return redirect()->route('products.index')
            ->with('success', '決済が完了しました！');
    }
}