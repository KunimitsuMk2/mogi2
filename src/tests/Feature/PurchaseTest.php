<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 「購入する」ボタンを押下すると購入が完了するかテスト
     */
    public function test_purchase_completes_on_button_click()
    {
        // ユーザーと商品を作成
        $user = User::factory()->create();
        $item = Item::factory()->create(['status' => 'available']);

        // 購入処理リクエスト
        $response = $this->actingAs($user)
                         ->post(route('purchase.complete', $item), [
                             'payment_method' => 'convenience_store'
                         ]);

        // リダイレクトとデータベースの確認
        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    /**
     * 購入した商品が「sold」と表示されるかテスト
     */
    public function test_purchased_item_is_marked_as_sold()
    {
        // ユーザーと商品を作成
        $user = User::factory()->create();
        $item = Item::factory()->create(['status' => 'available']);

        // 購入処理実行
        $this->actingAs($user)
             ->post(route('purchase.complete', $item), [
                 'payment_method' => 'convenience_store'
             ]);

        // 商品状態の確認
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'status' => 'sold'
        ]);

        // 商品一覧ページで「sold」と表示されるか確認
        $response = $this->get('/');
        $response->assertSee('SOLD');
    }

    /**
     * 購入した商品がプロフィールの購入履歴に追加されるかテスト
     */
    public function test_purchased_item_appears_in_profile_history()
    {
        // ユーザーと商品を作成
        $user = User::factory()->create();
        $item = Item::factory()->create(['status' => 'available']);

        // 購入処理実行
        $this->actingAs($user)
             ->post(route('purchase.complete', $item), [
                 'payment_method' => 'convenience_store'
             ]);

        // プロフィールページで購入履歴に表示されるか確認
        $response = $this->get(route('mypage', ['tab' => 'purchased']));
        $response->assertSee($item->name);
    }
}