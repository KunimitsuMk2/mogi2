<?php

namespace Tests\Feature;

use App\Models\Favorite;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MylistTest extends TestCase
{
    use RefreshDatabase;

    /**
     * いいねした商品だけが表示されるかテスト
     */
    public function test_only_favorited_items_displayed()
    {
        // ユーザーを作成
        $user = User::factory()->create();
        
        // 商品を作成
        $favoritedItem = Item::factory()->create();
        $otherItem = Item::factory()->create();
        
        // いいねを追加
        Favorite::create([
            'user_id' => $user->id,
            'item_id' => $favoritedItem->id
        ]);

        // マイリストページにアクセス
        $response = $this->actingAs($user)->get('/?tab=mylist');

        // いいねした商品だけが表示されることを確認
        $response->assertSee($favoritedItem->name);
        $response->assertDontSee($otherItem->name);
    }

    /**
     * 購入済み商品に「Sold」と表示されるかテスト
     */
    public function test_sold_items_are_marked_in_mylist()
    {
        // ユーザーを作成
        $user = User::factory()->create();
        
        // 購入済み商品を作成
        $soldItem = Item::factory()->create(['status' => 'sold']);
        
        // いいねを追加
        Favorite::create([
            'user_id' => $user->id,
            'item_id' => $soldItem->id
        ]);

        // マイリストページにアクセス
        $response = $this->actingAs($user)->get('/?tab=mylist');

        // Soldラベルが表示されているか確認
        $response->assertSee('SOLD');
    }
    
    /**
     * 自分が出品した商品は表示されないかテスト
     */
    public function test_own_items_not_displayed_in_mylist()
    {
        // ユーザーを作成
        $user = User::factory()->create();
        
        // 自分の商品を作成していいねも追加
        $ownItem = Item::factory()->create(['seller_id' => $user->id]);
        Favorite::create([
            'user_id' => $user->id,
            'item_id' => $ownItem->id
        ]);

        // マイリストページにアクセス
        $response = $this->actingAs($user)->get('/?tab=mylist');

        // 自分の出品商品が表示されないことを確認
        $response->assertDontSee($ownItem->name);
    }

    /**
     * 未認証の場合は何も表示されないかテスト
     */
    public function test_nothing_displayed_when_not_authenticated()
    {
        // 商品を作成
        $items = Item::factory()->count(3)->create();

        // 未ログイン状態でマイリストページにアクセス
        $response = $this->get('/?tab=mylist');

        // 商品が表示されないことを確認
        foreach ($items as $item) {
            $response->assertDontSee($item->name);
        }
    }
}