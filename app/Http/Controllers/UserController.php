<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\LoginAttemptFailed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class UserController extends Controller
{
    //Registro
    public function store(Request $request)
    {
        if (!$request->hasFile('avatar')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Avatar image is required'
            ], 400);
        }
        $validatedUser = $request->validate([
            'name' => 'required|min:3|max:10|string',
            'email' => 'required|unique:users,email|email',
            'password' => 'required|string|min:8|confirmed',
            'avatar' => 'required|file'
        ]);

        $file = $request->file('avatar');
        $path = $file->store('uploads', 'public');
        $validatedUser['avatar'] = url('storage/' . $path);
        $validatedUser['password'] = bcrypt($validatedUser['password']);
        User::create($validatedUser);
        return response()->json(["status" => "success"], 201);
    }
    //Obtener un usuario
    public function show(User $user)
    {
        if (!$user) {
            return response()->json(["status" => "Not found"], 404);
        }
        $user->load("following", "followers");
        return response()->json($user);
    }
    //Obtener todos los usuarios
    public function index()
    {
        return response()->json(["status" => "success", "users" => User::all()]);
    }
    //Loguearse
    public function login(Request $request, User $user)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        if ($user->is_locked) {
            return response()->json(['status' => 'error', 'message' => 'Account is locked.'], 403);
        }

        $attempts = $request->session()->get('login_attempts_' . $user->id, 0);

        if (!auth()->attempt($credentials)) {
            $attempts++;
            $request->session()->put('login_attempts_' . $user->id, $attempts);


            if ($user && $attempts === 3) {
                // Bloquear la cuenta después de 3 intentos
                $user->is_locked = true;
                $user->reactivation_code = Str::random(10); // Generar un código de reactivación
                $user->save();

                // Notificar al usuario sobre el bloqueo de cuenta
                $user->notify(new LoginAttemptFailed($request->email, $user->reactivation_code));
                return response()->json(['status' => 'error', 'message' => 'Limit of attempts reached, account is now locked.'], 401);
            }

            return response()->json(['status' => 'error', 'message' => 'Unauthorized - Invalid credentials'], 401);
        }

        // Limpiar los intentos de inicio de sesión en caso de éxito
        $request->session()->forget('login_attempts_' . $user->id);

        // Cargar los seguidores y seguidos usando las relaciones
        $user->load('followers', 'following');

        // Generar el token de acceso y retornarlo
        $token = $request->user()->createToken('Social-NETT')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 200);
    }
    //Reactivar cuenta tras bloqueo por +3 intentos
    public function reactivateAccount(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->reactivation_code !== $request->code) {
            return response()->json(['status' => 'error', 'message' => 'Invalid code or user not found'], 404);
        }

        // Reactivar la cuenta
        $user->is_locked = false;
        $user->reactivation_code = null; // Limpiar el código
        $user->save(); // Guardar cambios

        // Limpiar los intentos de inicio de sesión fallidos
        $request->session()->forget('login_attempts_' . $user->id);
        return response()->json(['message' => 'Account reactivated successfully'], 200);
    }
    public function changeAccountPrivacy()
    {
        // Obtiene el usuario autenticado
        $user = Auth::user();
        if ($user) {
            // Cambia el valor de isPrivate al opuesto
            $user->isPrivate = !$user->isPrivate;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'La privacidad de la cuenta ha sido actualizada.',
                'isPrivate' => $user->isPrivate,
            ]);
        }

        // Si el usuario no está autenticado, retorna un error
        return response()->json([
            'success' => false,
            'message' => 'Usuario no autenticado.',
        ], 401);
    }

    //Buscar usuarios
    public function searchUsers(Request $request)
    {
        $query = $request->input('query');
        // Validar que la consulta tenga al menos 2 letras
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        $users = User::where('name', 'like', '%' . $query . '%')->with('followers', 'following')->take(5)->get(['id', 'name', 'avatar']);
        return response()->json(['users' => $users]);
    }
}
