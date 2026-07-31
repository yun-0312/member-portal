<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;
use App\Models\MedicalInstitution;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use Illuminate\Support\Facades\Hash;
use App\Notifications\UserRegisteredByAdmin;


class UserController extends Controller
{
    public function index(Request $request) {
        $query = User::query()
            ->visibleTo($request->user())
            ->with(['role', 'medicalInstitution']);
        $query = $this->applyUserFilters($query, $request);

        $perPage = $request->input('per_page', 30);

        $users = $query->orderBy('id', 'desc')
            ->paginate($perPage)
            ->through(function ($user) {
                $user->show_url = "/admin/users/{$user->id}";
                return $user;
            })
            ->toArray();

        $users['export_url'] = '/admin/users/export' . '?' . http_build_query($request->query());
        $users['pending_url'] = '/admin/users/pending';
        $users['store_url'] = '/admin/users/create';

        return response()->json($users);
    }

    public function show(User $user) {
        $this->authorize('view', $user);

        $currentUser = auth()->user();

        $response = [
            'user' => $user->load(['role', 'medicalInstitution', 'approvedBy']),
        ];

        // admin の場合のみキーを追加
        if ($currentUser && $currentUser->isAdmin()) {
            $response['update_url'] = "/admin/users/{$user->id}/edit";
            $response['delete_url'] = "/admin/users/{$user->id}";
        }

        return response()->json($response);
    }

    public function store(UserStoreRequest $request) {
        $this->authorize('create', User::class);

        $validated = $request->validated();

        $validated['password'] = Hash::make($validated['password']);

        $validated['approved_at'] = now();
        $validated['approved_by'] = auth()->id();
        $validated['email_verified_at'] = now();

        $user = User::create($validated);
        // 登録完了メールを送る
        $user->notify(new UserRegisteredByAdmin());

        return response()->json([
            'message' => 'ユーザーを作成しました',
            'user' => $user,
        ], 201);
    }

    public function update(UserUpdateRequest $request, User $user) {
        $this->authorize('update', $user);

        $validated = $request->validated();

        if (!auth()->user()->isAdmin()) {
            // admin 以外は name と email だけ許可
            $validated = array_intersect_key($validated, [
                'name' => true,
                'email' => true,
            ]);
        }

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        //mail変更時の処理
        $isEmailChanged = isset($validated['email']) && $validated['email'] !== $user->email;

        if ($isEmailChanged) {
            $newEmail = $validated['email'];

            unset($validated['email']);
            $user->notify(new \App\Notifications\VerifyNewEmail($newEmail));
        }

        //代理承認の判定
        $requestedStatus = $request->input('status');
        if ($requestedStatus !== null) {
            // 現在のステータスが Pending から Active に変更される場合
            $isPendingToActive =
                ($user->status === UserStatus::Pending || $user->status == UserStatus::Pending->value) &&
                ($requestedStatus == UserStatus::Active || $requestedStatus == UserStatus::Active->value);

            if ($isPendingToActive) {
                $validated['approved_at'] = now();
                $validated['approved_by'] = auth()->id();
            }
        }

        DB::transaction(function () use ($user, $validated) {
            $user->update($validated);
        });

        $message = 'ユーザーを更新しました';
        if ($isEmailChanged) {
            $message .= '。新しいメールアドレスに確認メールを送信しました。認証を完了するまでメールアドレスは変更されません。';
        }

        return response()->json([
            'message' => $message,
            'user' => $user->fresh(),
        ]);
    }

    public function options() {
        $this->authorize('view', User::class);

        return response()->json([
            'roles' => Role::select('id', 'name')->get(),
            'medical_institutions' => MedicalInstitution::select('id', 'name')->get(),
        ]);
    }

    public function destroy(User $user) {
        $this->authorize('delete', $user);

        $isRepresentative = MedicalInstitution::where('representative_user_id', $user->id)->exists();
        if ($isRepresentative) {
        return response()->json([
            'message' => 'このユーザーは医療機関の代表者に設定されているため削除できません。代表者を変更してから削除してください。',
        ], 422);
    }

        $user->delete();

        return response()->json([
            'message' => 'ユーザーを削除しました',
        ]);
    }

    public function export(Request $request) {
        $this->authorize('view', User::class);

        $query = User::with(['role', 'medicalInstitution', 'approvedBy']);
        $query = $this->applyUserFilters($query, $request);

        $users = $query->orderBy('id')->get();

        $csv = fopen('php://temp', 'r+');
        fwrite($csv, "\xEF\xBB\xBF");

        // ヘッダー行
        fputcsv($csv, [
            'ID',
            '名前',
            'メール',
            'ロール名',
            'ステータス',
            '医療機関名',
            '承認日',
            '承認者',
            '作成日',
            '削除日'
        ]);

        foreach ($users as $user) {
            fputcsv($csv, [
                $user->id,
                $user->name,
                $user->email,
                optional($user->role)->name,
                $user->status,
                optional($user->medicalInstitution)->name,
                $user->approved_at,
                optional($user->approvedBy)->name,
                $user->created_at,
                $user->delete_at,
            ]);
        }

        rewind($csv);

            $filename = 'users_' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($csv) {
            fpassthru($csv);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

        public function pending(Request $request) {
            $users = User::query()
                ->visibleTo($request->user())
                ->with(['role', 'medicalInstitution.representative'])
                ->whereNull('approved_at')
                ->whereNull('approved_by')
                ->orderBy('created_at')
                ->get();

            return response()->json([
                'data' => $users,
            ]);
        }

    private function applyUserFilters($query, Request $request) {
        if ($request->filled('keyword')) {
            $rawKeyword = trim($request->keyword);
            $keyword = '%' . trim($request->keyword) . '%';

            $query->where(function ($q) use ($keyword, $rawKeyword) {
                // 1. ユーザー自身の氏名・メールアドレスで検索
                $q->where('name', 'like', $keyword)
                ->orWhere('email', 'like', $keyword)

                // 2. 所属医療機関の「名前」「住所」で検索
                ->orWhereHas('medicalInstitution', function ($qMed) use ($keyword) {
                    $qMed->where('name', 'like', $keyword)
                        ->orWhere('address', 'like', $keyword);
                })

                // 3. ロール（権限）の「名前」で検索
                ->orWhereHas('role', function ($qRole) use ($keyword, $rawKeyword) {
                    $qRole->where('name', 'like', $keyword);

                    if (strcasecmp($rawKeyword, 'STAFF') === 0) {
                        $qRole->where('name', 'not like', '%MEDICAL%');
                    }
                });
            });
        }

        return $query;
    }

}
