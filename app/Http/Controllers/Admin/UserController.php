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
        $this->authorize('view', User::class);

        $query = User::query()->with(['role', 'medicalInstitution']);
        $query = $this->applyUserFilters($query, $request);

        $perPage = $request->input('per_page', 30);

        $users = $query->orderBy('id', 'desc')
            ->paginate($perPage)
            ->through(function ($user) {
                $user->show_url = route('admin.users.show', $user->id);
                return $user;
            })
            ->toArray();

        $users['export_url'] = route('admin.users.export') . '?' . http_build_query($request->query());
        $users['pending_url'] = route('admin.users.pending');

        return response()->json($users);
    }

    public function show(User  $user) {
        $this->authorize('view', $user);

        return response()->json([
            'user' => $user->load(['role', 'medicalInstitution', 'approvedBy']),
            'update_url' => route('admin.users.update', $user->id),
            'delete_url' => route('admin.users.destroy', $user->id),
        ]);
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

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        //代理承認の判定
        if ($request->has('status') && $request->status === UserStatus::Active) {
            if ($user->status === UserStatus::Pending) {
                $validated['approved_at'] = now();
                $validated['approved_by'] = auth()->id();
            }
        }

        DB::transaction(function () use ($user, $validated) {
            $user->update($validated);
        });

        return response()->json([
            'message' => 'ユーザーを更新しました',
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
            ]);
        }

        rewind($csv);

        return response()->streamDownload(function () use ($csv) {
            fpassthru($csv);
        }, 'users.csv');
    }

    public function pending() {
        $this->authorize('view', User::class);

        $users = User::with(['role', 'medicalInstitution'])
            ->whereNull('approved_at')
            ->whereNull('approved_by')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'data' => $users,
        ]);
    }

    private function applyUserFilters($query, Request $request) {
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('medical_institution_id')) {
            $query->where('medical_institution_id', $request->medical_institution_id);
        }

        if ($request->filled('approved_by')) {
            $query->where('approved_by', $request->approved_by);
        }

        return $query;
    }

}
