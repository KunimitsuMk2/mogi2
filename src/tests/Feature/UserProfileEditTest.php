<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserProfileEditTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 初期値が過去の設定値であるかテスト
     */
    public function test_profile_edit_form_shows_previous_values()
    {
        // テスト用のユーザーを作成
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'postal_code' => '1234567',
            'address' => '東京都渋谷区',
            'avatar' => 'avatars/test.jpg'
        ]);

        // プロフィール編集ページにアクセス
        $response = $this->actingAs($user)->get(route('mypage.profile.edit'));

        // フォームに既存の値が入力されていることを確認
        $response->assertStatus(200);
        $response->assertSee('value="テストユーザー"', false);
        $response->assertSee('value="1234567"', false);
        $response->assertSee('value="東京都渋谷区"', false);
        $response->assertSee('avatars/test.jpg');
    }

    /**
     * プロフィール情報を更新できるかテスト
     */
    public function test_user_can_update_profile()
    {
        // ファイルアップロードのモック設定
        Storage::fake('public');
        
        // テスト用のユーザーを作成
        $user = User::factory()->create();
        
        // プロフィール更新リクエスト
        $response = $this->actingAs($user)
                         ->put(route('mypage.profile.update'), [
                             'name' => '更新後の名前',
                             'postal_code' => '9876543',
                             'address' => '大阪府大阪市',
                             'building_name' => '新しいビル',
                             'avatar' => UploadedFile::fake()->image('new_avatar.jpg')
                         ]);

        // リダイレクトとデータベースの確認
        $response->assertRedirect(route('mypage'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => '更新後の名前',
            'postal_code' => '9876543',
            'address' => '大阪府大阪市',
            'building_name' => '新しいビル'
        ]);
        
        // 画像が保存されたことを確認
        $updatedUser = User::find($user->id);
        Storage::disk('public')->assertExists($updatedUser->avatar);
    }
}