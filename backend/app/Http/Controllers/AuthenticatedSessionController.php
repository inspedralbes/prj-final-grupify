<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Mail\LoginNotificationMail;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

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
            'invitation_token' => 'nullable|exists:invitations,token',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();

        // Revocar tokens existentes
        $user->tokens()->delete();

        // Crear nuevo token
        $token = $user->createToken('Groupify')->plainTextToken;

        // Procesar token de invitación si existe
        if ($request->has('invitation_token')) {
            $invitation = \App\Models\Invitation::where('token', $request->invitation_token)->first();
            
            if ($invitation) {
                // Actualizar o crear la asignación de curso y división
                $existingAssignment = \App\Models\CourseDivisionUser::where('user_id', $user->id)->first();
                
                if ($existingAssignment) {
                    $existingAssignment->update([
                        'course_id' => $invitation->course_id,
                        'division_id' => $invitation->division_id
                    ]);
                } else {
                    \App\Models\CourseDivisionUser::create([
                        'user_id' => $user->id,
                        'course_id' => $invitation->course_id,
                        'division_id' => $invitation->division_id
                    ]);
                }
            }
        }

        // Cargar las relaciones necesarias
        $user->load(['forms', 'subjects', 'role', 'courseDivisions', 'courseDivisionUsers.course', 'courseDivisionUsers.division']);

        // Recarga el usuario después de posibles cambios por la invitación
        $user = User::with(['forms', 'subjects', 'role', 'courseDivisions', 'courseDivisionUsers.course', 'courseDivisionUsers.division'])->find($user->id);

        // Para todos los usuarios: extraer el primer course_id y division_id para compatibilidad
        $courseDivision = $user->courseDivisions->first(); // Obtenemos solo el primer curso/división
        $course_id = $courseDivision ? $courseDivision->pivot->course_id : null;
        $division_id = $courseDivision ? $courseDivision->pivot->division_id : null;

        // Cargar los nombres del curso y división
        $course_name = null;
        $division_name = null;
        
        if ($course_id) {
            $course = \App\Models\Course::find($course_id);
            $course_name = $course ? $course->name : null;
        }
        
        if ($division_id) {
            $division = \App\Models\Division::find($division_id);
            $division_name = $division ? $division->division : null;
        }
        
        // Preparar course_divisions para profesores
        $course_divisions = null;
        if ($user->role_id == 1) { // Si es profesor
            $course_divisions = $user->courseDivisionUsers->map(function($cdu) {
                return [
                    'course_id' => $cdu->course_id,
                    'course_name' => $cdu->course?->name ?? null,
                    'division_id' => $cdu->division_id,
                    'division_name' => $cdu->division?->division ?? null,
                ];
            });
        }


        Mail::to($user->email)->send(new LoginNotificationMail($user));


        // Preparar la respuesta según el rol
        $userData = [
            'id' => $user->id,
            'image' => $user->image,
            'name' => $user->name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'role_id' => $user->role_id,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'status' => $user->status,
            'course_id' => $course_id,
            'division_id' => $division_id,
            'course_name' => $course_name,
            'division_name' => $division_name,
            'forms' => $user->forms,
            'subjects' => $user->subjects,
            'role' => $user->role
        ];
        
        // Si es profesor, agregar course_divisions
        if ($user->role_id == 1 && $course_divisions !== null) {
            $userData['course_divisions'] = $course_divisions;
        }
        
        return response()->json([
            'token' => $token,
            'role' => $user->role->name,
            'user' => $userData
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
        try {
            if ($request->user()) {
                // Revoca solo el token actual
                $request->user()->currentAccessToken()->delete();
            }

            return response()->json([
                'message' => 'Sessió tancada correctament'
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error durante el logout: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al tancar la sessió'
            ], 500);
        }
    }
}
