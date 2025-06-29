<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Favorite;
use Illuminate\Support\Facades\Hash;

class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // テスト用ユーザーを作成
        $this->user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // テスト用商品を作成
        $this->item1 = Item::create([
            'name' => 'iPhone 15',
            'description' => 'iPhone 15の説明',
            'price' => 100000,
            'condition' => Item::CONDITION_GOOD,
            'seller_id' => $this->user->id,
            'status' => 'available',
            'image_url' => 'iphone15.jpg', // 追加
        ]);

        $this->item2 = Item::create([
            'name' => 'Android スマートフォン',
            'description' => 'Android端末の説明',
            'price' => 50000,
            'condition' => Item::CONDITION_LIKE_NEW,
            'seller_id' => $this->user->id,
            'status' => 'available',
            'image_url' => 'android.jpg', // 追加
        ]);

        $this->item3 = Item::create([
            'name' => 'ノートパソコン',
            'description' => 'ノートパソコンの説明',
            'price' => 80000,
            'condition' => Item::CONDITION_GOOD,
            'seller_id' => $this->user->id,
            'status' => 'available',
            'image_url' => 'laptop.jpg', // 追加
        ]);
    }

    /** @test */
    public function 商品名で部分一致検索ができる()
    {
        // "iPhone"で検索
        $response = $this->get('/?search=iPhone');

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertSee('iPhone 15');
        $response->assertDontSee('Android スマートフォン');
        $response->assertDontSee('ノートパソコン');
    }

    /** @test */
    public function 部分一致で複数の商品が検索される()
    {
        // "スマート"で検索（"スマートフォン"が該当）
        $response = $this->get('/?search=スマート');

        $response->assertStatus(200);
        $response->assertSee('Android スマートフォン');
        $response->assertDontSee('iPhone 15');
        $response->assertDontSee('ノートパソコン');
    }

    /** @test */
    public function 検索結果が存在しない場合()
    {
        // 存在しない商品名で検索
        $response = $this->get('/?search=存在しない商品');

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertDontSee('iPhone 15');
        $response->assertDontSee('Android スマートフォン');
        $response->assertDontSee('ノートパソコン');
    }

    /** @test */
    public function 検索状態がマイリストでも保持されている()
    {
        // ユーザーをログイン
        $this->actingAs($this->user);
        
        // 別のユーザーの商品を作成（自分の商品は表示されないため）
        $otherUser = User::create([
            'name' => '他のユーザー',
            'email' => 'other@example.com',
            'password' => Hash::make('password123'),
        ]);

        $searchableItem = Item::create([
            'name' => 'iPhone 検索テスト',
            'description' => '検索テスト用商品',
            'price' => 90000,
            'condition' => Item::CONDITION_GOOD,
            'seller_id' => $otherUser->id,
            'status' => 'available',
            'image_url' => 'search-test.jpg', // 追加
        ]);

        // 商品をいいね
        Favorite::create([
            'user_id' => $this->user->id,
            'item_id' => $searchableItem->id,
        ]);

        // おすすめページで検索
        $response = $this->get('/?search=iPhone&tab=recommended');
        $response->assertStatus(200);
        $response->assertSee('iPhone 検索テスト');

        // マイリストページでも同じ検索キーワードが保持される
        $response = $this->get('/?search=iPhone&tab=mylist');
        $response->assertStatus(200);
        $response->assertSee('iPhone 検索テスト');
    }

    /** @test */
    public function 検索フォームにキーワードが保持される()
    {
        $response = $this->get('/?search=iPhone');

        $response->assertStatus(200);
        // 検索フォームに入力されたキーワードが保持されているか確認
        $response->assertSee('value="iPhone"', false);
    }

    /** @test */
    public function 空の検索で全商品が表示される()
    {
        $response = $this->get('/?search=');

        $response->assertStatus(200);
        $response->assertSee('iPhone 15');
        $response->assertSee('Android スマートフォン');
        $response->assertSee('ノートパソコン');
    }
}