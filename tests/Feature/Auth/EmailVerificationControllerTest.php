<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class EmailVerificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = Role::create(['name' => 'medical_staff']);

    }

    //正しい署名付きURLでアクセスした場合、メール認証が成功するかテスト
    public function test_email_can_be_verified(): void
    {
        // メール未認証のユーザーを作成
        $user = User::factory()->create([
            'role_id' => $this->role->id,
            'email_verified_at' => null,
            'approved_by' => null,
        ]);

        // 正しい署名付きURL（Signed URL）を生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $response = $this->getJson($verificationUrl);

        $response->assertStatus(302)
            ->assertRedirect('http://localhost/?verified=success_register');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    //ハッシュ値（hash）が不正な場合、403エラーになるかテスト
    public function test_email_cannot_be_verified_with_invalid_hash(): void
    {
        $user = User::factory()->create([
            'role_id' => $this->role->id,
            'email_verified_at' => null,
            'approved_by' => null,
        ]);

        // 不正なハッシュ値を指定したURLを作成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1('invalid-email@example.com'),
            ]
        );

        $response = $this->getJson($verificationUrl);

        $response->assertStatus(302);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    //既に認証済みのユーザーがアクセスした場合のテスト
    public function test_already_verified_email_returns_message(): void
    {
        // 既に認証済みのユーザーを作成
        $user = User::factory()->create([
            'role_id' => $this->role->id,
            'email_verified_at' => now(),
            'approved_by' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $response = $this->getJson($verificationUrl);

        $response->assertStatus(302);
    }
}
