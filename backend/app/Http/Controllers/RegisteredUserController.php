<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisteredUserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            // Validación de los datos del formulario
            $request->validate([
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            // Asignar rol predeterminado con ID 2 si no se proporciona 'role_id'
            $roleId = $request->role_id ?? 2; // Si no hay 'role_id', asignar el rol con ID 2 (por defecto)

            // Crear el usuario con los datos del formulario
            $user = User::create([
                'name' => $request->name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $roleId, // Asignar el rol (predeterminado o el proporcionado)
            ]);

            Mail::to($user->email)->send(new WelcomeMail($user));

            // Crear token de autenticación
            $token = $user->createToken('Groupify')->plainTextToken;

            // Responder con éxito
            return response()->json([
                'message' => 'Usuari registrat correctament.',
                'user' => $user,
                'token' => $token
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Manejar errores de validación
            return response()->json([
                'message' => 'Error de validació',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Manejar cualquier otro error
            return response()->json([
                'message' => 'Error en el registre',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/google-login",
     *     summary="Login/Registro con Google",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "google_id", "name"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="google_id", type="string", example="google_123456"),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="image", type="string", example="https://example.com/avatar.jpg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
     *             @OA\Property(property="user", type="object", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function googleLogin(Request $request)
    {
        try {
            // Validación de campos
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'google_id' => 'required|string',
                'name' => 'required|string',
                'last_name' => 'nullable|string',
                'image' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 400);
            }

            // Buscar o crear usuario
            $user = User::where('email', $request->email)
                ->orWhere('google_id', $request->google_id)
                ->first();

            if (!$user) {
                $user = User::create([
                    'name' => $request->name,
                    'last_name' => $request->last_name ?? '',
                    'email' => $request->email,
                    'google_id' => $request->google_id,
                    'image' => $request->image,
                    'password' => bcrypt(Str::random(16)), // Contraseña aleatoria
                    'role_id' => 2, // Rol por defecto
                ]);
            } else {
                $user->update([
                    'name' => $request->name,
                    'last_name' => $request->last_name ?? $user->last_name,
                    'google_id' => $request->google_id,
                    'image' => $request->image,
                ]);
            }

            // Generar token
            $token = $user->createToken('GroupifyToken')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Google Login Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
