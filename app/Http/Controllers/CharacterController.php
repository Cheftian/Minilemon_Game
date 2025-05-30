<?php

namespace App\Http\Controllers;

use App\Models\Character;
use Illuminate\Http\Request;

class CharacterController extends Controller {

    public function index() {
        return response()->json(Character::all());
    }

    public function show($id) {
        return response()->json(Character::findOrFail($id));
    }

    public function store(Request $request) {
        $character = Character::create($request->all());
        return response()->json([
            'id' => $character->Character_ID,

            'message' => 'Character created successfully'
        ], 201);
    }
    

    public function update(Request $request, $id) {
        $character = Character::findOrFail($id);
        $character->update($request->all());
        return response()->json($character);
    }

    public function destroy($id) {
        Character::destroy($id);
        return response()->json(['message' => 'Character deleted']);
    }
}
