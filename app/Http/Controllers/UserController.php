<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SpacesMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller {

    public function index() {
        return response()->json(User::all());
    }

    public function show($id) {
        return response()->json(User::findOrFail($id));
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Username' => 'required|string|max:255',
            'Email' => 'required|email|unique:users,Email',
            'Password' => 'required|string|min:6',
            'Profile_Image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();
        $validated['Password'] = Hash::make($validated['Password']);

        // Upload gambar profil jika ada
        if ($request->hasFile('Profile_Image')) {
            $file = $request->file('Profile_Image');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $folder = 'profile';
            $path = base_path("public/images/{$folder}");

            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }

            $file->move($path, $filename);
            $validated['Profile_Image'] = "images/{$folder}/{$filename}";
        } else {
            $validated['Profile_Image'] = "images/profile/default.jpg";
        }

        $user = User::create($validated);
        return response()->json($user, 201);
    }



    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->all();

        if (isset($data['Password'])) {
            $data['Password'] = Hash::make($data['Password']);
        }

        // Upload baru jika ada file
        if ($request->hasFile('Profile_Image')) {
            $image = $request->file('Profile_Image');
            $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/profile'), $imageName);
            $data['Profile_Image'] = 'images/profile/' . $imageName;
        }

        $user->update($data);
        return response()->json($user);
    }


    public function destroy($id) {
        User::destroy($id);
        return response()->json(['message' => 'User deleted']);
    }


    public function login(Request $request) {
        $credentials = $this->validate($request, [
            'Email' => 'required|email',
            'Password' => 'required|string',
        ]);
    
        $user = User::where('Email', $credentials['Email'])->first();
    
        if (!$user || !Hash::check($credentials['Password'], $user->Password)) {
            return response()->json(['message' => 'Invalid Email or Password'], 401);
        }
    
        $payload = [
            'iss' => "lumen-jwt", // issuer
            'sub' => $user->User_ID, // subject (user ID)
            'iat' => time(), // issued at
            'exp' => time() + 60*60*24 // expires in 24 hours
        ];
    
        $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');
    
        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }


    public function logout(Request $request)
    {
        $user = auth()->user();

        // Set semua status online ke false di semua space
        SpacesMember::where('User_ID', $user->User_ID)->update(['Online' => false]);

        // Tidak perlu menghapus token karena JWT tidak disimpan di server
        return response()->json(['message' => 'Logout successful. User marked as offline.']);
    }

}
