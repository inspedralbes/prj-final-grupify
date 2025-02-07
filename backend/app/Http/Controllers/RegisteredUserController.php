<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CourseDivisionUser; // Importa el modelo de asignaciones
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
     *     summary="Registra un nuevo usuario",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "last_name", "email", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", example="Juan"),
     *             @OA\Property(property="last_name", type="string", example="Pérez"),
     *             @OA\Property(property="email", type="string", example="juanperez@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario registrado correctamente"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            // Validación de los datos del formulario
            $request->validate([
                'name'              => 'required|string|max:255',
                'last_name'         => 'required|string|max:255',
                'email'             => 'required|string|email|max:255|unique:users',
                'password'          => 'required|string|min:8|confirmed',
            ]);

            // Asignar rol predeterminado (ID 2) si no se proporciona 'role_id'
            $roleId = $request->role_id ?? 2;

            // Definir imagen predeterminada
            $defaultImage = 'http://localhost:8000/images/default.png'; // Ajusta esta ruta según tu configuración

            // Crear el usuario con los datos del formulario
            $user = User::create([
                'name'      => $request->name,
                'last_name' => $request->last_name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'role_id'   => $roleId,
                'image'     => $request->image ?? $defaultImage, // Si no se envía imagen se asigna la predeterminada
            ]);

            // Enviar correo de bienvenida
            Mail::to($user->email)->send(new WelcomeMail($user));

            // Crear la asignación en la tabla course_division_user con course_id y division_id en null
            CourseDivisionUser::create([
                'user_id'     => $user->id,
                'course_id'   => null,
                'division_id' => null,
            ]);

            // Crear token de autenticación
            $token = $user->createToken('Groupify')->plainTextToken;

            return response()->json([
                'message' => 'Usuario registrado correctamente.',
                'user'    => $user,
                'token'   => $token
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error en el registro',
                'error'   => $e->getMessage()
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
     *             @OA\Property(property="name", type="string", example="Juan"),
     *             @OA\Property(property="last_name", type="string", example="Pérez"),
     *             @OA\Property(property="image", type="string", example="https://example.com/avatar.jpg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
     *             @OA\Property(property="user", type="object")
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
            // Validación de los campos recibidos
            $validator = Validator::make($request->all(), [
                'email'     => 'required|email',
                'google_id' => 'required|string',
                'name'      => 'required|string',
                'last_name' => 'nullable|string',
                'image'     => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors'  => $validator->errors(),
                ], 400);
            }

            // Definir imagen predeterminada
            $defaultImage = '/images/default.png';

            // Buscar o crear usuario
            $user = User::where('email', $request->email)
                        ->orWhere('google_id', $request->google_id)
                        ->first();

            if (!$user) {
                $user = User::create([
                    'name'      => $request->name,
                    'last_name' => $request->last_name ?? '',
                    'email'     => $request->email,
                    'google_id' => $request->google_id,
                    'image'     => $request->image ?? $defaultImage,
                    'password'  => bcrypt(Str::random(16)),
                    'role_id'   => 2,
                ]);

                // Crear la asignación en course_division_user
                CourseDivisionUser::create([
                    'user_id'     => $user->id,
                    'course_id'   => null,
                    'division_id' => null,
                ]);
            } else {
                // Actualizar datos del usuario existente
                $user->update([
                    'name'      => $request->name,
                    'last_name' => $request->last_name ?? $user->last_name,
                    'google_id' => $request->google_id,
                    'image'     => $request->image ?? $user->image,
                ]);
            }

            // Generar token de autenticación
            $token = $user->createToken('GroupifyToken')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user'  => $user,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Google Login Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error interno del servidor',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
