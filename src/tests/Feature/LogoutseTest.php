<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ログアウトができるかテスト
     */
    public function test_user_can_logout()
    {
        // ユーザーを作成してログイン状態にする
        $user = User::factory()->create();
        $this->actingAs($user);

        // ログアウトリクエスト
        $response = $this->post('/logout');

        // リダイレクトと認証状態の確認
        $response->assertRedirect('/');
        $this->assertGuest();
    }
}