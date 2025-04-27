<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ログインユーザーがコメントを送信できるかテスト
     */
    public function test_logged_in_user_can_comment()
    {
        // ユーザーと商品を作成
        $user = User::factory()->create();
        $item = Item::factory()->create();

        // コメント送信リクエスト
        $response = $this->actingAs($user)
                         ->post(route('comments.store', $item), [
                             'content' => 'これはテストコメントです'
                         ]);

        // リダイレクトとデータベースの確認
        $response->assertRedirect(route('products.item', $item));
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'content' => 'これはテストコメントです'
        ]);
    }

    /**
     * 未ログインユーザーがコメントを送信できないことをテスト
     */
    public function test_guest_cannot_comment()
    {
        // 商品を作成
        $item = Item::factory()->create();

        // コメント送信リクエスト
        $response = $this->post(route('comments.store', $item), [
            'content' => 'これはテストコメントです'
        ]);

        // ログインページへリダイレクトされることを確認
        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('comments', [
            'item_id' => $item->id,
            'content' => 'これはテストコメントです'
        ]);
    }

    /**
     * コメントが入力されていない場合のバリデーションテスト
     */
    public function test_comment_content_is_required()
    {
        // ユーザーと商品を作成
        $user = User::factory()->create();
        $item = Item::factory()->create();

        // 空のコメント送信リクエスト
        $response = $this->actingAs($user)
                         ->post(route('comments.store', $item), [
                             'content' => ''
                         ]);

        // バリデーションエラーの確認
        $response->assertSessionHasErrors('content');
    }

    /**
     * コメントが255文字を超える場合のバリデーションテスト
     */
    public function test_comment_max_length()
    {
        // ユーザーと商品を作成
        $user = User::factory()->create();
        $item = Item::factory()->create();

        // 256文字のコメント送信リクエスト
        $longComment = str_repeat('あ', 256);
        $response = $this->actingAs($user)
                         ->post(route('comments.store', $item), [
                             'content' => $longComment
                         ]);

        // バリデーションエラーの確認
        $response->assertSessionHasErrors('content');
    }
}