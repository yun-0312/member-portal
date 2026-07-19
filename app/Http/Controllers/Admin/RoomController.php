<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Http\Requests\RoomStoreRequest;
use App\Http\Requests\RoomUpdateRequest;

class RoomController extends Controller
{
    public function index() {
        $rooms = Room::orderBy('sort_order')->get();

        $rooms->transform(function ($room) {
            $room->show_url = route('admin.rooms.show', $room->id);
            return $room;
        });

        return response()->json([
            'data' => $rooms,
            'store_url' => route('admin.rooms.store'),
        ]);
    }

    public function show(Room $room) {
        return response()->json([
            'room' => [
                'id' => $room->id,
                'name' => $room->name,
                'sort_order' => $room->sort_order,
                'update_url' => route('admin.rooms.update', $room->id),
                'destroy_url' => route('admin.rooms.destroy', $room->id),
                'index_url' => route('admin.rooms.index'),
            ],
        ]);
    }

    public function store(RoomStoreRequest $request) {
        $validated = $request->validated();

        $room = Room::create($validated);

        return response()->json([
            'message' => '会議室を登録しました',
            'room' => $room,
        ]);
    }

    public function update(RoomUpdateRequest $request, Room $room) {
        $validated = $request->validated();

        $room->update($validated);

        return response()->json([
            'message' => '会議室を更新しました',
            'room' => $room,
        ]);
    }

    public function destroy(Room $room) {
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
