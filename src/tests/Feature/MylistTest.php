<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Favorite;
use Illuminate\Support\Facades\Hash;

class MylistTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // テスト用ユーザーを作成
        $this->user1 = User::create([
            'name' => 'テストユーザー1',
            'email' => 'test1@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $this->user2 = User::create([
            'name' => 'テストユーザー2',
            'email' => 'test2@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // テスト用商品を作成
        $this->item1 = Item::create([
            'name' => 'いいね商品1',
            'description' => 'いいね商品1の説明',
            'price' => 1000,
            'condition' => Item::CONDITION_GOOD,
            'seller_id' => $this->user2->id,
            'status' => 'available',
            'image_url' => 'like-item1.jpg', // 追加
        ]);

        $this->item2 = Item::create([
            'name' => 'いいね商品2',
            'description' => 'いいね商品2の説明',
            'price' => 2000,
            'condition' => Item::CONDITION_LIKE_NEW,
            'seller_id' => $this->user2->id,
            'status' => 'sold',  // 購入済み
            'image_url' => 'like-item2.jpg', // 追加
        ]);

        $this->item3 = Item::create([
            'name' => '通常商品',
            'description' => '通常商品の説明',
            'price' => 3000,
            'condition' => Item::CONDITION_GOOD,
            'seller_id' => $this->user2->id,
            'status' => 'available',
            'image_url' => 'normal-item.jpg', // 追加
        ]);

        // user1がitem1とitem2をいいね
        Favorite::create([
            'user_id' => $this->user1->id,
            'item_id' => $this->item1->id,
        ]);

        Favorite::create([
            'user_id' => $this->user1->id,
            'item_id' => $this->item2->id,
        ]);
    }

    /** @test */
    public function いいねした商品だけが表示されている()
    {
        // user1でログイン
        $this->actingAs($this->user1);

        $response = $this->get('/?tab=mylist');

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        
        // いいねした商品が表示される
        $response->assertSee('いいね商品1');
        $response->assertSee('いいね商品2');
        
        // いいねしていない商品は表示されない
        $response->assertDontSee('通常商品');
    }

    /** @test */
    public function 購入済み商品はSoldと表示される()
    {
        // user1でログイン
        $this->actingAs($this->user1);

        $response = $this->get('/?tab=mylist');

        $response->assertStatus(200);
        $response->assertSee('SOLD');  // 購入済み商品のラベル
    }

    /** @test */
    public function 未認証の場合は何も表示されない()
    {
        // 未認証状態でマイリストにアクセス
        $response = $this->get('/?tab=mylist');

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        
        // 商品が表示されないことを確認
        $response->assertDontSee('いいね商品1');
        $response->assertDontSee('いいね商品2');
        $response->assertDontSee('通常商品');
    }

    /** @test */
    public function マイリストタブが正しく動作する()
    {
        // user1でログイン
        $this->actingAs($this->user1);

        // おすすめタブ
        $response = $this->get('/?tab=recommended');
        $response->assertStatus(200);
        $response->assertSee('通常商品');  // 他人の商品が表示される

        // マイリストタブ
        $response = $this->get('/?tab=mylist');
        $response->assertStatus(200);
        $response->assertSee('いいね商品1');
        $response->assertDontSee('通常商品');
    }
}