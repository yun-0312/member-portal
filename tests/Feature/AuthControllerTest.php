<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Role $role;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = Role::create(['name' => 'medical_staff']);

        // 承認者用のユーザー
        $this->adminUser = User::factory()->create([
            'role_id' => $this->role->id,
            'approved_by' => null,
        ]);
    }

    //未承認のユーザーはログインできず403エラーが返るかテスト
    public function test_unapproved_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'pending@example.com',
            'password' => bcrypt('password123'),
            'role_id' => $this->role->id,
            'email_verified_at' => now(),
            'approved_at' => null,
            'approved_by' => null,
            'status' => UserStatus::Pending,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'pending@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'アカウントがまだ承認されていません。管理者の承認をお待ちください。',
            ]);
    }

    //メール未認証のユーザーはログインできず 403 エラーになるかテスト
    public function test_unverified_email_user_cannot_login(): void
    {
        User::factory()->create([
            'role_id' => $this->role->id,
            'email' => 'unverified@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => null,
            'approved_at' => now(),
            'approved_by' => $this->adminUser->id,
            'status' => UserStatus::Active,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'unverified@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'メール認証が完了していません',
            ]);
    }

    //承認済みのユーザーはログインに成功するかテスト
    public function test_approved_user_can_login(): void
    {
        User::factory()->create([
            'email' => 'active@example.com',
            'password' => bcrypt('password123'),
            'role_id' => $this->role->id,
            'email_verified_at' => now(),
            'approved_at' => now(),
            'approved_by' => $this->adminUser->id,
            'status' => UserStatus::Active,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'active@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'user', 'token']);
    }

    //パスワードが違う場合はログインに失敗するかテスト
    public function test_login_fails_with_invalid_password(): void
    {
        User::factory()->create([
            'role_id' => $this->role->id,
            'email' => 'active@example.com',
            'password' => bcrypt('correct-password'),
            'email_verified_at' => now(),
            'approved_at' => now(),
            'approved_by' => $this->adminUser->id,
            'status' => UserStatus::Active,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'active@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    //ログイン中のユーザーがログアウトできるかテスト
    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create([
            'role_id' => $this->role->id,
            'email' => 'logout-test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'approved_at' => now(),
            'approved_by' => $this->adminUser->id,
            'status' => UserStatus::Active,
        ]);

        // Sanctum の actingAs() を使って「ログイン済み状態」をシミュレートし、POSTリクエストを送る
        $response = $this->actingAs($user)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'ログアウトしました',
            ]);

        // (オプショナル) トークンが削除されたか確認
        $this->assertCount(0, $user->tokens);
    }

    //未ログイン状態でログアウトAPIを叩いた場合 401 Unauthorized になるかテスト
    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    //新規会員登録ができるかテスト
    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register-medical-staff', [
            'name' => 'テスト太郎',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $this->role->id,
        ]);

        $response->assertStatus(201); // または 200

        // データベースに「未承認」状態で保存されているかチェック
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'status' => UserStatus::Pending->value,
        ]);
    }

}
