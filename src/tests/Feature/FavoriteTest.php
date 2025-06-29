<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Favorite;
use Illuminate\Support\Facades\Hash;

class FavoriteTest extends TestCase
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

        $this->seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // テスト用商品を作成
        $this->item = Item::create([
            'name' => 'テスト商品',
            'description' => 'テスト商品の説明',
            'price' => 1000,
            'condition' => Item::CONDITION_GOOD,
            'seller_id' => $this->seller->id,
            'status' => 'available',
            'image_url' => 'test-favorite.jpg', // 追加
        ]);
    }

    /** @test */
    public function いいねアイコンを押下することで_いいねした商品として登録することができる()
    {
        // ユーザーをログイン
        $this->actingAs($this->user);

        // 初期状態ではいいねは0件
        $this->assertEquals(0, $this->item->favorites()->count());

        // いいねを実行
        $response = $this->post("/item/{$this->item->id}/favorite");

        $response->assertRedirect();

        // いいねが1件増加している
        $this->assertEquals(1, $this->item->fresh()->favorites()->count());

        // データベースにいいねが保存されている
        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
        ]);
    }

    /** @test */
    public function 商品詳細画面のいいね合計値が増加表示される()
    {
        // ユーザーをログイン
        $this->actingAs($this->user);

        // いいねを実行
        $this->post("/item/{$this->item->id}/favorite");

        // 商品詳細画面を表示
        $response = $this->get("/item/{$this->item->id}");

        $response->assertStatus(200);
        // いいね数が1と表示されている
        $response->assertSee('1');
    }

    /** @test */
    public function 追加済みのアイコンは色が変化する()
    {
        // ユーザーをログイン
        $this->actingAs($this->user);

        // いいねを実行
        $this->post("/item/{$this->item->id}/favorite");

        // 商品詳細画面を表示
        $response = $this->get("/item/{$this->item->id}");

        $response->assertStatus(200);
        // アクティブ状態のクラスが含まれている
        $response->assertSee('favorite-active');
    }

    /** @test */
    public function 再度いいねアイコンを押下することで_いいねを解除することができる()
    {
        // ユーザーをログイン
        $this->actingAs($this->user);

        // まずいいねを追加
        $this->post("/item/{$this->item->id}/favorite");
        $this->assertEquals(1, $this->item->fresh()->favorites()->count());

        // 再度いいねを押下（解除）
        $response = $this->post("/item/{$this->item->id}/favorite");

        $response->assertRedirect();

        // いいねが解除されている
        $this->assertEquals(0, $this->item->fresh()->favorites()->count());

        // データベースからいいねが削除されている
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
        ]);
    }

    /** @test */
    public function 商品詳細画面のいいね合計値が減少表示される()
    {
        // ユーザーをログイン
        $this->actingAs($this->user);

        // いいねを追加してから解除
        $this->post("/item/{$this->item->id}/favorite");
        $this->post("/item/{$this->item->id}/favorite");

        // 商品詳細画面を表示
        $response = $this->get("/item/{$this->item->id}");

        $response->assertStatus(200);
        // いいね数が0と表示されている
        $response->assertSee('0');
    }

    /** @test */
    public function 未ログインユーザーはいいねできない()
    {
        // 未ログイン状態でいいねを試行
        $response = $this->post("/item/{$this->item->id}/favorite");

        // ログイン画面にリダイレクトされる
        $response->assertRedirect('/login');

        // いいねは追加されていない
        $this->assertEquals(0, $this->item->favorites()->count());
    }

    /** @test */
    public function Ajax形式でいいね状態を変更できる()
    {
        // ユーザーをログイン
        $this->actingAs($this->user);

        // Ajax形式でいいねを実行
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => csrf_token(),
        ])->post("/item/{$this->item->id}/favorite");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'count' => 1,
        ]);

        // いいねが追加されている
        $this->assertEquals(1, $this->item->fresh()->favorites()->count());
    }
}