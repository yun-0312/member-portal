<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use App\Models\Role;
use App\Models\User;

class ForgotPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = Role::create(['name' => 'medical_staff']);
    }

    //存在するメールアドレスを指定した場合、パスワードリセットメールが送信されるかテスト
    public function test_send_reset_link_email_success(): void
    {
        //  メールの実際の発行をスキップする
        Notification::fake();

        $user = User::factory()->create([
            'role_id' => $this->role->id,
            'email' => 'registered@example.com',
            'approved_by' => null,
        ]);

        // リセットメール送信APIのコール
        $response = $this->postJson('/api/password/email', [
            'email' => 'registered@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'パスワードリセット用のメールを送信しました。',
            ]);

        Notification::assertSentTo(
            $user,
            ResetPasswordNotification::class
        );
    }

    //存在しないメールアドレスを指定した場合、404エラーになるかテスト
    public function test_send_reset_link_email_user_not_found(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/password/email', [
            'email' => 'nonexistent@example.com',
        ]);

        // 404 Not Found の検証
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'メールアドレスが見つかりません。',
            ]);

        // 通知がどこにも送られていないことを検証
        Notification::assertNothingSent();
    }

    //バリデーションエラーのテスト
    public function test_send_reset_link_email_validation_fails(): void
    {
        $response = $this->postJson('/api/password/email', [
            'email' => 'invalid-email-format',
        ]);

        // 422 （バリデーション失敗）の検証
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
