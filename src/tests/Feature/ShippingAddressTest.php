<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingAddressTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 送付先住所変更が商品購入画面に反映されるかテスト
     */
    public function test_shipping_address_reflected_in_purchase_screen()
    {
        // ユーザーと商品を作成
        $user = User::factory()->create([
            'postal_code' => '1234567',
            'address' => '東京都渋谷区',
            'building_name' => 'テストビル101'
        ]);
        $item = Item::factory()->create();

        // 住所を変更
        $response = $this->actingAs($user)
                         ->post(route('address.update'), [
                             'postal_code' => '9876543',
                             'address' => '大阪府大阪市',
                             'building_name' => 'サンプルマンション202',
                             'item_id' => $item->id
                         ]);

        // 商品購入画面で新しい住所が表示されることを確認
        $purchaseResponse = $this->get(route('products.confirm', $item));
        $purchaseResponse->assertSee('9876543');
        $purchaseResponse->assertSee('大阪府大阪市');
        $purchaseResponse->assertSee('サンプルマンション202');
    }

    /**
     * 購入した商品に送付先住所が紐づくかテスト
     */
    public function test_shipping_address_linked_to_purchased_item()
    {
        // ユーザーと商品を作成
        $user = User::factory()->create([
            'postal_code' => '9876543',
            'address' => '大阪府大阪市',
            'building_name' => 'サンプルマンション202'
        ]);
        $item = Item::factory()->create(['status' => 'available']);

        // 購入処理実行
        $this->actingAs($user)
             ->post(route('purchase.complete', $item), [
                 'payment_method' => 'convenience_store'
             ]);

        // 購入レコードに住所情報が紐づいていることを確認
        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'shipping_postal_code' => '9876543',
            'shipping_address' => '大阪府大阪市',
            'shipping_building_name' => 'サンプルマンション202'
        ]);
    }
}