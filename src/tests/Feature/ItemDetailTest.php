<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 必要な情報が表示されるかテスト
     */
    public function test_all_required_info_is_displayed()
    {
        // テスト商品を作成
        $item = Item::factory()->create([
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'price' => 1000,
            'description' => '商品の説明文です',
            'condition' => Item::CONDITION_GOOD
        ]);
        
        // コメントを追加
        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'content' => 'テストコメント'
        ]);

        // 商品詳細ページにアクセス
        $response = $this->get("/item/{$item->id}");

        // 各情報が表示されていることを確認
        $response->assertSee($item->name);
        $response->assertSee($item->brand);
        $response->assertSee('1,000'); // 価格
        $response->assertSee($item->description);
        $response->assertSee($item->getConditionNameAttribute());
        $response->assertSee($comment->content);
        $response->assertSee($user->name);
    }

    /**
     * 複数選択されたカテゴリが表示されるかテスト
     */
    public function test_multiple_categories_are_displayed()
    {
        // カテゴリを作成
        $category1 = Category::factory()->create(['name' => 'カテゴリ1']);
        $category2 = Category::factory()->create(['name' => 'カテゴリ2']);
        
        // 商品を作成してカテゴリを紐付け
        $item = Item::factory()->create();
        $item->categories()->attach([$category1->id, $category2->id]);

        // 商品詳細ページにアクセス
        $response = $this->get("/item/{$item->id}");

        // 両方のカテゴリが表示されていることを確認
        $response->assertSee($category1->name);
        $response->assertSee($category2->name);
    }
    
    /**
     * 未認証ユーザーにも詳細が表示されるかテスト
     */
    public function test_details_displayed_for_guests()
    {
        // テスト商品を作成
        $item = Item::factory()->create();
        
        // 未ログイン状態で商品詳細ページにアクセス
        $response = $this->get("/item/{$item->id}");
        
        // 商品情報が表示されることを確認
        $response->assertStatus(200);
        $response->assertSee($item->name);
    }
}