<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller {

    public function index() {
        return response()->json(Room::all());
    }

    public function show($id) {
        return response()->json(Room::findOrFail($id));
    }

    public function getBySpaceId($spaceId) {
        $rooms = Room::where('Spaces_ID', $spaceId)->get();
        return response()->json($rooms);
    }


    public function store(Request $request) {
        $room = Room::create($request->all());
        return response()->json($room, 201);
    }

    public function update(Request $request, $id) {
        $room = Room::findOrFail($id);
        $room->update($request->all());
        return response()->json($room);
    }

    public function destroy($id) {
        Room::destroy($id);
        return response()->json(['message' => 'Room deleted']);
    }
}
