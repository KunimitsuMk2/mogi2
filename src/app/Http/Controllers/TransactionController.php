<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * 取引一覧表示
     */
    public function index()
    {
        $user = Auth::user();
        
        // ユーザーの全取引を取得（出品・購入両方）
        $transactions = Transaction::where('seller_id', $user->id)
                                 ->orWhere('buyer_id', $user->id)
                                 ->with(['item', 'messages', 'seller', 'buyer'])
                                 ->orderBy('updated_at', 'desc')
                                 ->get();
        
        return view('transactions.index', compact('transactions'));
    }

    /**
     * 取引詳細表示（チャット画面）
     */
    public function show(Transaction $transaction)
    {
        $user = Auth::user();
        
        // 取引参加者かチェック
        if ($transaction->seller_id !== $user->id && $transaction->buyer_id !== $user->id) {
            abort(403, 'この取引にアクセスする権限がありません。');
        }
        
        // 取引データを詳細読み込み
        $transaction->load(['item', 'messages.user', 'seller', 'buyer']);
        
        // チャット相手を取得
        $chatPartner = $transaction->getChatPartner($user->id);
        
        // 他の取引商品を取得（出品者の場合のみ）
        $otherItems = [];
        if ($transaction->seller_id === $user->id) {
            $otherItems = Item::where('seller_id', $user->id)
                             ->where('id', '!=', $transaction->item_id)
                             ->limit(10)
                             ->get();
        }
        
        // 相手からのメッセージを既読にする
        $transaction->messages()
                   ->where('user_id', '!=', $user->id)
                   ->where('is_read', false)
                   ->update(['is_read' => true]);
        
        return view('transactions.show', compact('transaction', 'chatPartner', 'otherItems'));
    }

    /**
     * 取引完了処理
     */
    public function complete(Transaction $transaction)
    {
        $user = Auth::user();
        
        // 取引参加者かチェック
        if ($transaction->seller_id !== $user->id && $transaction->buyer_id !== $user->id) {
            return response()->json(['success' => false, 'message' => '権限がありません。'], 403);
        }
        
        // 取引ステータスを完了に更新
        $transaction->update(['status' => 'completed']);
        
        return response()->json(['success' => true, 'message' => '取引が完了しました。']);
    }
}