<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    // 保存を許可するカラムを明示（セキュリティのため）
    protected $fillable = [
        'user_id',
        'item_id',
        'quantity',
        'price',
        'payment_method',
        'shipping_postal_code',
        'shipping_address',
        'shipping_building_name',
        'status',
        'purchased_at'
    ];

    // 日付として扱いたいカラム
    protected $dates = ['purchased_at'];

    // ユーザーとのリレーション（多対1）
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 商品とのリレーション（多対1）
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}