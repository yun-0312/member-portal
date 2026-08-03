<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ResetPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = Role::create(['name' => 'medical_staff']);
    }

    //正しいトークンと新しいパスワードでリセットが成功するかテスト
    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create([
            'role_id' => $this->role->id,
            'email' => 'reset-test@example.com',
            'password' => Hash::make('oldpassword123'),
            'approved_by' => null,
        ]);

        // 正しいリセットトークンを生成
        $token = Password::createToken($user);

        $response = $this->postJson('/api/password/reset', [
            'token' => $token,
            'email' => 'reset-test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'パスワードをリセットしました。',
            ]);

        // DBのパスワードが新しいものに更新されていること
        $this->assertTrue(
            Hash::check('newpassword123', $user->fresh()->password)
        );
    }

    //不正なトークンの場合、400エラーになるかテスト
    public function test_password_cannot_be_reset_with_invalid_token(): void
    {
        $user = User::factory()->create([
            'role_id' => $this->role->id,
            'email' => 'reset-test@example.com',
            'password' => Hash::make('oldpassword123'),
            'approved_by' => null,
        ]);

        // デタラメなトークンを指定
        $response = $this->postJson('/api/password/reset', [
            'token' => 'invalid-token-12345',
            'email' => 'reset-test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        // 検証: 400 Bad Request
        $response->assertStatus(400)
            ->assertJson([
                'message' => 'トークンが無効です。',
            ]);

        // 検証: パスワードが変わっていないこと
        $this->assertTrue(
            Hash::check('oldpassword123', $user->fresh()->password)
        );
    }

    //バリデーションエラーのテスト
    public function test_password_reset_validation_fails(): void
    {
        $response = $this->postJson('/api/password/reset', [
            'token' => 'some-token',
            'email' => 'reset-test@example.com',
            'password' => 'short', // 8文字未満
            'password_confirmation' => 'mismatch', // 不一致
        ]);

        // 422 （バリデーション失敗）の検証
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
