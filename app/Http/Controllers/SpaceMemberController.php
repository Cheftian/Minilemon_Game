<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SpacesMember;

class SpaceMemberController extends Controller
{

    // GET: Semua SpaceMember
    public function getAllMembers()
    {
        $members = SpacesMember::with(['user', 'space'])->get();
        return response()->json($members);
    }

    // GET semua member dari satu space
    public function index($spaceId)
    {
        $members = SpacesMember::with('user')->where('Spaces_ID', $spaceId)->get();
        return response()->json($members);
    }

    // GET: SpaceMember berdasarkan space_id dan user_id
    public function getBySpaceAndUser($spaceId, $userId)
    {
        $member = SpacesMember::with(['user', 'space'])
            ->where('Spaces_ID', $spaceId)
            ->where('User_ID', $userId)
            ->first();

        if (!$member) {
            return response()->json(['message' => 'Member not found.'], 404);
        }

        return response()->json($member);
    }


    // PUT: Update status Online, Mic, Video
    public function updateStatus(Request $request, $id)
    {
        $this->validate($request, [
            'Online' => 'boolean',
            'Active_Mic' => 'boolean',
            'Active_Video' => 'boolean',
        ]);

        $member = SpacesMember::find($id);
        if (!$member) return response()->json(['message' => 'Member not found.'], 404);

        $member->update($request->only('Online', 'Active_Mic', 'Active_Video'));

        return response()->json(['message' => 'Status updated.', 'member' => $member]);
    }

    // PUT: Update role (Admin/Member)
    public function updateRole(Request $request, $id)
    {
        $this->validate($request, ['Role' => 'in:Admin,Member']);

        $member = SpacesMember::find($id);
        if (!$member) return response()->json(['message' => 'Member not found.'], 404);

        $member->Role = $request->input('Role');
        $member->save();

        return response()->json(['message' => 'Role updated.', 'member' => $member]);
    }

    // GET: Semua space yang diikuti oleh User tertentu
    public function getSpacesByUser($userId)
    {
        $spaces = SpacesMember::with('space')
            ->where('User_ID', $userId)
            ->get();

        return response()->json($spaces);
    }


    // DELETE: Hapus dari space
    public function destroy($id)
    {
        $member = SpacesMember::find($id);
        if (!$member) return response()->json(['message' => 'Member not found.'], 404);

        $member->delete();
        return response()->json(['message' => 'Member removed from space.']);
    }
}
