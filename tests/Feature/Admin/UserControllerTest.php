<?php

namespace Tests\Feature\Admin;

use App\Enums\UserStatus;
use App\Models\MedicalInstitution;
use App\Models\Role;
use App\Models\User;
use App\Notifications\UserRegisteredByAdmin;
use App\Notifications\VerifyNewEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Models\Permission;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Role $adminRole;
    protected Role $staffRole;

    protected function setUp(): void
    {
        parent::setUp();

        // ロールの作成
        $this->adminRole = Role::create(['name' => 'admin']);
        $this->staffRole = Role::create(['name' => 'member']);

        // ルーティングで使用されているパーミッションの作成
        $permissions = [
            'user.create',
            'user.update',
            'user.delete',
            'medical_institution.update',
        ];

        $permissionIds = [];
        foreach ($permissions as $permName) {
            $perm = Permission::create(['name' => $permName]);
            $permissionIds[] = $perm->id;
        }
        //adminRole にパーミッションを紐付ける
        $this->adminRole->permissions()->attach($permissionIds);

        // 管理者ユーザーの作成
        $user = $this->adminUser = User::factory()->create([
            'role_id' => $this->adminRole->id,
            'approved_by' => null,
        ]);

        $this->adminUser = $user->fresh(['role']);
    }

    //ユーザー一覧が取得できるかテスト
    public function test_admin_can_view_user_list(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/admin/users');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'export_url', 'store_url']);
    }

    //管理者によるユーザー作成と通知送信のテスト
    public function test_admin_can_create_user(): void
    {
        Notification::fake();

        $userData = [
            'name' => '新規スタッフ',
            'email' => 'newstaff@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $this->staffRole->id,
            'status' => UserStatus::Active->value,
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/admin/users', $userData);

        $response->assertStatus(201)
            ->assertJson(['message' => 'ユーザーを作成しました']);

        // DBに作成され、即時承認＆認証済みになっているか
        $createdUser = User::where('email', 'newstaff@example.com')->first();
        $this->assertNotNull($createdUser);
        $this->assertNotNull($createdUser->approved_at);
        $this->assertNotNull($createdUser->email_verified_at);

        // 通知が送信されたか
        Notification::assertSentTo($createdUser, UserRegisteredByAdmin::class);
    }

    //承認待ち（Pending）から承認済み（Active）への更新時に代理承認が行われるかテスト
    public function test_updating_status_to_active_sets_approval_info(): void
    {
        $pendingUser = User::factory()->create([
            'role_id' => $this->staffRole->id,
            'status' => UserStatus::Pending->value,
            'approved_at' => null,
            'approved_by' => null,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->putJson("/api/admin/users/{$pendingUser->id}", [
                'name' => $pendingUser->name,
                'status' => UserStatus::Active->value,
            ]);

        $response->assertStatus(200);

        // approved_at と approved_by が自動セットされたか検証
        $updatedUser = $pendingUser->fresh();
        $this->assertNotNull($updatedUser->approved_at);
        $this->assertEquals($this->adminUser->id, $updatedUser->approved_by);
    }

    //メールアドレス変更時に VerifyNewEmail 通知が送信されるかテスト
    public function test_updating_email_sends_verification_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role_id' => $this->staffRole->id,
            'email' => 'old@example.com',
            'approved_by' => null,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->putJson("/api/admin/users/{$user->id}", [
                'name' => $user->name,
                'email' => 'new@example.com',
            ]);

        $response->assertStatus(200);

        // 新メールアドレス宛に VerifyNewEmail 通知が飛んだか
        Notification::assertSentTo($user, VerifyNewEmail::class);
    }

    //医療機関の代表者に設定されているユーザーは削除できない（422）テスト
    public function test_cannot_delete_representative_user(): void
    {
        $repUser = User::factory()->create([
            'role_id' => $this->staffRole->id,
            'approved_by' => null,
        ]);

        // 医療機関を作成し、このユーザーを代表者に設定
        MedicalInstitution::factory()->create([
            'representative_user_id' => $repUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/api/admin/users/{$repUser->id}");

        // 422 エラーになりメッセージが返ること
        $response->assertStatus(422)
            ->assertJson([
                'message' => 'このユーザーは医療機関の代表者に設定されているため削除できません。代表者を変更してから削除してください。',
            ]);

        // DBから削除されていないこと
        $this->assertDatabaseHas('users', ['id' => $repUser->id]);
    }

    //CSVエクスポートが動作するかテスト
    public function test_admin_can_export_users_csv(): void
    {
        $this->withoutExceptionHandling();
        $response = $this->actingAs($this->adminUser)
            ->get('/api/admin/users/export');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}