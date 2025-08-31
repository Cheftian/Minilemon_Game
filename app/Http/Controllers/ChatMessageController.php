<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;


class ChatMessageController extends Controller
{
    // Menyimpan pesan ke chat
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Chats_ID' => 'required|exists:chats,Chats_ID',
            'ChatsMember_ID'  => 'required|exists:chats_members,ChatsMember_ID',
            'Message'  => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $message = ChatMessage::create([
            'Chats_ID' => $request->Chats_ID,
            'ChatsMember_ID'  => $request->ChatsMember_ID,
            'Message'  => $request->Message,
            'Time' => Carbon::now(),
        ]);
    
        return response()->json($message, 201);
    }
    

    // Mengambil semua pesan dalam satu chat
    public function getByChat($chatId)
    {
        // --- INILAH PERBAIKANNYA ---
        // Kita beritahu server untuk mengambil rantai data yang benar:
        // dari Pesan -> ke Anggota Chat -> lalu ke User
        $messages = ChatMessage::with('chat_member.user') 
                    ->where('Chats_ID', $chatId)
                    ->orderBy('Time', 'asc')
                    ->get();

        return response()->json($messages);
    }

    // Hapus pesan
    public function destroy($chatId)
    {
        $deleted = ChatMessage::where('Chats_ID', $chatId)->delete();

        return response()->json([
            'message' => "$deleted message(s) deleted for chat ID $chatId"
        ]);
    }
}