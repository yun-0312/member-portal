<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminMasterController;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Http\Requests\RoomStoreRequest;
use App\Http\Requests\RoomUpdateRequest;

class RoomController extends BaseAdminMasterController
{
    protected string $modelClass = Room::class;
    protected string $routePrefix = 'rooms';

    protected string $storeRequestClass = RoomStoreRequest::class;
    protected string $updateRequestClass = RoomUpdateRequest::class;

    protected string $sortColumn = 'sort_order';

    protected function beforeStore(array $validated, Request $request): array {
        // sort_order が未入力なら自動採番
        if (empty($validated['sort_order'])) {
            $validated['sort_order'] = Room::getNextAvailableSortOrder();
        }

        return $validated;
    }

    //削除時の制約チェックのためオーバーライド
    public function destroy($id) {
        $room = $this->findModel($id);

        if ($room->schedules()->exists()) {
            return response()->json([
                'message' => 'この会議室にはスケジュールが存在するため削除できません。',
            ], 422);
        }
        $room->delete();

        return response()->json([
            'message' => '会議室を削除しました',
        ]);
    }
}
