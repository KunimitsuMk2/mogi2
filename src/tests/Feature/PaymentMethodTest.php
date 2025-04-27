<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 支払い方法選択の変更が即時反映されるかテスト
     */
    public function test_payment_method_selection_updates_immediately()
    {
        // ユーザーと商品を作成
        $user = User::factory()->create();
        $item = Item::factory()->create();

        // 購入確認ページにアクセス
        $response = $this->actingAs($user)
                         ->get(route('products.confirm', $item));

        // JavaScript動作確認のためのマーカーが存在することを確認
        $response->assertStatus(200);
        $response->assertSee('payment_method_select');
        $response->assertSee('payment-method-display');
    }

    /**
     * 小計画面に支払い方法が正しく表示されるかテスト
     */
    public function test_payment_method_displayed_correctly()
    {
        // ユーザーと商品を作成
        $user = User::factory()->create();
        $item = Item::factory()->create();

        // 購入確認ページにアクセス
        $response = $this->actingAs($user)
                         ->get(route('products.confirm', $item));

        // デフォルトの支払い方法が表示されていることを確認
        $response->assertSee('コンビニ払い');
    }
}