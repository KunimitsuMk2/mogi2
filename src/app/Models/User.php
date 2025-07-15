<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'postal_code',
        'address',
        'building_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
   // User モデルに追加するメソッド
    public function sellingProducts() {
    return $this->hasMany(Item::class, 'seller_id');
    }
    public function comments()
    {
    return $this->hasMany(Comment::class);
    }
    
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedItems()
    {
    return $this->belongsToMany(Item::class, 'favorites', 'user_id', 'item_id');
    }
    // 既存のUser.phpのクラス内に以下のメソッドを追加

    // 出品者としての取引
    public function sellingTransactions()
    {
        return $this->hasMany(Transaction::class, 'seller_id');
    }

    // 購入者としての取引
    public function buyingTransactions()
    {
        return $this->hasMany(Transaction::class, 'buyer_id');
    }

    // 全ての取引（出品・購入両方）
    public function allTransactions()
    {
        return Transaction::where('seller_id', $this->id)
                        ->orWhere('buyer_id', $this->id);
    }

    // 受け取った評価
    public function receivedRatings()
    {
        return $this->hasMany(UserRating::class, 'rated_user_id');
    }

    // 付けた評価
    public function givenRatings()
    {
        return $this->hasMany(UserRating::class, 'rater_id');
    }

    // 平均評価を取得
    public function getAverageRating()
    {
        return $this->receivedRatings()->avg('rating') ?? 0;
    }

    // 平均評価（整数）を取得
    public function getAverageRatingRounded()
    {
        return round($this->getAverageRating());
    }
}
