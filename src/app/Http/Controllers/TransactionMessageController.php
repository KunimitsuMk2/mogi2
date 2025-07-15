<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionMessage;
use App\Http\Requests\StoreTransactionMessageRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TransactionMessageController extends Controller
{
    /**
     * メッセージ投稿
     */
    public function store(StoreTransactionMessageRequest $request, Transaction $transaction)
    {
        $user = Auth::user();
        
        // 取引参加者かチェック
        if ($transaction->seller_id !== $user->id && $transaction->buyer_id !== $user->id) {
            abort(403, 'この取引にメッセージを送信する権限がありません。');
        }
        
        $imagePath = null;
        
        // 画像がアップロードされている場合の処理
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('transaction_images', 'public');
        }
        
        // メッセージを保存
        $message = TransactionMessage::create([
            'transaction_id' => $transaction->id,
            'user_id' => $user->id,
            'message' => $request->message,
            'image_path' => $imagePath,
        ]);
        
        // 取引の最終更新日時を更新
        $transaction->touch();
        
        return redirect()->route('transactions.show', $transaction)
                        ->with('success', 'メッセージを送信しました。');
    }

    /**
     * メッセージ編集
     */
    public function update(Request $request, TransactionMessage $message)
    {
        $user = Auth::user();
        
        // 投稿者本人かチェック
        if ($message->user_id !== $user->id) {
            abort(403, 'このメッセージを編集する権限がありません。');
        }
        
        // バリデーション
        $request->validate([
            'message' => 'required|string|max:400',
        ], [
            'message.required' => '本文を入力してください',
            'message.max' => '本文は400文字以内で入力してください',
        ]);
        
        // メッセージを更新
        $message->update([
            'message' => $request->message,
        ]);
        
        return redirect()->route('transactions.show', $message->transaction)
                        ->with('success', 'メッセージを更新しました。');
    }

    /**
     * メッセージ削除
     */
    public function destroy(TransactionMessage $message)
    {
        $user = Auth::user();
        
        // 投稿者本人かチェック
        if ($message->user_id !== $user->id) {
            abort(403, 'このメッセージを削除する権限がありません。');
        }
        
        $transaction = $message->transaction;
        
        // 画像がある場合は削除
        if ($message->image_path) {
            Storage::disk('public')->delete($message->image_path);
        }
        
        // メッセージを削除
        $message->delete();
        
        return redirect()->route('transactions.show', $transaction)
                        ->with('success', 'メッセージを削除しました。');
    }
}