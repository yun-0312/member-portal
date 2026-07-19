<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request) {
        $query = User::query()->with(['role', 'medicalInstitution', 'approvedBy']);

        $query = $this->applyUserFilters($query, $request);

        $perPage = $request->input('per_page', 30);

        $users = $query->orderBy('id', 'desc')->paginate($perPage);

        $users->getCollection()->transform(function ($user) {
            $user->show_url = route('admin.users.show', $user->id);
            return $user;
        });

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
            'export_url' => route('admin.users.export') . '?' . http_build_query($request->query()),
            'pending_url' => route('admin.users.pending'),
        ]);
    }

    public function show(User  $user) {
        return response()->json([
            'user' => $user->load(['role', 'medicalInstitution', 'approvedBy']),
            'update_url' => route('admin.users.update', $user->id),
            'delete_url' => route('admin.users.destroy', $user->id),
        ]);
    }

    public function store(UserStoreRequest $request) {
        $validated = $request->validated();

        $validated['password'] = Hash::make($validated['password']);

        $validated['approved_at'] = now();
        $validated['approved_by'] = auth()->id();

        $user = User::create($validated);

        return response()->json([
            'message' => 'ユーザーを作成しました',
            'user' => $user,
        ], 201);
    }

    public function update(UserUpdateRequest $request, User $user) {
        $validated = $request->validated();

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'ユーザーを更新しました',
            'user' => $user,
        ]);
    }

    public function destroy(User $user) {
        $user->delete();

        return response()->json([
            'message' => 'ユーザーを削除しました',
        ]);
    }

    public function export(Request $request) {
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
        $users = User::with(['role', 'medicalInstitution'])
            ->whereNull('approved_at')
            ->whereNull('approved_by')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'data' => $users,
        ]);
    }

    public function approve(User $user) {
        $user->update([
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'status' => UserStatus::Active,
        ]);

        return response()->json([
            'message' => 'ユーザーを承認しました',
            'user' => $user,
        ]);
    }

    public function reject(User $user) {
        if ($user->status === UserStatus::Active) {
            return response()->json([
                'message' => '承認済みユーザーは却下できません',
            ], 422);
        }

        $user->update([
            'approved_at' => null,
            'approved_by' => null,
            'status' => UserStatus::Rejected,
        ]);

        return response()->json([
            'message' => 'ユーザーを却下しました',
            'user' => $user,
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
