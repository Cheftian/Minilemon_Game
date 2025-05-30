<?php

namespace App\Http\Controllers;


use App\Models\Chat;
use App\Models\ChatsMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Space;
use App\Models\Room;
use App\Models\SpacesMember;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class SpaceController extends Controller
{
    // 1. Buat space baru + otomatis buat 2 room
    public function create(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'Name' => 'required|string',
        'Password' => 'required|string',
        'Banner_Image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $user = auth()->user();
    $code = strtoupper(Str::random(6));
    $validated = $validator->validated();

    // Upload banner image jika ada
    if ($request->hasFile('Banner_Image')) {
        $file = $request->file('Banner_Image');
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $folder = 'banners';
        $path = base_path("public/images/{$folder}");

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $file->move($path, $filename);
        $validated['Banner_Image'] = "images/{$folder}/{$filename}";
    } else {
        $validated['Banner_Image'] = "images/banners/default.png";
    }

    $space = Space::create([
        'Name' => $validated['Name'],
        'Password' => $validated['Password'],
        'Code' => $code,
        'Banner_Image' => $validated['Banner_Image'],
    ]);

        // Buat dua Room: lantai1 & lantai2
        $roomData = [
            ['Room_Name' => 'lantai1', 'MapLayout' => 'map_lantai1.json'],
            ['Room_Name' => 'lantai2', 'MapLayout' => 'map_lantai2.json']
        ];

        foreach ($roomData as $data) {
            Room::create([
                'Spaces_ID' => $space->Spaces_ID,
                'Room_Name' => $data['Room_Name'],
            ]);
        }

        SpacesMember::create([
            'Role' => 'Admin',
            'Online' => false,
            'Active_Video' => false,
            'Active_Mic' => false,
            'User_ID' => $user->User_ID,
            'Spaces_ID' => $space->Spaces_ID,
        ]);

        // Buat chat broadcast untuk space tersebut
        $broadcastChat = Chat::create([
            'Chat_Type'   => 'Broadcast',
            'Spaces_ID'   => $space->Spaces_ID,
            'Room_ID'     => null,
            'ChatArea_ID' => null,
            'Temporary'   => false
        ]);

        // Ambil semua member dari space (termasuk admin yang baru dibuat)
        $members = SpacesMember::where('Spaces_ID', $space->Spaces_ID)->pluck('User_ID');

        // Masukkan semua user ke chats_member
        $broadcastMembers = $members->map(function ($userId) use ($broadcastChat) {
            return [
                'Chats_ID' => $broadcastChat->Chats_ID,
                'User_ID' => $userId
            ];
        })->toArray();

        ChatsMember::insert($broadcastMembers);


        return response()->json([
            'message' => 'Space created successfully.',
            'space' => $space,
        ], 201);
    }

    // 2. Masuk ke space dengan kode + password
    public function join(Request $request)
    {
        $this->validate($request, [
            'Code' => 'required|string',
            'Password' => 'required|string',
        ]);

        $user = auth()->user();

        $space = Space::where('Code', $request->input('Code'))
                    ->where('Password', $request->input('Password'))
                    ->first();

        if (!$space) {
            return response()->json(['message' => 'Invalid code or password.'], 401);
        }

        // Buatkan entri member baru
        SpacesMember::create([
            'Role' => 'Member',
            'Online' => false,
            'Active_Video' => false,
            'Active_Mic' => false,
            'User_ID' => $user->User_ID,
            'Spaces_ID' => $space->Spaces_ID,
        ]);

        // Tambahkan user ke broadcast chat space
        $broadcastChat = Chat::where('Spaces_ID', $space->Spaces_ID)
            ->where('Chat_Type', 'Broadcast')
            ->first();

        if ($broadcastChat) {
            $alreadyExists = ChatsMember::where('Chats_ID', $broadcastChat->Chats_ID)
                ->where('User_ID', $user->User_ID)
                ->exists();

            if (!$alreadyExists) {
                ChatsMember::create([
                    'Chats_ID' => $broadcastChat->Chats_ID,
                    'User_ID' => $user->User_ID
                ]);
            }
        }

        // Ambil semua member lain (selain user ini)
        $otherMembers = SpacesMember::where('Spaces_ID', $space->Spaces_ID)
                                    ->where('User_ID', '!=', $user->User_ID)
                                    ->pluck('User_ID');

        // Buat chat personal antara user ini dan tiap member lain
        foreach ($otherMembers as $otherUserId) {
            $chat = Chat::create([
                'Chat_Type'   => 'Personal',
                'Spaces_ID'   => $space->Spaces_ID,
                'Room_ID'     => null,
                'ChatArea_ID' => null,
                'Temporary'   => false,
            ]);

            // Tambahkan ke chats_member: [user, other] dan [other, user]
            ChatsMember::insert([
                ['Chats_ID' => $chat->Chats_ID, 'User_ID' => $user->User_ID],
                ['Chats_ID' => $chat->Chats_ID, 'User_ID' => $otherUserId],
            ]);
        }

        return response()->json([
            'message' => 'Successfully joined the space and chats created.',
            'space' => $space
        ]);
    }


    // 3. Edit nama dan password space
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'Name' => 'nullable|string',
            'Password' => 'nullable|string',
            'Banner_Image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $space = Space::find($id);
        if (!$space) {
            return response()->json(['message' => 'Space not found.'], 404);
        }

        $validated = $validator->validated();

        if (isset($validated['Name'])) {
            $space->Name = $validated['Name'];
        }

        if (isset($validated['Password'])) {
            $space->Password = $validated['Password'];
        }

        if ($request->hasFile('Banner_Image')) {
            $file = $request->file('Banner_Image');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $folder = 'banners';
            $path = base_path("public/images/{$folder}");

            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }

            $file->move($path, $filename);
            $space->Banner_Image = "images/{$folder}/{$filename}";
        }

        $space->save();

        return response()->json(['message' => 'Space updated successfully.', 'space' => $space]);
    }


    // 4. Get semua space (untuk admin/debug)
    public function index()
    {
        return response()->json(Space::all());
    }

    // 5. Get satu space by ID
    public function show($id)
    {
        $space = Space::find($id);
        if (!$space) {
            return response()->json(['message' => 'Space not found.'], 404);
        }

        return response()->json($space);
    }

    // 6. Hapus space
    public function destroy($id)
    {
        $space = Space::find($id);
        if (!$space) {
            return response()->json(['message' => 'Space not found.'], 404);
        }

        $space->delete();
        return response()->json(['message' => 'Space deleted successfully.']);
    }
}
