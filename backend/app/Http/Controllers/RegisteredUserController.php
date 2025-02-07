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
                'image'     => $request->image ?? 'http://localhost:8000/images/default.png',
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

    public function googleLogin(Request $request)
    {
        DB::beginTransaction();
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'email'     => 'required|email',
                'google_id' => 'required|string',
                'name'      => 'required|string',
                'last_name' => 'nullable|string',
                'image'     => 'nullable|string',
                'invitation_token' => 'nullable|exists:invitations,token',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors'  => $validator->errors()
                ], 400);
            }

            // Find or create user
            $user = User::firstOrNew(
                ['email' => $request->email],
                [
                    'name'      => $request->name,
                    'last_name' => $request->last_name ?? '',
                    'google_id' => $request->google_id,
                    'image'     => $request->image ?? '/images/default.png',
                    'password'  => Hash::make(Str::random(16)),
                    'role_id'   => 2,
                ]
            );

            // Handle invitation if present
            $course_id = null;
            $division_id = null;

            if ($request->has('invitation_token')) {
                $invitation = Invitation::where('token', $request->invitation_token)->first();

                if ($invitation) {
                    $course_id = $invitation->course_id;
                    $division_id = $invitation->division_id;
                }
            }

            // Ensure course-division-user association
            $existingAssociation = CourseDivisionUser::where('user_id', $user->id)->first();
            if (!$existingAssociation) {
                CourseDivisionUser::create([
                    'user_id'     => $user->id,
                    'course_id'   => $course_id,
                    'division_id' => $division_id,
                ]);
            }

            // Save or update user
            $user->save();

            // Generate token
            $token = $user->createToken('GroupifyToken')->plainTextToken;

            DB::commit();

            return response()->json([
                'token' => $token,
                'user'  => $user,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Internal server error',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
