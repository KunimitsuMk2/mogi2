<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'seller_id',
        'buyer_id',
        'status'
    ];

    // 商品との関係
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // 出品者との関係
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    // 購入者との関係
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    // メッセージとの関係
    public function messages()
    {
        return $this->hasMany(TransactionMessage::class)->orderBy('created_at', 'asc');
    }

    // 評価との関係
    public function ratings()
    {
        return $this->hasMany(UserRating::class);
    }

    // 未読メッセージ数を取得（指定ユーザー向け）
    public function getUnreadMessagesCount($userId)
    {
        return $this->messages()
            ->where('user_id', '!=', $userId)
            ->where('is_read', false)
            ->count();
    }

    // 最新メッセージを取得
    public function getLatestMessage()
    {
        return $this->messages()->latest()->first();
    }

    // チャット相手を取得
    public function getChatPartner($currentUserId)
    {
        if ($this->seller_id == $currentUserId) {
            return $this->buyer;
        } else {
            return $this->seller;
        }
    }
}