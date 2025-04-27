<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 必要な情報が取得できるかテスト
     */
    public function test_required_info_is_displayed()
    {
        // ユーザーを作成
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'avatar' => 'avatars/test.jpg'
        ]);
        
        // 出品商品を作成
        $listedItem = Item::factory()->create([
            'name' => '出品商品',
            'seller_id' => $user->id
        ]);
        
        // 購入商品を作成
        $purchasedItem = Item::factory()->create([
            'name' => '購入商品',
            'status' => 'sold'
        ]);
        
        // 購入情報を作成
        Purchase::factory()->create([
            'user_id' => $user->id,
            'item_id' => $purchasedItem->id
        ]);

        // プロフィールページにアクセス
        $response = $this->actingAs($user)->get(route('mypage'));

        // 必要な情報が表示されていることを確認
        $response->assertStatus(200);
        $response->assertSee('テストユーザー');
        $response->assertSee('avatars/test.jpg');
        
        // 「出品した商品」タブを確認
        $response->assertSee('出品した商品');
        $response->assertSee('出品商品');
        
        // 「購入した商品」タブを確認
        $purchasedResponse = $this->actingAs($user)->get(route('mypage', ['tab' => 'purchased']));
        $purchasedResponse->assertSee('購入した商品');
        $purchasedResponse->assertSee('購入商品');
    }
}