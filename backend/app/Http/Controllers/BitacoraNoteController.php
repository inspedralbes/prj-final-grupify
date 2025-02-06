<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BitacoraNote;
use App\Models\Bitacora;
use App\Models\Group;

class BitacoraNoteController extends Controller
{
    /**
     * Mostrar todas las notas de una bitácora.
     */
    public function index($bitacoraId)
    {
        $notes = BitacoraNote::where('bitacora_id', $bitacoraId)
                             ->with('user:id,name,last_name')
                             ->orderBy('created_at', 'desc')
                             ->get();

        // Asegúrate de que se esté devolviendo JSON
        return response()->json($notes);
    }

    /**
     * Almacenar una nueva nota.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bitacora_id' => 'required|exists:bitacoras,id',
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $note = BitacoraNote::create($validated);

        return response()->json([
            'message' => 'Nota creada exitosamente',
            'note' => $note->load('user:id,name,last_name')
        ], 201);
    }

    /**
     * Mostrar una nota específica.
     */
    public function show($id)
    {
        $note = BitacoraNote::with('user:id,name,last_name')
                            ->findOrFail($id);

        return response()->json($note);
    }

    /**
    * Actualizar una nota específica.
    */

    public function update(Request $request, $bitacoraId, $id)
    {
        $note = BitacoraNote::where('bitacora_id', $bitacoraId)
                            ->where('id', $id)
                            ->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $note->update($validated);

        return response()->json([
            'message' => 'Nota actualizada exitosamente',
            'note' => $note->load('user:id,name,last_name')
        ]);
    }

    /**
     * Eliminar una nota específica.
     */
    public function destroy($bitacoraId, $id)
    {
        $note = BitacoraNote::where('bitacora_id', $bitacoraId)->find($id);

        if (!$note) {
            return response()->json(['error' => 'Nota no encontrada'], 404);
        }
        $note->delete();

        return response()->json(['message' => 'Nota eliminada correctamente'], 200);
    }
    /**
     * Obtener todas las notas de un usuario específico en una bitácora.
     */
    public function getUserNotes($bitacoraId, $userId)
    {
        $notes = BitacoraNote::where('bitacora_id', $bitacoraId)
                             ->where('user_id', $userId)
                             ->with('user:id,name,last_name')
                             ->orderBy('created_at', 'desc')
                             ->get();

        return response()->json($notes);
    }

    /**
     * Obtener el conteo de notas por usuario en una bitácora.
     */
    public function getNoteCountByUser($bitacoraId)
    {
        $notesCount = BitacoraNote::where('bitacora_id', $bitacoraId)
                                  ->selectRaw('user_id, COUNT(*) as total_notes')
                                  ->groupBy('user_id')
                                  ->with('user:id,name,last_name')
                                  ->get();

        return response()->json($notesCount);
    }

    public function getNotes($groupId)
    {
        // Obtén las notas del grupo
        $bitacora = Bitacora::find($groupId);
        if (!$bitacora) {
            return response()->json(['error' => 'Bitácora no encontrada'], 404);
        }

        $notes = $bitacora->notes; 
        return response()->json($notes);
    }

}
