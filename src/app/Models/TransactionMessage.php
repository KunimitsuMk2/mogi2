<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'message',
        'image_path',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    // 取引との関係
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    // ユーザーとの関係
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // メッセージを既読にする
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }
}