<?php

namespace Tests\Feature;

use App\Models\Favorite;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * いいねの登録ができるかテスト
     */
    public function test_user_can_add_favorite()
    {
        // ユーザーと商品を作成
        $user = User::factory()->create();
        $item = Item::factory()->create();

        // いいね登録リクエスト
        $response = $this->actingAs($user)
                         ->post(route('favorites.toggle', $item));

        // レスポンスとデータベースの確認
        $response->assertStatus(200);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'item_id' => $item->id
        ]);
        
        // JSONレスポンスの確認（いいね数が増加）
        $response->assertJson([
            'success' => true,
            'count' => 1
        ]);
    }

    /**
     * いいね登録時にアイコンの色が変化するかテスト
     */
    public function test_favorite_icon_changes_color()
    {
        // ユーザーと商品を作成
        $user = User::factory()->create();
        $item = Item::factory()->create();
        
        // いいね登録前の商品詳細ページを取得
        $beforeResponse = $this->actingAs($user)->get("/item/{$item->id}");
        
        // いいね登録
        $this->post(route('favorites.toggle', $item));
        
        // いいね登録後の商品詳細ページを取得
        $afterResponse = $this->get("/item/{$item->id}");
        
        // いいねアイコンのクラスが変化していることを確認
        $beforeResponse->assertDontSee('favorite-active');
        $afterResponse->assertSee('favorite-active');
    }

    /**
     * いいねを解除できるかテスト
     */
    public function test_user_can_remove_favorite()
    {
        // ユーザーと商品を作成
        $user = User::factory()->create();
        $item = Item::factory()->create();
        
        // 事前にいいねを登録
        Favorite::create([
            'user_id' => $user->id,
            'item_id' => $item->id
        ]);

        // いいね解除リクエスト
        $response = $this->actingAs($user)
                         ->post(route('favorites.toggle', $item));

        // レスポンスとデータベースの確認
        $response->assertStatus(200);
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'item_id' => $item->id
        ]);
        
        // JSONレスポンスの確認（いいね数が減少）
        $response->assertJson([
            'success' => true,
            'count' => 0
        ]);
    }
}