<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    // 一括代入を許可するカラム
    protected $fillable = [
        'name',
        'description',
        'price',
        'image_url',
        'condition',
        'seller_id',
        'status',
        'brand'  // 追加
    ];

    // 商品状態の定数
    const CONDITION_NEW = 1;
    const CONDITION_LIKE_NEW = 2;
    const CONDITION_GOOD = 3;
    const CONDITION_FAIR = 4;
    const CONDITION_POOR = 5;
    const CONDITION_BAD = 6;
    
    // 商品状態のマッピング配列
    public static $conditionNames = [
        self::CONDITION_NEW => '新品',
        self::CONDITION_LIKE_NEW => '未使用に近い',
        self::CONDITION_GOOD => '目立った傷や汚れなし',
        self::CONDITION_FAIR => 'やや傷や汚れあり',
        self::CONDITION_POOR => '傷や汚れあり',
        self::CONDITION_BAD => '全体的に状態が悪い'
    ];
    
    // 商品状態を文字列で取得するアクセサ
    public function getConditionNameAttribute() {
        return self::$conditionNames[$this->condition] ?? '不明';
    }

    // 出品者とのリレーション定義
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    // コメントとのリレーション
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // お気に入り関連のリレーション
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites', 'item_id', 'user_id');
    }
    
    // カテゴリとのリレーション
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_item');
    }
}