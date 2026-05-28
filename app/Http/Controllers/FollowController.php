<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    // ✅ Follow / Unfollow
    public function toggle(Request $request, $id)
    {
        $userToFollow = User::findOrFail($id);

        if ($request->user()->id === $userToFollow->id) {
            return response()->json(['message' => 'Cannot follow yourself'], 400);
        }

        $isFollowing = $request->user()->following()->where('following_id', $id)->exists();

        if ($isFollowing) {
            $request->user()->following()->detach($id);
            return response()->json(['message' => 'Unfollowed', 'following' => false]);
        } else {
            $request->user()->following()->attach($id);
            return response()->json(['message' => 'Followed', 'following' => true]);
        }
    }

    // ✅ قائمة المتابَعين
    public function following(Request $request)
    {
        $following = $request->user()->following()
            ->select('users.id', 'users.name', 'users.avatar')
            ->get();

        return response()->json($following);
    }

    // ✅ قائمة المتابِعين
    public function followers(Request $request)
    {
        $followers = $request->user()->followers()
            ->select('users.id', 'users.name', 'users.avatar')
            ->get();

        return response()->json($followers);
    }

    public function profile($id)
    {
        $user = User::withCount(['followers', 'following', 'posts'])
            ->findOrFail($id);

        return response()->json([
            'id'              => $user->id,
            'name'            => $user->name,
            'avatar'          => $user->avatar,
            'bio'             => $user->bio,
            'posts_count'     => $user->posts_count,
            'followers_count' => $user->followers_count,
            'following_count' => $user->following_count,
        ]);
    }
}
