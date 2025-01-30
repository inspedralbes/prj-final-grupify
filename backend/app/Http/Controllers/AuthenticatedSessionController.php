<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Mail\LoginNotificationMail;
use Illuminate\Support\Facades\Mail;

class AuthenticatedSessionController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="User login",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     )
     * )
     */
    public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $user = Auth::user();

    // Revocar tokens existentes
    $user->tokens()->delete();

    // Crear nuevo token
    $token = $user->createToken('Groupify')->plainTextToken;

    // Cargar las relaciones necesarias (forms, subjects, role, courseDivisions)
    $user->load(['forms', 'subjects', 'role', 'courseDivisions']);

    // Extraer el primer course_id y division_id
    $courseDivision = $user->courseDivisions->first(); // Obtenemos solo el primer curso/división
    $course_id = $courseDivision ? $courseDivision->pivot->course_id : null;
    $division_id = $courseDivision ? $courseDivision->pivot->division_id : null;

    // Preparar la respuesta con los campos que deseas
    return response()->json([
        'token' => $token,
        'role' => $user->role->name,
        'user' => [
            'id' => $user->id,
            'image' => $user->image,
            'name' => $user->name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'role_id' => $user->role_id,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'status' => $user->status,
            'course_id' => $course_id,  // Agregar el course_id aquí
            'division_id' => $division_id,  // Agregar el division_id aquí
            'forms' => $user->forms,
            'subjects' => $user->subjects,
            'role' => $user->role
        ]
    ]);
}


    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="User logout",
     *     tags={"Authentication"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out"
     *     )
     * )
     */
    public function logout(Request $request)
    {
        // Revocar todos los tokens del usuario autenticado
        if ($request->user()) {
            $request->user()->tokens()->delete(); // Esto elimina todos los tokens activos del usuario
        }

        return response()->json([
            'message' => 'Sesión cerrada correctamente.'
        ], 200);
    }
}
