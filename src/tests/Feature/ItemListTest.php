<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 全商品を取得できるかテスト
     */
    public function test_all_items_are_displayed()
    {
        // テスト商品を作成
        $items = Item::factory()->count(5)->create();

        // 商品一覧ページにアクセス
        $response = $this->get('/');

        // レスポンスとビューの確認
        $response->assertStatus(200);
        $response->assertViewHas('items');
        
        // 各商品が表示されていることを確認
        foreach ($items as $item) {
            $response->assertSee($item->name);
        }
    }

    /**
     * 購入済み商品に「Sold」と表示されるかテスト
     */
    public function test_sold_items_are_marked()
    {
        // 購入済み商品を作成
        $soldItem = Item::factory()->create(['status' => 'sold']);

        // 商品一覧ページにアクセス
        $response = $this->get('/');

        // Soldラベルが表示されているか確認
        $response->assertSee('SOLD');
    }

    /**
     * 自分が出品した商品は表示されないかテスト
     */
    public function test_own_items_not_displayed()
    {
        // ユーザーを作成
        $user = User::factory()->create();
        
        // 自分の商品を作成
        $ownItem = Item::factory()->create(['seller_id' => $user->id]);
        
        // 他のユーザーの商品を作成
        $otherItem = Item::factory()->create();

        // ログインした状態で商品一覧ページにアクセス
        $response = $this->actingAs($user)->get('/');

        // 自分の商品は表示されず、他の商品は表示されることを確認
        $response->assertDontSee($ownItem->name);
        $response->assertSee($otherItem->name);
    }
    
    /**
     * 未認証ユーザーにも商品一覧が表示されるかテスト
     */
    public function test_items_displayed_for_guests()
    {
        // テスト商品を作成
        $items = Item::factory()->count(3)->create();
        
        // 未ログイン状態で商品一覧ページにアクセス
        $response = $this->get('/');
        
        // 商品が表示されることを確認
        $response->assertStatus(200);
        foreach ($items as $item) {
            $response->assertSee($item->name);
        }
    }
}