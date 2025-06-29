<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserLoginTest extends TestCase
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
    public function メールアドレスが入力されていない場合_バリデーションメッセージが表示される()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertSessionHasErrorsIn('default', [
            'email' => 'メールアドレスを入力してください'
        ]);
    }

    /** @test */
    public function パスワードが入力されていない場合_バリデーションメッセージが表示される()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
        $response->assertSessionHasErrorsIn('default', [
            'password' => 'パスワードを入力してください'
        ]);
    }

    /** @test */
    public function 入力情報が間違っている場合_バリデーションメッセージが表示される()
    {
        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertSessionHasErrorsIn('default', [
            'email' => 'ログイン情報が登録されていません'
        ]);
    }

    /** @test */
    public function 正しい情報が入力された場合_ログイン処理が実行される()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // ログインが成功することを確認
        $this->assertAuthenticatedAs($this->user);
        
        // トップページにリダイレクトされることを確認
        $response->assertRedirect('/');
    }

    /** @test */
    public function ログイン画面が正常に表示される()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
        $response->assertSee('ログイン');
        $response->assertSee('メールアドレス');
        $response->assertSee('パスワード');
    }

    /** @test */
    public function ログイン画面から会員登録画面に遷移できる()
    {
        $response = $this->get('/login');

        $response->assertSee('会員登録はこちら');
        
        // 会員登録リンクをクリック
        $registerResponse = $this->get('/register');
        $registerResponse->assertStatus(200);
        $registerResponse->assertViewIs('auth.register');
    }

    /** @test */
    public function 会員登録画面からログイン画面に遷移できる()
    {
        $response = $this->get('/register');

        $response->assertSee('ログインはこちら');
        
        // ログインリンクをクリック
        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);
        $loginResponse->assertViewIs('auth.login');
    }
}