<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;
use App\Models\ChatsMember;

class ChatController extends Controller
{
    public function create(Request $request)
    {
        $validated = $request->validate([
            'Chat_Type'    => 'required|in:Personal,Group,Broadcast',
            'Spaces_ID'    => 'required|integer|exists:spaces,Spaces_ID',
            'Room_ID'      => 'required|integer|exists:room,Room_ID',
            'ChatArea_ID'  => 'nullable|integer|exists:chat_areas,ChatArea_ID',
            'Temporary'    => 'boolean'
        ]);

        $chat = Chat::create($validated);
        return response()->json($chat, 201);
    }

    public function getAll()
    {
        return response()->json(Chat::all());
    }

    public function getById($id)
    {
        $chat = Chat::find($id);
        return $chat ? response()->json($chat) : response()->json(['message' => 'Not found'], 404);
    }

    public function delete($id)
    {
        $chat = Chat::find($id);
        if (!$chat) return response()->json(['message' => 'Not found'], 404);

        $chat->delete();
        return response()->json(['message' => 'Chat deleted']);
    }

    public function setTemporaryFalse($id)
    {
        $chat = Chat::find($id);
        if (!$chat) return response()->json(['message' => 'Not found'], 404);

        $chat->Temporary = false;
        $chat->save();

        return response()->json(['message' => 'Temporary set to false']);
    }

    // Ambil semua chat broadcast berdasarkan Spaces_ID
    public function getBroadcastBySpaces($spaceId)
    {
        $broadcasts = Chat::where('Spaces_ID', $spaceId)
                        ->where('Chat_Type', 'broadcast')
                        ->get();

        return response()->json($broadcasts);
    }

    // Ambil semua chat berdasarkan ChatArea_ID
    public function getByChatArea($chatAreaId)
    {
        $chats = Chat::where('ChatArea_ID', $chatAreaId)->get();

        return response()->json($chats);
    }

    public function findPersonalChat($userId1, $userId2)
    {
        $chat = Chat::where('Chat_Type', 'Personal')
                    ->whereHas('members', function ($query) use ($userId1) {
                        $query->where('User_ID', $userId1);
                    })
                    ->whereHas('members', function ($query) use ($userId2) {
                        $query->where('User_ID', $userId2);
                    })
                    ->withCount('members')
                    ->having('members_count', 2)
                    ->first();

        if (!$chat) {
            return response()->json(['message' => 'Personal chat not found'], 404);
        }

        return response()->json($chat);
    }

}
