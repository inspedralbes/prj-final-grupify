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
use App\Models\Invitation;
use Illuminate\Support\Facades\DB;

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
        DB::beginTransaction();
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'name'              => 'required|string|max:255',
                'last_name'         => 'required|string|max:255',
                'email'             => 'required|string|email|max:255|unique:users,email',
                'password'          => 'required|string|min:8|confirmed',
                'invitation_token'  => 'nullable|exists:invitations,token',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors'  => $validator->errors()
                ], 422);
            }

            // Create user
            $user = User::create([
                'name'      => $request->name,
                'last_name' => $request->last_name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'role_id'   => $request->role_id ?? 2,
                'image'     => $request->image ?? 'https://api.grupify.cat/images/default.png',
            ]);

            // Find invitation and get course/division
            $course_id = null;
            $division_id = null;

            if ($request->has('invitation_token')) {
                $invitation = Invitation::where('token', $request->invitation_token)->first();

                if ($invitation) {
                    $course_id = $invitation->course_id;
                    $division_id = $invitation->division_id;
                }
            }

            // Create course-division-user association
            CourseDivisionUser::create([
                'user_id'     => $user->id,
                'course_id'   => $course_id,
                'division_id' => $division_id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'User registered successfully',
                'user'    => $user,
                'course_id' => $course_id,
                'division_id' => $division_id
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Registration failed',
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
            // Validación de campos, incluyendo invitation_token opcional
            $validator = Validator::make($request->all(), [
                'email' => [
                    'required',
                    'email',
                    'regex:/^[^@]+@inspedralbes\.cat$/i' // Nueva regla de dominio
                ],
                'google_id' => 'required|string',
                'name' => 'required|string',
                'last_name' => 'nullable|string',
                'image' => 'nullable|string',
                'invitation_token' => 'nullable|exists:invitations,token',
            ], [
                'email.regex' => 'Solo se permiten correos de @inspedralbes.cat' // Mensaje personalizado
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => $validator->errors(),
                ], 400);
            }

            // Buscar o crear el usuario
            $user = User::where('email', $request->email)
                ->orWhere('google_id', $request->google_id)
                ->first();

            // Determinar rol basado en el correo
            $emailLocalPart = explode('@', $request->email)[0];
            $isStudent = preg_match('/^a\d/i', $emailLocalPart);
            $role_id = $isStudent ? 2 : 3; // Ajusta los IDs según tus roles

            if (!$user) {
                $user = User::create([
                    'name'      => $request->name,
                    'last_name' => $request->last_name ?? '',
                    'email'     => $request->email,
                    'google_id' => $request->google_id,
                    'image'     => $request->image,
                    'password'  => Hash::make(Str::random(16)),
                    'role_id'   => $role_id, // Rol determinado
                ]);
            } else {
                $user->update([
                    'name'      => $request->name,
                    'last_name' => $request->last_name ?? $user->last_name,
                    'google_id' => $request->google_id,
                    'image'     => $request->image,
                    'role_id'   => $role_id, // Actualizar rol cada login
                ]);
            }

            // Procesar invitation_token si se envía, para obtener course_id y division_id
            $course_id = null;
            $division_id = null;
            if ($request->has('invitation_token')) {
                $invitation = Invitation::where('token', $request->invitation_token)->first();
                if ($invitation) {
                    $course_id = $invitation->course_id;
                    $division_id = $invitation->division_id;
                }
            }

            // Asociar el usuario en la tabla course_division_user
            $existingAssociation = CourseDivisionUser::where('user_id', $user->id)->first();
            if ($existingAssociation) {
                // Si se envió invitation_token, actualizamos la asociación
                if ($request->has('invitation_token') && isset($invitation)) {
                    $existingAssociation->update([
                        'course_id'   => $course_id,
                        'division_id' => $division_id,
                    ]);
                }
            } else {
                // Si no existe, se crea la asociación (con los valores de la invitación o null)
                CourseDivisionUser::create([
                    'user_id'     => $user->id,
                    'course_id'   => $course_id,
                    'division_id' => $division_id,
                ]);
            }

            // Cargar la asociación y agregar los valores al objeto user
            $association = CourseDivisionUser::where('user_id', $user->id)->first();
            if ($association) {
                // Se asignan al objeto _user_ para que se retornen al frontend
                $user->course_id = $association->course_id;
                $user->division_id = $association->division_id;
            }

            // Generar token de autenticación
            $token = $user->createToken('GroupifyToken')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user'  => $user, // Incluye role_id para redirección en frontend
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Google Login Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Internal Server Error',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
