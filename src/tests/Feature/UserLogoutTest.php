<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserLogoutTest extends TestCase
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
    }

    /** @test */
    public function ログアウトができる()
    {
        // ユーザーをログインさせる
        $this->actingAs($this->user);
        
        // ログイン状態を確認
        $this->assertAuthenticated();
        
        // ログアウト処理を実行
        $response = $this->post('/logout');
        
        // ログアウトが成功することを確認
        $this->assertGuest();
        
        // トップページにリダイレクトされることを確認
        $response->assertRedirect('/');
    }

    /** @test */
    public function ログイン済みユーザーのヘッダーにログアウトボタンが表示される()
    {
        // ユーザーをログインさせる
        $this->actingAs($this->user);
        
        // トップページを表示
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('ログアウト');
    }

    /** @test */
    public function 未ログインユーザーのヘッダーにはログインボタンが表示される()
    {
        // 未ログイン状態でトップページを表示
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('ログイン');
        $response->assertDontSee('ログアウト');
    }

    /** @test */
    public function ログアウト後に認証が必要なページにアクセスするとログイン画面にリダイレクトされる()
    {
        // ユーザーをログインさせる
        $this->actingAs($this->user);
        
        // ログアウト処理を実行
        $this->post('/logout');
        
        // 認証が必要なページ（マイページ）にアクセス
        $response = $this->get('/mypage');
        
        // ログイン画面にリダイレクトされることを確認
        $response->assertRedirect('/login');
    }
}