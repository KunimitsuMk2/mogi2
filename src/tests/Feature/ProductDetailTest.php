<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Favorite;
use Illuminate\Support\Facades\Hash;

class ProductDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // テスト用ユーザーを作成
        $this->seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $this->buyer = User::create([
            'name' => '購入者',
            'email' => 'buyer@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // カテゴリを作成
        $this->category1 = Category::create(['name' => '家電']);
        $this->category2 = Category::create(['name' => 'スマートフォン']);

        // テスト用商品を作成
        $this->item = Item::create([
            'name' => 'iPhone 15 Pro',
            'description' => '最新のiPhone 15 Proです。状態は良好です。',
            'brand' => 'Apple',
            'price' => 150000,
            'condition' => Item::CONDITION_GOOD,
            'seller_id' => $this->seller->id,
            'status' => 'available',
            'image_url' => 'test-image.jpg', // 統一
        ]);

        // カテゴリを商品に関連付け
        $this->item->categories()->attach([$this->category1->id, $this->category2->id]);

        // いいねを作成
        Favorite::create([
            'user_id' => $this->buyer->id,
            'item_id' => $this->item->id,
        ]);

        // コメントを作成
        Comment::create([
            'user_id' => $this->buyer->id,
            'item_id' => $this->item->id,
            'content' => 'この商品について質問があります。',
        ]);
    }

    /** @test */
    public function 必要な情報が表示されている()
    {
        $response = $this->get("/item/{$this->item->id}");

        $response->assertStatus(200);
        $response->assertViewIs('products.item');
        
        // 商品情報が表示されている
        $response->assertSee('iPhone 15 Pro');           // 商品名
        $response->assertSee('Apple');                   // ブランド名
        $response->assertSee('¥150,000');              // 価格
        $response->assertSee('1');                      // いいね数
        $response->assertSee('1');                      // コメント数
        $response->assertSee('最新のiPhone 15 Proです'); // 商品説明
        $response->assertSee('良好');                    // 商品の状態
    }

    /** @test */
    public function 複数選択されたカテゴリが表示されている()
    {
        $response = $this->get("/item/{$this->item->id}");

        $response->assertStatus(200);
        
        // 複数のカテゴリが表示されている
        $response->assertSee('家電');
        $response->assertSee('スマートフォン');
    }

    /** @test */
    public function コメント情報が表示されている()
    {
        $response = $this->get("/item/{$this->item->id}");

        $response->assertStatus(200);
        
        // コメント数が表示されている
        $response->assertSee('コメント(1)');
        
        // コメント内容が表示されている
        $response->assertSee('この商品について質問があります。');
        
        // コメントしたユーザー名が表示されている
        $response->assertSee('購入者');
    }

    /** @test */
    public function 未認証ユーザーにも商品詳細が表示される()
    {
        // 未認証状態で商品詳細にアクセス
        $response = $this->get("/item/{$this->item->id}");

        $response->assertStatus(200);
        $response->assertViewIs('products.item');
        $response->assertSee('iPhone 15 Pro');
        $response->assertSee('Apple');
        $response->assertSee('¥150,000');
    }

    /** @test */
    public function 商品画像が表示される()
    {
        $response = $this->get("/item/{$this->item->id}");

        $response->assertStatus(200);
        $response->assertSee('test-image.jpg');
    }

    /** @test */
    public function いいね数とコメント数のアイコンが表示される()
    {
        $response = $this->get("/item/{$this->item->id}");

        $response->assertStatus(200);
        
        // いいねアイコンとコメントアイコンが表示されている
        $response->assertSee('★');  // いいねアイコン
        $response->assertSee('💬'); // コメントアイコン
    }
}