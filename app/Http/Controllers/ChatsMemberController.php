<?php

namespace App\Http\Controllers;

use App\Models\ChatsMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatsMemberController extends Controller
{
    // Tambah users ke chat

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Chats_ID' => 'required|exists:chats,Chats_ID',
            'User_ID'  => 'required|exists:users,User_ID',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $existing = ChatsMember::where('Chats_ID', $request->Chats_ID)
                               ->where('User_ID', $request->User_ID)
                               ->first();
    
        if ($existing) {
            return response()->json(['message' => 'User already in chat'], 409);
        }
    
        $member = ChatsMember::create($request->all());
        return response()->json($member, 201);
    }
    

    // Ambil semua member dalam suatu chat
    public function membersByChat($chatId)
    {
        $members = ChatsMember::with('user')->where('Chats_ID', $chatId)->get();
        return response()->json($members);
    }

    // Ambil semua chat yang diikuti user
    public function membersById($userId)
    {
        $members = ChatsMember::with('chat')->where('User_ID', $userId)->get();
        return response()->json($members);
    }

    // Hapus member dari chat
    public function destroy($id)
    {
        ChatsMember::destroy($id);
        return response()->json(['message' => 'Chat member removed']);
    }

    // Ambil semua chat personal yang diikuti oleh user
    public function getChatByUser($userId)
    {
        $personalChats = ChatsMember::with(['chat' => function ($query) {
                                    $query->where('Chat_Type', 'Personal');
                                }])
                                ->where('User_ID', $userId)
                                ->get()
                                ->filter(function ($chatMember) {
                                    return $chatMember->chat !== null;
                                })
                                ->pluck('chat');

        return response()->json($personalChats);
    }

    // Ambil member berdasarkan Chats_ID dan User_ID
    public function getByChatAndUserId($chatId, $userId)
    {
        $member = ChatsMember::with(['chat', 'user'])
                    ->where('Chats_ID', $chatId)
                    ->where('User_ID', $userId)
                    ->first();

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        return response()->json($member);
    }


}
