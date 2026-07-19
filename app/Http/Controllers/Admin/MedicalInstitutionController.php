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
        $query = MedicalInstitution::orderBy('id', 'desc');
        $query = $this->applyFilters($query, $request);

        $institutions = $query->paginate(20);

        $institutions->getCollection()->transform(function ($institution) {
            $institution->show_url = route('medical-institutions.show', $institution->id);
            $institution->update_url = route('admin.medical-institutions.update', $institution->id);
            $institution->destroy_url = route('admin.medical-institutions.destroy', $institution->id);
            return $institution;
        });

        return response()->json([
            'data' => $institutions,
            'store_url' => route('admin.medical-institutions.store'),
            'export_url' => route('admin.medical-institutions.export') . '?' . http_build_query($request->query()),
        ]);
    }

    public function store (MedicalInstitutionStoreRequest $request) {
        $validated = $request->validated();

        $institution = MedicalInstitution::create($validated);

        return response()->json([
            'message' => '医療機関を登録しました',
            'institution' => $institution,
        ]);
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
        $query = MedicalInstitution::orderBy('id', 'desc');
        $query = $this->applyFilters($query, $request);

        $institutions = $query->get();

        // CSV 作成
        $csv = fopen('php://temp', 'r+');

        // ヘッダー行
        fputcsv($csv, [
            'ID',
            '医療機関名',
            '住所',
            '電話番号',
            '登録日',
        ]);

        // データ行
        foreach ($institutions as $i) {
            fputcsv($csv, [
                $i->id,
                $i->name,
                $i->address,
                $i->phone,
                $i->created_at->format('Y-m-d'),
            ]);
        }

        rewind($csv);

        return response()->streamDownload(function () use ($csv) {
            fpassthru($csv);
        }, 'medical_institutions.csv');
    }

    public function users(Request $request, MedicalInstitution $medicalInstitution) {
        $this->authorize('view', $medicalInstitution);

        $query = $medicalInstitution->users()->orderBy('id', 'desc');

        if ($request->filled('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', "%{$request->email}%");
        }

        return $query->get();
    }

    private function applyFilters($query, Request $request) {
        // キーワード検索
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('address', 'like', "%{$keyword}%")
                ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        // 医療機関名
        if ($request->filled('name')) {
            $name = trim($request->name);
            $query->where('name', 'like', "%{$name}%");
        }

        // 住所
        if ($request->filled('address')) {
            $address = trim($request->address);
            $query->where('address', 'like', "%{$address}%");
        }

        // 電話番号
        if ($request->filled('phone')) {
            $phone = trim($request->phone);
            $query->where('phone', 'like', "%{$phone}%");
        }

        // 期間指定
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        return $query;
    }
}
