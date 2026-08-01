<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalInstitutionStoreRequest;
use App\Http\Requests\MedicalInstitutionUpdateRequest;
use App\Models\MedicalInstitution;

class MedicalInstitutionController extends Controller
{
    public function index(Request $request) {

        $query = MedicalInstitution::query()
            ->visibleTo($request->user())
            ->orderBy('id', 'desc');

        $query = $this->applyFilters($query, $request);

        $institutions = $query
            ->with('representative')
            ->paginate(20)
            ->through(function ($institution) {
                $institution->show_url = "/admin/medical-institutions/{$institution->id}";
                return $institution;
            })
            ->toArray();

        $institutions['store_url'] = "/admin/medical-institutions/create";
        $institutions['export_url'] = "/admin/medical-institutions/export" . '?' . http_build_query($request->query());

        return response()->json($institutions);
    }

    public function show(MedicalInstitution $medicalInstitution) {
        $this->authorize('view', $medicalInstitution);

        $medicalInstitution->load('representative');

        $response = [
            'institution' => $medicalInstitution,
            'users_url' => "/admin/medical-institutions/{$medicalInstitution->id}/users",
        ];

        $currentUser = auth()->user();

        if ($currentUser && $currentUser->isAdmin()) {
            $response['edit_url'] = "/admin/medical-institutions/{$medicalInstitution->id}/edit";
            $response['delete_url'] = "/admin/medical-institutions/{$medicalInstitution->id}";
        }

        return response()->json($response);
    }

    public function store (MedicalInstitutionStoreRequest $request) {
        $this->authorize('create', MedicalInstitution::class);

        $validated = $request->validated();

        $institution = MedicalInstitution::create($validated);

        return response()->json([
            'message' => '医療機関を登録しました',
            'institution' => $institution,
        ], 201);
    }

    public function update(MedicalInstitutionUpdateRequest $request, MedicalInstitution $medicalInstitution) {
        $this->authorize('update', $medicalInstitution);

        $validated = $request->validated();

        $medicalInstitution->update($validated);

        return response()->json([
            'message' => '医療機関を更新しました',
            'institution' => $medicalInstitution,
        ]);
    }

    public function destroy(MedicalInstitution $medicalInstitution) {
        $this->authorize('delete', $medicalInstitution);

        // ユーザーが存在するかチェック
        if ($medicalInstitution->users()->exists()) {
            return response()->json([
                'message' => 'この医療機関にはユーザーが存在するため削除できません。',
                'users' => $medicalInstitution->users()->pluck('name'),
            ], 422);
        }

        $medicalInstitution->delete();

        return response()->json([
            'message' => '医療機関を削除しました',
        ]);
    }

    public function export(Request $request) {
        $this->authorize('view', MedicalInstitution::class);

        $query = MedicalInstitution::orderBy('id', 'asc');
        $query = $this->applyFilters($query, $request);

        $institutions = $query->with('representative')->get();

        // CSV 作成
        $csv = fopen('php://temp', 'r+');

        // 文字化け防止（Excel対応）：UTF-8 BOM を先頭に書き込む
        fwrite($csv, "\xEF\xBB\xBF");

        // ヘッダー行
        fputcsv($csv, [
            'ID',
            '医療機関名',
            '住所',
            '電話番号',
            '代表者',
            '登録日',
        ]);

        // データ行
        foreach ($institutions as $i) {
            fputcsv($csv, [
                $i->id,
                $i->name,
                $i->address,
                $i->phone,
                $i->representative ? $i->representative->name : '',
                $i->created_at->format('Y-m-d'),
            ]);
        }

        rewind($csv);

        $filename = 'medical_institutions_' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($csv) {
            fpassthru($csv);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function users(MedicalInstitution $medicalInstitution) {
        $this->authorize('view', $medicalInstitution);

        $users = $medicalInstitution->users()
            ->with('role', 'medicalInstitution')
            ->orderBy('created_at', 'desc')
            ->get();
        $users->transform(function ($user) {
            $user->show_url = "/admin/users/{$user->id}";
            return $user;
        });

        return response()->json([
            'date' => $users,
        ]);
    }

    private function applyFilters($query, Request $request) {
        // フリーワード検索（keyword）
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);

            $query->where(function ($q) use ($keyword) {
                // 1. 医療機関名
                $q->where('name', 'like', "%{$keyword}%")
                // 2. 郵便番号
                ->orWhere('postcode', 'like', "%{$keyword}%")
                // 3. 住所
                ->orWhere('address', 'like', "%{$keyword}%")
                // 4. 電話番号
                ->orWhere('phone', 'like', "%{$keyword}%")
                // 5. 代表者名（リレーション先：representative）
                ->orWhereHas('representative', function ($repQuery) use ($keyword) {
                    $repQuery->where('name', 'like', "%{$keyword}%");
                });
            });
        }

        return $query;
    }
}
