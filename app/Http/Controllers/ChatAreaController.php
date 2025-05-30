<?php

namespace App\Http\Controllers;

use App\Models\ChatArea;
use App\Models\Room;
use App\Models\SpacesMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use App\Models\Chat;


class ChatAreaController extends Controller
{

    public function getByObjectId($objectId)
    {
        $chatArea = ChatArea::where('Objects_ID', $objectId)->first();
        return $chatArea ? response()->json($chatArea) : response()->json(['message' => 'Not found'], 404);
    }

    public function create($spacesId)
    {
        $onlineCount = SpacesMember::where('Spaces_ID', $spacesId)->where('Online', true)->count();
        if ($onlineCount < 1) {
            return response()->json(['message' => 'No online members in this space'], 403);
        }

        $rooms = Room::where('Spaces_ID', $spacesId)->whereIn('Room_Name', ['lantai1', 'lantai2'])->get();
        $maxId = ChatArea::max('ChatArea_ID') ?? 0;
        $totalCreated = 0;

        foreach ($rooms as $room) {
            $filePath = base_path('public/data/' . $room->Room_Name . '.json');
            if (!File::exists($filePath)) continue;

            $map = json_decode(File::get($filePath), true);
            foreach ($map['layers'] as $layer) {
                if (!in_array($layer['name'], ['group_chat_area', 'private_chat_area'])) continue;
                $type = $layer['name'] === 'group_chat_area' ? 'group_chat' : 'private_chat';

                foreach ($layer['objects'] ?? [] as $object) {
                    $objectId = $object['id'];
                    $exists = ChatArea::where('Room_ID', $room->Room_ID)->where('Objects_ID', $objectId)->exists();
                    if ($exists) continue;

                    $maxId++;

                    // Insert into ChatAreas
                    \DB::table('chat_areas')->insert([
                        'ChatArea_ID' => $maxId,
                        'Room_ID'     => $room->Room_ID,
                        'Area_Type'   => $type,
                        'Temporary'   => true,
                        'Objects_ID'  => $objectId,
                        'created_at'  => Carbon::now(),
                        'updated_at'  => Carbon::now()
                    ]);

                    // Insert into Chats
                    Chat::create([
                        'Chat_Type'    => 'Group',
                        'Spaces_ID'    => $spacesId,
                        'Room_ID'      => $room->Room_ID,
                        'ChatArea_ID'  => $maxId,
                        'Temporary'    => true
                    ]);


                    $totalCreated++;
                }
            }
        }

        return response()->json(['message' => "$totalCreated chat areas and chats created"], 201);
    }


    public function deleteIfEmpty($spacesId)
    {
        $onlineCount = SpacesMember::where('Spaces_ID', $spacesId)->where('Online', true)->count();
        if ($onlineCount > 0) {
            return response()->json(['message' => 'Members are still online'], 403);
        }

        $roomIds = Room::where('Spaces_ID', $spacesId)->pluck('Room_ID');

        // Ambil semua ChatArea temporary yang akan dihapus
        $chatAreasToDelete = ChatArea::whereIn('Room_ID', $roomIds)
            ->where('Temporary', true)
            ->pluck('ChatArea_ID');

        // Hapus chats yang memiliki ChatArea_ID tersebut
        Chat::whereIn('ChatArea_ID', $chatAreasToDelete)->delete();

        // Hapus chat_areas
        $deleted = ChatArea::whereIn('ChatArea_ID', $chatAreasToDelete)->delete();

        // Reset ulang ID untuk permanent chat_areas
        $permanentAreas = ChatArea::whereIn('Room_ID', $roomIds)->where('Temporary', false)->orderBy('ChatArea_ID')->get();
        $newId = 1;
        foreach ($permanentAreas as $area) {
            \DB::table('chat_areas')->where('ChatArea_ID', $area->ChatArea_ID)->update(['ChatArea_ID' => $newId]);
            $newId++;
        }

        return response()->json([
            'message' => "$deleted temporary chat areas and associated chats deleted, permanent IDs reset"
        ]);
    }



    public function getAll()
    {
        return response()->json(ChatArea::all());
    }

    public function getByRoom($roomId)
    {
        return response()->json(ChatArea::where('Room_ID', $roomId)->get());
    }

    public function getById($id)
    {
        $chatArea = ChatArea::find($id);
        return $chatArea ? response()->json($chatArea) : response()->json(['message' => 'Not found'], 404);
    }

    public function setTemporaryFalse($id)
    {
        $chatArea = ChatArea::find($id);
        if (!$chatArea) return response()->json(['message' => 'Not found'], 404);

        $chatArea->Temporary = false;
        $chatArea->save();

        return response()->json(['message' => 'Temporary set to false']);
    }
}
