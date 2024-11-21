<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function Laravel\Prompts\select;

class PostController extends Controller
{
    public function store(Request $request)
    {
        // Confirmar que el usuario esté autenticado
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        try {
            $request->validate([
                'description' => 'required|string',
                'image' => 'required|image|max:2048'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => $e->errors()], 422);
        }
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }
        $imagePath = $request->file('image')->store('posts', 'public');

        $post = Post::create([
            'user_id' => Auth::id(),
            'description' => $request->input('description'),
            'image_path' => url('storage/' . $imagePath)
        ]);

        // Cargar los likes y seleccionar solo los campos `id` y `name` de los usuarios
        $post->load(['likes' => function ($query) {
            $query->select('users.id', 'users.name', 'likes.post_id');
        }]);
        $post->likes->makeHidden('pivot');
        return response()->json(['status' => 'success', 'post' => $post]);
    }
    public function index()
    {

        return response()->json(['status' => 'success', 'posts' => Post::all()]);
    }
    public function getUsersPosts()
    {
        $userId = auth()->id();
        /*Obtener ids de los usuarios seguidos*/
        $followingIds = User::find($userId)->following()->pluck('users.id');

        /* Obtener publicaciones de los usuarios seguidos y de los públicos, excluyendo el propio usuario*/
        $usersPosts = Post::whereIn('user_id', $followingIds)
            ->orWhereHas('user', function ($query) {
                $query->where('isPrivate', false);
            })->where('user_id', '<>', $userId)
            ->with(['user:id,name,avatar', 'likes:id,name,avatar'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Transformar para eliminar `user_id` y ocultar `pivot` en `likes`
        $usersPosts = $usersPosts->map(function ($post) {
            unset($post->user_id); // Eliminar el campo `user_id`
            // Ocultar el atributo `pivot` en la relación `likes`
            $post->likes->makeHidden('pivot');
            return $post;
        });
        return response()->json($usersPosts);
    }
    public function getCurrentUserPosts($userId)
    {
        $userPosts = Post::where('user_id', $userId)->with(['user:id,name,avatar', 'likes:id,name,avatar'])->get();
        $userPosts = $userPosts->map(function ($post) {
            unset($post->user_id); // Eliminar el campo `user_id`
            // Ocultar el atributo `pivot` en la relación `likes`
            $post->likes->makeHidden('pivot');
            return $post;
        });
        return response()->json($userPosts);
    }
}
