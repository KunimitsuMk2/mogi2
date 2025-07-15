<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'rater_id',
        'rated_user_id',
        'rating',
        'comment'
    ];

    // 取引との関係
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    // 評価者との関係
    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    // 被評価者との関係
    public function ratedUser()
    {
        return $this->belongsTo(User::class, 'rated_user_id');
    }
}