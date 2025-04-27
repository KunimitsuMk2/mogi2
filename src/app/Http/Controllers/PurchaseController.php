<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    // 購入確認画面を表示
    public function confirm(Item $item)
    {
        // 自分の商品は購入できないようにする
        if ($item->seller_id == Auth::id()) {
            return redirect()->route('products.item', $item)
                ->with('error', '自分の出品した商品は購入できません。');
        }
        //すでに売れている商品は購入できないようにす
        if($item->status==='sold'){
            return redirect()->route('products.item',$item)
                                    ->with('error','この商品はすでに購入されています');
        }
        // 認証済みユーザーの情報を取得
        $user = Auth::user();
        
        return view('products.confirm', compact('item','user'));
    }
    
    // 購入処理を実
    public function complete(Request $request, Item $item)
    {
         
    
        // すでに売れている商品は購入できないようにする
         if ($item->status === 'sold') {
            return redirect()->route('products.item', $item)
                        ->with('error', 'この商品はすでに購入されています。');
        }

        $user = Auth::user();
        $paymentMethod = $request->input('payment_method', 'convenience_store');
        
        // 購入情報を保存
        $purchase = new Purchase();
        $purchase->user_id = $user->id;
        $purchase->item_id = $item->id;
        $purchase->price = $item->price;
        $purchase->payment_method = $paymentMethod;
        
        // 配送先住所を保存（各購入に住所を紐づける）
        $purchase->shipping_postal_code = $user->postal_code;
        $purchase->shipping_address = $user->address;
        $purchase->shipping_building_name = $user->building_name;
        
        $purchase->status = 'completed';
        $purchase->purchased_at = now();
        $purchase->save();
        
        // 商品を「購入済み」に更新
        $item->status = 'sold';
        $item->save();
        
        return redirect()->route('products.index')
            ->with('success', '商品の購入が完了しました！');
    }
    
}