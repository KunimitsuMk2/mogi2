<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\UserRating;
use App\Http\Requests\StoreUserRatingRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserRatingController extends Controller
{
    /**
     * 評価投稿
     */
    public function store(StoreUserRatingRequest $request, Transaction $transaction)
    {
        $user = Auth::user();
        
        // デバッグログ追加
        Log::info('Rating submission started', [
            'user_id' => $user->id,
            'transaction_id' => $transaction->id,
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);
        
        // 取引参加者かチェック
        if ($transaction->seller_id !== $user->id && $transaction->buyer_id !== $user->id) {
            Log::error('User not authorized for this transaction', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->id
            ]);
            abort(403, 'この取引に評価を投稿する権限がありません。');
        }
        
        // 取引が完了していない場合はエラー
        if ($transaction->status !== 'completed') {
            Log::error('Transaction not completed', [
                'transaction_id' => $transaction->id,
                'current_status' => $transaction->status
            ]);
            return redirect()->route('transactions.show', $transaction)
                            ->with('error', '取引が完了していません。');
        }
        
        // 評価相手を特定
        $ratedUserId = ($transaction->seller_id === $user->id) 
                      ? $transaction->buyer_id 
                      : $transaction->seller_id;
        
        Log::info('Rating target identified', [
            'rater_id' => $user->id,
            'rated_user_id' => $ratedUserId
        ]);
        
        // すでに評価済みかチェック
        $existingRating = UserRating::where('transaction_id', $transaction->id)
                                  ->where('rater_id', $user->id)
                                  ->first();
        
        if ($existingRating) {
            Log::error('Rating already exists', [
                'existing_rating_id' => $existingRating->id
            ]);
            return redirect()->route('transactions.show', $transaction)
                            ->with('error', 'すでに評価済みです。');
        }
        
        try {
            // 評価を保存
            $rating = UserRating::create([
                'transaction_id' => $transaction->id,
                'rater_id' => $user->id,
                'rated_user_id' => $ratedUserId,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);
            
            Log::info('Rating saved successfully', [
                'rating_id' => $rating->id
            ]);
            
            return redirect()->route('products.index')
                            ->with('success', '評価を送信しました。');
                            
        } catch (\Exception $e) {
            Log::error('Failed to save rating', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('transactions.show', $transaction)
                            ->with('error', '評価の保存に失敗しました。');
        }
    }

    /**
     * 評価一覧表示
     */
    public function index()
    {
        $user = Auth::user();
        
        // 受け取った評価を取得
        $receivedRatings = UserRating::where('rated_user_id', $user->id)
                                   ->with(['rater', 'transaction.item'])
                                   ->orderBy('created_at', 'desc')
                                   ->get();
        
        // 付けた評価を取得
        $givenRatings = UserRating::where('rater_id', $user->id)
                                ->with(['ratedUser', 'transaction.item'])
                                ->orderBy('created_at', 'desc')
                                ->get();
        
        return view('ratings.index', compact('receivedRatings', 'givenRatings'));
    }
}