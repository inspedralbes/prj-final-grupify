<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Group;
use Illuminate\Http\Request;

class BitacoraController extends Controller
{
    /**
     * Mostrar la lista de bitácoras.
     */
    public function index()
{
    $bitacoras = Bitacora::all();
    return response()->json($bitacoras); 
}


    /**
     * Mostrar el formulario para crear una nueva bitácora.
     */
    public function create()
    {
        // Obtener todos los grupos para asociarlos a la bitácora
        $groups = Group::all();

        return view('bitacoras.create', compact('groups'));
    }

    /**
     * Almacenar una nueva bitácora.
     */
    public function store(Request $request)
    {
        // Validar los datos
        $validated = $request->validate([
            'group_id' => 'required|exists:groups,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Crear la bitácora
        $bitacora = Bitacora::create($validated);

        // Puedes asociar usuarios a la bitácora aquí si lo deseas
        // $group = Group::find($request->group_id);
        // $bitacora->users()->sync($group->users->pluck('id')->toArray());

        return redirect()->route('bitacoras.index')->with('success', 'Bitácora creada exitosamente.');
    }

    /**
     * Mostrar una bitácora específica.
     */
    public function show($id)
{
    $bitacora = Bitacora::find($id);

    if (!$bitacora) {
        return response()->json(['error' => 'Bitácora no encontrada'], 404);
    }

    return response()->json($bitacora);
}

    /**
     * Mostrar el formulario para editar una bitácora.
     */
    public function edit($id)
    {
        $bitacora = Bitacora::findOrFail($id);
        $groups = Group::all();

        return view('bitacoras.edit', compact('bitacora', 'groups'));
    }

    /**
     * Actualizar la bitácora.
     */
    public function update(Request $request, $id)
    {
        // Validar los datos
        $validated = $request->validate([
            'group_id' => 'required|exists:groups,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Actualizar la bitácora
        $bitacora = Bitacora::findOrFail($id);
        $bitacora->update($validated);

        return redirect()->route('bitacoras.index')->with('success', 'Bitácora actualizada exitosamente.');
    }

    /**
     * Eliminar una bitácora.
     */
    public function destroy($id)
    {
        $bitacora = Bitacora::findOrFail($id);
        $bitacora->delete();

        return redirect()->route('bitacoras.index')->with('success', 'Bitácora eliminada exitosamente.');
    }

}
