<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Comment;
use Illuminate\Support\Facades\Hash;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // テスト用ユーザーを作成
        $this->user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $this->seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // テスト用商品を作成
        $this->item = Item::create([
            'name' => 'テスト商品',
            'description' => 'テスト商品の説明',
            'price' => 1000,
            'condition' => Item::CONDITION_GOOD,
            'seller_id' => $this->seller->id,
            'status' => 'available',
            'image_url' => 'test-item.jpg', // 追加
        ]);
    }

    /** @test */
    public function ログイン済みのユーザーはコメントを送信できる()
    {
        // ユーザーをログイン
        $this->actingAs($this->user);

        // 初期状態ではコメントは0件
        $this->assertEquals(0, $this->item->comments()->count());

        // コメントを送信
        $response = $this->post("/item/{$this->item->id}/comment", [
            'content' => 'これはテストコメントです。',
        ]);

        $response->assertRedirect("/item/{$this->item->id}");

        // コメントが1件増加している
        $this->assertEquals(1, $this->item->fresh()->comments()->count());

        // データベースにコメントが保存されている
        $this->assertDatabaseHas('comments', [
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
            'content' => 'これはテストコメントです。',
        ]);
    }

    /** @test */
    public function コメントが保存され_コメント数が増加する()
    {
        // ユーザーをログイン
        $this->actingAs($this->user);

        // コメントを送信
        $this->post("/item/{$this->item->id}/comment", [
            'content' => 'テストコメント',
        ]);

        // 商品詳細画面を表示
        $response = $this->get("/item/{$this->item->id}");

        $response->assertStatus(200);
        // コメント数が1と表示されている
        $response->assertSee('コメント(1)');
        // コメント内容が表示されている
        $response->assertSee('テストコメント');
    }

    /** @test */
    public function ログイン前のユーザーはコメントを送信できない()
    {
        // 未ログイン状態でコメントを送信
        $response = $this->post("/item/{$this->item->id}/comment", [
            'content' => 'テストコメント',
        ]);

        // ログイン画面にリダイレクトされる
        $response->assertRedirect('/login');

        // コメントは追加されていない
        $this->assertEquals(0, $this->item->comments()->count());
    }

    /** @test */
    public function コメントが入力されていない場合_バリデーションメッセージが表示される()
    {
        // ユーザーをログイン
        $this->actingAs($this->user);

        // 空のコメントを送信
        $response = $this->post("/item/{$this->item->id}/comment", [
            'content' => '',
        ]);

        $response->assertSessionHasErrors('content');
        
        // エラーメッセージの確認
        $errors = session('errors');
        $this->assertStringContainsString('コメントを入力してください', $errors->first('content'));
    }

    /** @test */
    public function コメントが255字以上の場合_バリデーションメッセージが表示される()
    {
        // ユーザーをログイン
        $this->actingAs($this->user);

        // 256文字のコメントを送信
        $longComment = str_repeat('あ', 256);
        $response = $this->post("/item/{$this->item->id}/comment", [
            'content' => $longComment,
        ]);

        $response->assertSessionHasErrors('content');
        
        // エラーメッセージの確認
        $errors = session('errors');
        $this->assertStringContainsString('255文字以内', $errors->first('content'));
    }

    /** @test */
    public function 複数のコメントを送信できる()
    {
        // ユーザーをログイン
        $this->actingAs($this->user);

        // 複数のコメントを送信
        $this->post("/item/{$this->item->id}/comment", [
            'content' => '1つ目のコメント',
        ]);

        $this->post("/item/{$this->item->id}/comment", [
            'content' => '2つ目のコメント',
        ]);

        // コメントが2件になっている
        $this->assertEquals(2, $this->item->fresh()->comments()->count());

        // 商品詳細画面でコメント数が正しく表示される
        $response = $this->get("/item/{$this->item->id}");
        $response->assertSee('コメント(2)');
        $response->assertSee('1つ目のコメント');
        $response->assertSee('2つ目のコメント');
    }

    /** @test */
    public function コメント送信後に成功メッセージが表示される()
    {
        // ユーザーをログイン
        $this->actingAs($this->user);

        // コメントを送信
        $response = $this->post("/item/{$this->item->id}/comment", [
            'content' => 'テストコメント',
        ]);

        // リダイレクト先で成功メッセージが表示される
        $response->assertRedirect("/item/{$this->item->id}");
        $response->assertSessionHas('success', 'コメントを投稿しました。');
    }

    /** @test */
    public function 未ログインユーザーには_ログインを促すメッセージが表示される()
    {
        // 未ログイン状態で商品詳細画面を表示
        $response = $this->get("/item/{$this->item->id}");

        $response->assertStatus(200);
        // ログインを促すメッセージが表示される
        $response->assertSee('ログインしてコメントする');
    }
}