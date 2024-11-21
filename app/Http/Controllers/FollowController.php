<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    // Seguir a un usuario
    public function follow($id)
    {
        $followerId = auth()->id();
        $user = User::findOrFail($id);

        if ($followerId !== $id && !$user->followers()->where('follower_id', $followerId)->exists()) {
            auth()->user()->following()->attach($id);
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error', 'message' => 'You can’t follow this user']);
    }

    // Dejar de seguir a un usuario
    public function unfollow($id)
    {
        $followerId = auth()->id();
        $user = User::findOrFail($id);

        if ($user->followers()->where('follower_id', $followerId)->exists()) {
            auth()->user()->following()->detach($id);
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error', 'message' => 'You are not following this user']);
    }

    // Obtener los seguidores de un usuario
    public function getFollowers($id)
    {
        $user = User::findOrFail($id);
        $followerIds = $user->followers()->pluck('users.id');
        return response()->json(['followers_ids' => $followerIds]);
    }

    // Obtener los usuarios que sigue un usuario
    public function getFollowings($id)
    {
        $user = User::findOrFail($id);
        $followingIds = $user->following()->pluck('users.id');
        return response()->json(['following_ids' => $followingIds]);
    }
}
