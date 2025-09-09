<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserPosition;
use App\Models\SpacesMember;
use App\Models\Room;
use Carbon\Carbon;
use App\Models\Chat;
use App\Models\ChatsMember;


class UserPositionController extends Controller
{

    // GET - Semua posisi user yang berada di ruangan yang sama dengan SpacesMember_ID tertentu
    public function getSameRoomPositions($SpacesMember_ID)
    {
        $userPosition = UserPosition::where('SpacesMember_ID', $SpacesMember_ID)->first();

        if (!$userPosition) {
            return response()->json(['message' => 'User position not found for given SpacesMember_ID.'], 404);
        }

        $sameRoomPositions = UserPosition::with('space_member.user')
            ->where('Room_ID', $userPosition->Room_ID)
            ->where('SpacesMember_ID', '!=', $SpacesMember_ID)
            ->get();

        // Format hasil agar menyertakan Username secara eksplisit
        $formatted = $sameRoomPositions->map(function ($pos) {
            return [
                'UserPosition_ID' => $pos->UserPosition_ID,
                'SpacesMember_ID' => $pos->SpacesMember_ID,
                'Room_ID' => $pos->Room_ID,
                'PosX' => $pos->PosX,
                'PosY' => $pos->PosY,
                'Username' => optional($pos->space_member->user)->Username, // gunakan optional untuk menghindari null error
            ];
        });

        return response()->json($formatted);
    }


    
    // 1. POST - Masuk ke ruangan
    public function enter($SpacesMember_ID)
    {
        $member = SpacesMember::find($SpacesMember_ID);
        if (!$member) {
            return response()->json(['message' => 'SpacesMember not found.'], 404);
        }

        $room = Room::where('Room_Name', 'lantai1')
                    ->where('Spaces_ID', $member->Spaces_ID)
                    ->first();

        if (!$room) {
            return response()->json(['message' => 'No room named lantai1 found in this space.'], 404);
        }

        $position = UserPosition::create([
            'SpacesMember_ID' => $member->SpacesMember_ID,
            'Room_ID' => $room->Room_ID,
            'ChatArea_ID' => null,
            'PosX' => 5,
            'PosY' => 5,
            'FacingDirection' => 'down',
            'LastUpdated' => Carbon::now(),
        ]);

        $member->Online = true;
        $member->save();

        return response()->json(['message' => 'User entered room.', 'position' => $position], 201);
    }



    // 2. PUT - Ganti Room (asal space sama)
    public function moveRoom(Request $request, $SpacesMember_ID)
    {
        $position = UserPosition::where('SpacesMember_ID', $SpacesMember_ID)->first();
        if (!$position) {
            return response()->json(['message' => 'Position not found for given SpacesMember_ID.'], 404);
        }

        $currentRoom = Room::find($position->Room_ID);
        if (!$currentRoom) {
            return response()->json(['message' => 'Current room not found.'], 404);
        }

        $targetRoomName = $currentRoom->Room_Name === 'lantai1' ? 'lantai2' : 'lantai1';

        $newRoom = Room::where('Room_Name', $targetRoomName)
                    ->where('Spaces_ID', $currentRoom->Spaces_ID)
                    ->first();

        if (!$newRoom) {
            return response()->json(['message' => "Target room '$targetRoomName' not found in this space."], 404);
        }

        $position->Room_ID = $newRoom->Room_ID;
        $position->LastUpdated = Carbon::now();
        $position->save();

        return response()->json(['message' => "Moved to $targetRoomName.", 'position' => $position]);
    }


    // 7. PUT - Update seluruh isi UserPosition berdasarkan SpacesMember_ID
    public function update(Request $request, $SpacesMember_ID)
    {
        $position = UserPosition::where('SpacesMember_ID', $SpacesMember_ID)->first();
        if (!$position) {
            return response()->json(['message' => 'Position not found for given SpacesMember_ID.'], 404);
        }

        $this->validate($request, [
            'ChatArea_ID' => 'nullable|exists:chat_areas,ChatArea_ID',
            'PosX' => 'required|integer',
            'PosY' => 'required|integer',
            'FacingDirection' => 'required|in:up,down,left,right',
        ]);

        $position->ChatArea_ID = $request->input('ChatArea_ID');
        $position->PosX = $request->input('PosX');
        $position->PosY = $request->input('PosY');
        $position->FacingDirection = $request->input('FacingDirection');
        $position->LastUpdated = Carbon::now();
        $position->save();

        return response()->json(['message' => 'UserPosition updated.', 'position' => $position]);
    }




    // 3. PUT - Update posisi ke arah tertentu
    public function move(Request $request, $SpacesMember_ID)
    {
        $position = UserPosition::where('SpacesMember_ID', $SpacesMember_ID)->first();
        if (!$position) {
            return response()->json(['message' => 'Position not found for given SpacesMember_ID.'], 404);
        }
        $direction = $request->input('direction');
        $deltaX = 0;
        $deltaY = 0;

        switch ($direction) {
            case 'up':
                $deltaY = -1;
                $facing = 'up';
                break;
            case 'down':
                $deltaY = 1;
                $facing = 'down';
                break;
            case 'left':
                $deltaX = -1;
                $facing = 'left';
                break;
            case 'right':
                $deltaX = 1;
                $facing = 'right';
                break;
            default:
                return response()->json(['message' => 'Invalid direction.'], 422);
        }

        $position->PosX += $deltaX;
        $position->PosY += $deltaY;
        $position->FacingDirection = $facing;
        $position->LastUpdated = Carbon::now();
        $position->save();

        return response()->json($position);

        // return response()->json(['message' => 'Position updated.', 'position' => $position]);
    }

    // 4. GET - Semua posisi
    public function index()
    {
        return response()->json(UserPosition::all());
    }

    // 5. GET - By ID
    public function show($SpacesMember_ID)
    {
        $position = UserPosition::where('SpacesMember_ID', $SpacesMember_ID)->first();
        if (!$position) {
            return response()->json(['message' => 'Position not found for given SpacesMember_ID.'], 404);
        }

        return response()->json($position);
    }

    public function filter(Request $request)
    {
        $query = UserPosition::query();

        if ($request->has('Room_ID')) {
            $query->where('Room_ID', $request->input('Room_ID'));
        }

        if ($request->has('Spaces_ID')) {
            $query->whereHas('space_member', function ($q) use ($request) {
                $q->where('Spaces_ID', $request->input('Spaces_ID'));
            });
        }

        if ($request->has('ChatArea_ID')) {
            $query->where('ChatArea_ID', $request->input('ChatArea_ID'));
        }

        return response()->json($query->get());
    }


    // 6. DELETE
    public function leave($SpacesMember_ID)
    {
        $position = UserPosition::where('SpacesMember_ID', $SpacesMember_ID)->first();
        if (!$position) return response()->json(['message' => 'Position not found.'], 404);

        $member = SpacesMember::find($SpacesMember_ID);
        if ($member) {
            $member->Online = false;
            $member->save();
        }

        $position->delete();
        return response()->json(['message' => 'User left the room.']);
    }

    public function enterAreaChat($SpacesMember_ID, $ChatArea_ID)
    {
        $position = UserPosition::where('SpacesMember_ID', $SpacesMember_ID)->first();
        if (!$position) {
            return response()->json(['message' => 'User position not found.'], 404);
        }

        $member = SpacesMember::find($SpacesMember_ID);
        if (!$member) {
            return response()->json(['message' => 'SpacesMember not found.'], 404);
        }

        // Update ChatArea_ID pada posisi
        $position->ChatArea_ID = $ChatArea_ID;
        $position->LastUpdated = Carbon::now();
        $position->save();

        // Ambil chat yang sesuai
        $chat = Chat::where('ChatArea_ID', $ChatArea_ID)->first();
        if (!$chat) {
            return response()->json(['message' => 'Chat not found for this area.'], 404);
        }

        // Cek apakah user sudah tergabung, jika belum tambahkan
        $exists = ChatsMember::where('Chats_ID', $chat->Chats_ID)
                            ->where('User_ID', $member->User_ID)
                            ->exists();

        if (!$exists) {
            ChatsMember::create([
                'Chats_ID' => $chat->Chats_ID,
                'User_ID' => $member->User_ID
            ]);
        }

        return response()->json(['message' => 'Entered chat area.', 'ChatArea_ID' => $ChatArea_ID]);
    }

    public function leaveAreaChat($SpacesMember_ID)
    {
        $position = UserPosition::where('SpacesMember_ID', $SpacesMember_ID)->first();
        if (!$position || !$position->ChatArea_ID) {
            return response()->json(['message' => 'User is not in any chat area.'], 404);
        }

        $member = SpacesMember::find($SpacesMember_ID);
        if (!$member) {
            return response()->json(['message' => 'SpacesMember not found.'], 404);
        }

        // --- HAPUS ATAU KOMENTARI BLOK INI ---
        // Dengan tidak menghapus keanggotaan, riwayat chat akan tetap terhubung
        /* $chats = Chat::where('ChatArea_ID', $position->ChatArea_ID)->get();
        foreach ($chats as $chat) {
            ChatsMember::where('Chats_ID', $chat->Chats_ID)
                    ->where('User_ID', $member->User_ID)
                    ->delete();
        }
        */

        // Kosongkan ChatArea_ID dari posisi user (ini sudah benar)
        $position->ChatArea_ID = null;
        $position->LastUpdated = Carbon::now();
        $position->save();

        return response()->json(['message' => 'Left chat area.']);
    }


}
