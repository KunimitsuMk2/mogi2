<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\User;

class ItemSeeder extends Seeder
{
    public function run()
    {
        // テストユーザー3人を作成
        // CO01-CO05の商品を出品するユーザー
        $user1 = User::firstOrCreate(
            ['email' => 'seller1@example.com'],
            [
                'name' => '出品者1',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
                'postal_code' => '1234567',
                'address' => '東京都渋谷区渋谷1-1-1',
                'building_name' => 'テストマンション101',
            ]
        );

        // CO06-CO10の商品を出品するユーザー
        $user2 = User::firstOrCreate(
            ['email' => 'seller2@example.com'],
            [
                'name' => '出品者2',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
                'postal_code' => '2345678',
                'address' => '大阪府大阪市北区梅田2-2-2',
                'building_name' => 'テストビル202',
            ]
        );

        // 何も商品と紐づけられていない購入専用ユーザー
        $user3 = User::firstOrCreate(
            ['email' => 'buyer@example.com'],
            [
                'name' => '購入者',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
                'postal_code' => '3456789',
                'address' => '愛知県名古屋市中区栄3-3-3',
                'building_name' => 'テストプラザ303',
            ]
        );

        // 商品データ
        // CO01-CO05は出品者1（user1）が出品
        // CO06-CO10は出品者2（user2）が出品
        // 購入者（user3）は商品を出品しない
        $items = [
            // user1の商品（CO01-CO05）
            [
                'name' => '腕時計',
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'price' => 15000,
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Armani+Mens+Clock.jpg',
                'condition' => 1, // 良好
                'seller_id' => $user1->id,
                'status' => 'available'
            ],
            [
                'name' => 'HDD',
                'description' => '高速で信頼性の高いハードディスク',
                'price' => 5000,
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/HDD+Hard+Disk.jpg',
                'condition' => 3, // 目立った傷や汚れなし
                'seller_id' => $user1->id,
                'status' => 'available'
            ],
            [
                'name' => '玉ねぎ3束',
                'description' => '新鮮な玉ねぎ3束のセット',
                'price' => 300,
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/iLoveIMG+d.jpg',
                'condition' => 4, // やや傷や汚れあり
                'seller_id' => $user1->id,
                'status' => 'available'
            ],
            [
                'name' => '革靴',
                'description' => 'クラシックなデザインの革靴',
                'price' => 4000,
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Leather+Shoes+Product+Photo.jpg',
                'condition' => 6, // 状態が悪い
                'seller_id' => $user1->id,
                'status' => 'available'
            ],
            [
                'name' => 'ノートPC',
                'description' => '高性能なノートパソコン',
                'price' => 45000,
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Living+Room+Laptop.jpg',
                'condition' => 1, // 良好
                'seller_id' => $user1->id,
                'status' => 'available'
            ],
            // user2の商品（CO06-CO10）
            [
                'name' => 'マイク',
                'description' => '高音質のレコーディング用マイク',
                'price' => 8000,
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Music+Mic+4632231.jpg',
                'condition' => 3, // 目立った傷や汚れなし
                'seller_id' => $user2->id,
                'status' => 'available'
            ],
            [
                'name' => 'ショルダーバッグ',
                'description' => 'おしゃれなショルダーバッグ',
                'price' => 3500,
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Purse+fashion+pocket.jpg',
                'condition' => 4, // やや傷や汚れあり
                'seller_id' => $user2->id,
                'status' => 'available'
            ],
            [
                'name' => 'タンブラー',
                'description' => '使いやすいタンブラー',
                'price' => 500,
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Tumbler+souvenir.jpg',
                'condition' => 6, // 状態が悪い
                'seller_id' => $user2->id,
                'status' => 'sold'
            ],
            [
                'name' => 'コーヒーミル',
                'description' => '手動のコーヒーミル',
                'price' => 4000,
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Waitress+with+Coffee+Grinder.jpg',
                'condition' => 1, // 良好
                'seller_id' => $user2->id,
                'status' => 'available'
            ],
            [
                'name' => 'メイクセット',
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/%E5%A4%96%E5%87%BA%E3%83%A1%E3%82%A4%E3%82%AF%E3%82%A2%E3%83%83%E3%83%95%E3%82%9A%E3%82%BB%E3%83%83%E3%83%88.jpg',
                'condition' => 3, // 目立った傷や汚れなし
                'seller_id' => $user2->id,
                'status' => 'available'
            ],
        ];

        foreach ($items as $item) {
            Item::create($item);
        }
    }
}