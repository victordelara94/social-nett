<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function index()
    {
        return response()->json([
            'likes' => Post::with("likes")->get()
        ]);
    }
    public function store($postId)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        // Validar que el postId esté en la base de datos
        $post = Post::find($postId);
        if (!$post) {
            return response()->json(['status' => 'error', 'message' => 'Post not found'], 404);
        }

        if ($post->likes()->where('user_id', $user->id)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'You have already liked this post.', 409]);
        }
        $post->likes()->attach($user->id);
        return response()->json([
            'status' => 'success',
            'message' => 'You have liked this post.',
            'like' => ['id' => $user->id, 'name' => $user->name, 'avatar' => $user->avatar]
        ], 201);
    }

    public function destroy($postId)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $post = Post::find($postId);
        if (!$post) {
            return response()->json(['status' => 'error', 'message' => 'Post not found'], 200);
        }
        // Verificar si el "like" existe y pertenece al usuario autenticado

        if (!$post->likes()->where("user_id", $user->id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Like not found or you do not have permission to remove this like',
            ], 200);
        }

        // Eliminar el "like"
        $post->likes()->detach($user->id);

        return response()->json(['status' => 'success', 'message' => 'You have removed your like from this post.', 'like' => ['id' => $user->id, 'name' => $user->name, 'avatar' => $user->avatar]]);
    }

    public function getPostLikes($postId)
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        // Validar que el postId esté en la base de datos
        $post = Post::find($postId);
        if (!$post) {
            return response()->json(['status' => 'error', 'message' => 'Post not found'], 200);
        }
        $postLikes = $post->likes()->select("users.id", "users.name")->get()->makeHidden('pivot');

        return response()->json([
            'status' => 'success',
            'postId' => $postId,
            'likes' => $postLikes
        ], 200);
    }
}
