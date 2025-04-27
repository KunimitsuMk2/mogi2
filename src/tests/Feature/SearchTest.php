<?php

namespace Tests\Feature;

use App\Models\Favorite;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 商品名で部分一致検索ができるかテスト
     */
    public function test_search_by_partial_name_match()
    {
        // テスト商品を作成
        $matchingItem1 = Item::factory()->create(['name' => 'テスト商品ABC']);
        $matchingItem2 = Item::factory()->create(['name' => '別のABC商品']);
        $nonMatchingItem = Item::factory()->create(['name' => 'サンプル商品XYZ']);

        // 検索実行
        $response = $this->get('/?search=ABC');

        // 検索結果の確認
        $response->assertStatus(200);
        $response->assertSee($matchingItem1->name);
        $response->assertSee($matchingItem2->name);
        $response->assertDontSee($nonMatchingItem->name);
    }

    /**
     * ヘッダー内に検索欄が実装されているかテスト
     */
    public function test_search_form_exists_in_header()
    {
        $response = $this->get('/');

        // ヘッダー内に検索フォームが存在することを確認
        $response->assertStatus(200);
        $response->assertSee('<form action="', false);
        $response->assertSee('method="GET"', false);
        $response->assertSee('name="search"', false);
        $response->assertSee('placeholder="なにをお探してすか？"', false);
    }

    /**
     * 検索状態がマイリストでも保持されるかテスト
     */
    public function test_search_state_preserved_in_mylist()
    {
        // ユーザーとテスト商品を作成
        $user = User::factory()->create();
        $matchingItem1 = Item::factory()->create(['name' => 'いいね済みABC商品']);
        $matchingItem2 = Item::factory()->create(['name' => '非いいねABC商品']);
        
        // いいねを追加
        Favorite::create([
            'user_id' => $user->id,
            'item_id' => $matchingItem1->id
        ]);

        // 検索実行後にマイリストタブに切り替え
        $response = $this->actingAs($user)
                         ->get('/?search=ABC&tab=mylist');

        // 検索キーワードが保持され、条件に合った商品だけが表示されることを確認
        $response->assertStatus(200);
        $response->assertSee($matchingItem1->name);
        $response->assertDontSee($matchingItem2->name);
        
        // 検索フォームに値が残っていることを確認
        $response->assertSee('value="ABC"', false);
    }
}