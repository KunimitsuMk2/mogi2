<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ExhibitionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // テスト用ユーザーを作成
        $this->user = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // テスト用カテゴリを作成
        $this->category1 = Category::create(['name' => 'ファッション']);
        $this->category2 = Category::create(['name' => '家電']);
        $this->category3 = Category::create(['name' => 'インテリア']);
    }

    /** @test */
    public function 商品出品画面が正しく表示される()
    {
        // ユーザーでログイン
        $this->actingAs($this->user);

        // 出品画面にアクセス
        $response = $this->get('/sell');

        $response->assertStatus(200);
        $response->assertViewIs('products.create');
        $response->assertSee('商品の出品');
        
        // カテゴリ選択肢が表示されている
        $response->assertSee('ファッション');
        $response->assertSee('家電');
        $response->assertSee('インテリア');
        
        // 商品の状態選択肢が表示されている
        $response->assertSee('新品');
        $response->assertSee('未使用に近い');
        $response->assertSee('目立った傷や汚れなし');
    }

    /** @test */
    public function 商品出品画面にて必要な情報が保存できる()
    {
        // ストレージのテスト用設定
        Storage::fake('public');
        
        // ユーザーでログイン
        $this->actingAs($this->user);

        // テスト用ファイル（GD拡張なしでも動作）
        $file = UploadedFile::fake()->create('product.jpg', 1000); // 1000KB

        // 商品出品
        $response = $this->post('/products', [
            'name' => 'テスト商品',
            'description' => 'これはテスト用の商品説明です。',
            'brand' => 'テストブランド',
            'price' => 1500,
            'condition' => Item::CONDITION_GOOD,
            'image' => $file,
            'categories' => [$this->category1->id, $this->category2->id],
        ]);

        // トップページにリダイレクトされる
        $response->assertRedirect('/');

        // 商品がデータベースに保存されている
        $this->assertDatabaseHas('items', [
            'name' => 'テスト商品',
            'description' => 'これはテスト用の商品説明です。',
            'brand' => 'テストブランド',
            'price' => 1500,
            'condition' => Item::CONDITION_GOOD,
            'seller_id' => $this->user->id,
            'status' => 'available',
        ]);

        // カテゴリが正しく関連付けられている
        $item = Item::where('name', 'テスト商品')->first();
        $this->assertTrue($item->categories->contains($this->category1));
        $this->assertTrue($item->categories->contains($this->category2));
        $this->assertFalse($item->categories->contains($this->category3));

        // 画像ファイルがstorageディレクトリに保存されている
        $this->assertNotNull($item->image_url);
        $this->assertStringContainsString('storage/', $item->image_url); // 正しいメソッド名
    }

    /** @test */
    public function 商品名が入力されていない場合バリデーションエラーが表示される()
    {
        // ユーザーでログイン
        $this->actingAs($this->user);

        // 商品名なしで出品
        $response = $this->post('/products', [
            'name' => '',
            'description' => 'テスト説明',
            'price' => 1000,
            'condition' => Item::CONDITION_GOOD,
            'categories' => [$this->category1->id],
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function 商品説明が入力されていない場合バリデーションエラーが表示される()
    {
        // ユーザーでログイン
        $this->actingAs($this->user);

        // 商品説明なしで出品
        $response = $this->post('/products', [
            'name' => 'テスト商品',
            'description' => '',
            'price' => 1000,
            'condition' => Item::CONDITION_GOOD,
            'categories' => [$this->category1->id],
        ]);

        $response->assertSessionHasErrors('description');
    }

    /** @test */
    public function 商品画像が選択されていない場合バリデーションエラーが表示される()
    {
        // ユーザーでログイン
        $this->actingAs($this->user);

        // 画像なしで出品
        $response = $this->post('/products', [
            'name' => 'テスト商品',
            'description' => 'テスト説明',
            'price' => 1000,
            'condition' => Item::CONDITION_GOOD,
            'categories' => [$this->category1->id],
        ]);

        $response->assertSessionHasErrors('image');
    }

    /** @test */
    public function カテゴリが選択されていない場合バリデーションエラーが表示される()
    {
        // ユーザーでログイン
        $this->actingAs($this->user);

        // テスト用ファイル
        Storage::fake('public');
        $file = UploadedFile::fake()->create('product.jpg', 1000);

        // カテゴリなしで出品
        $response = $this->post('/products', [
            'name' => 'テスト商品',
            'description' => 'テスト説明',
            'price' => 1000,
            'condition' => Item::CONDITION_GOOD,
            'image' => $file,
            // categories パラメータを送信しない
        ]);

        $response->assertSessionHasErrors('categories');
    }

    /** @test */
    public function 商品の状態が選択されていない場合バリデーションエラーが表示される()
    {
        // ユーザーでログイン
        $this->actingAs($this->user);

        // 状態なしで出品
        $response = $this->post('/products', [
            'name' => 'テスト商品',
            'description' => 'テスト説明',
            'price' => 1000,
            'condition' => '',
            'categories' => [$this->category1->id],
        ]);

        $response->assertSessionHasErrors('condition');
    }

    /** @test */
    public function 販売価格が入力されていない場合バリデーションエラーが表示される()
    {
        // ユーザーでログイン
        $this->actingAs($this->user);

        // 価格なしで出品
        $response = $this->post('/products', [
            'name' => 'テスト商品',
            'description' => 'テスト説明',
            'price' => '',
            'condition' => Item::CONDITION_GOOD,
            'categories' => [$this->category1->id],
        ]);

        $response->assertSessionHasErrors('price');
    }

    /** @test */
    public function 未ログインユーザーは出品画面にアクセスできない()
    {
        // 未ログイン状態で出品画面にアクセス
        $response = $this->get('/sell');

        // ログイン画面にリダイレクトされる
        $response->assertRedirect('/login');
    }
}