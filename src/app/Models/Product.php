<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model {
    use HasFactory;
    
    protected $fillable = [
        'name',
        'description',
        'brand_name',
        'price',
        'condition',
        'image_path',
        'seller_id'
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
    
    public function categories(){
        return $this->belongsToMany(Category::class);
    }
    
    public function seller() {
        return $this->belongsTo(User::class, 'seller_id');
    }
    
    // 商品画像のURLを取得するアクセサ
    public function getImageUrlAttribute() {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        return asset('images/no-image.png'); // デフォルト画像
    }
}