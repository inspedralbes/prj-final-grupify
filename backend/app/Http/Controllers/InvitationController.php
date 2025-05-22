<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invitation;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    /**
     * Crea una nueva invitación.
     * 
     * Endpoint: POST /api/invitations  
     * Requiere autenticación (el profesor autenticado genera la invitación)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id'   => 'required|exists:courses,id',
            'division_id' => 'required|exists:divisions,id',
        ]);

        $teacher = $request->user();

        $token = Str::random(32);

        $invitation = Invitation::create([
            'teacher_id'  => $teacher->id,
            'course_id'   => $validated['course_id'],
            'division_id' => $validated['division_id'],
            'token'       => $token
        ]);

        // Use frontend URL instead of localhost:8000
        $invitationLink = 'https://grupify.cat' . '/login?invitation=' . $token;

        return response()->json([
            'message'    => 'Invitación creada correctamente.',
            'invitation' => $invitation,
            'link'       => $invitationLink,
        ], 201);
    }

    /**
     * Lista las invitaciones del profesor autenticado.
     * 
     * Endpoint: GET /api/invitations  
     */
    public function index(Request $request)
    {
        $teacher = $request->user();
        $invitations = Invitation::where('teacher_id', $teacher->id)
            ->with(['course', 'division'])
            ->get();

        return response()->json($invitations);
    }

    /**
     * Muestra los detalles de una invitación a partir de su token.
     * 
     * Endpoint: GET /api/invitations/{token}
     */
    public function show($token)
    {
        $invitation = Invitation::where('token', $token)
            ->with(['course', 'division', 'teacher'])
            ->first();

        if (!$invitation) {
            return response()->json(['message' => 'Invitación no encontrada.'], 404);
        }

        return response()->json($invitation);
    }
}
