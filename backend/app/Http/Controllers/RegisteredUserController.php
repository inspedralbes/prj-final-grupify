<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
}
