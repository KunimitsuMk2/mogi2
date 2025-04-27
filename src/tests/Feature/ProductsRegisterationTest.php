<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 商品情報が正しく保存されるかテスト
     */
    public function test_product_information_saves_correctly()
    {
        // ファイルアップロードのモック設定
        Storage::fake('public');
        
        // テスト用のユーザーとカテゴリを作成
        $user = User::factory()->create();
        $category1 = Category::factory()->create(['name' => 'カテゴリ1']);
        $category2 = Category::factory()->create(['name' => 'カテゴリ2']);
        
        // 商品出品リクエスト
        $response = $this->actingAs($user)
                         ->post(route('products.store'), [
                             'name' => 'テスト商品',
                             'brand' => 'テストブランド',
                             'description' => '商品の説明文です',
                             'price' => 5000,
                             'condition' => 3, // 目立った傷や汚れなし
                             'image' => UploadedFile::fake()->image('product.jpg'),
                             'categories' => [$category1->id, $category2->id]
                         ]);

        // リダイレクトとデータベースの確認
        $response->assertRedirect(route('products.index'));
        
        // 商品情報が保存されていることを確認
        $this->assertDatabaseHas('items', [
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => '商品の説明文です',
            'price' => 5000,
            'condition' => 3,
            'seller_id' => $user->id,
            'status' => 'available'
        ]);
        
        // 画像が保存されたことを確認
        Storage::disk('public')->assertExists('images/product.jpg');
        
        // カテゴリとの関連付けを確認
        $itemId = \App\Models\Item::where('name', 'テスト商品')->first()->id;
        $this->assertDatabaseHas('category_item', [
            'item_id' => $itemId,
            'category_id' => $category1->id
        ]);
        $this->assertDatabaseHas('category_item', [
            'item_id' => $itemId,
            'category_id' => $category2->id
        ]);
    }
    
    /**
     * カテゴリ複数選択が可能かテスト
     */
    public function test_multiple_categories_can_be_selected()
    {
        // テスト用のユーザーとカテゴリを作成
        $user = User::factory()->create();
        $categories = Category::factory()->count(5)->create();
        
        // 出品画面にアクセス
        $response = $this->actingAs($user)->get(route('products.create'));
        
        // カテゴリ選択肢が表示されていることを確認
        $response->assertStatus(200);
        foreach ($categories as $category) {
            $response->assertSee($category->name);
        }
        
        // チェックボックスとして表示されていることを確認
        $response->assertSee('name="categories[]"', false);
    }
}