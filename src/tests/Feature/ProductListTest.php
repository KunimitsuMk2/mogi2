<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;

class ProductListTest extends TestCase
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
            'name' => 'テスト商品1',
            'description' => 'テスト商品1の説明',
            'price' => 1000,
            'condition' => Item::CONDITION_GOOD,
            'seller_id' => $this->user1->id,
            'status' => 'available',
            'image_url' => 'test-image1.jpg', // 追加
        ]);

        $this->item2 = Item::create([
            'name' => 'テスト商品2',
            'description' => 'テスト商品2の説明',
            'price' => 2000,
            'condition' => Item::CONDITION_LIKE_NEW,
            'seller_id' => $this->user2->id,
            'status' => 'available',
            'image_url' => 'test-image2.jpg', // 追加
        ]);

        $this->soldItem = Item::create([
            'name' => '売り切れ商品',
            'description' => '売り切れ商品の説明',
            'price' => 3000,
            'condition' => Item::CONDITION_GOOD,
            'seller_id' => $this->user2->id,
            'status' => 'sold',
            'image_url' => 'test-image3.jpg', // 追加
        ]);
    }

    /** @test */
    public function 全商品を取得できる()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertSee('テスト商品1');
        $response->assertSee('テスト商品2');
        $response->assertSee('売り切れ商品');
    }

    /** @test */
    public function 購入済み商品はSoldと表示される()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('SOLD');
    }

    /** @test */
    public function 自分が出品した商品は表示されない()
    {
        // user1でログイン
        $this->actingAs($this->user1);

        $response = $this->get('/');

        $response->assertStatus(200);
        // 自分の商品（テスト商品1）は表示されない
        $response->assertDontSee('テスト商品1');
        // 他人の商品は表示される
        $response->assertSee('テスト商品2');
        $response->assertSee('売り切れ商品');
    }

    /** @test */
    public function 未認証ユーザーにも商品一覧が表示される()
    {
        // 未認証状態で商品一覧にアクセス
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertSee('テスト商品1');
        $response->assertSee('テスト商品2');
        $response->assertSee('売り切れ商品');
    }
}