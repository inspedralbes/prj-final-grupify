<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OptionService;

class OptionController extends Controller
{
    protected $optionService;

    public function __construct(OptionService $optionService)
    {
        $this->optionService = $optionService;
    }

    public function index()
    {
        $options = $this->optionService->getAllOptions();
        return response()->json($options);
    }

    public function show($id)
    {
        $option = $this->optionService->getOptionById($id);

        if (!$option) {
            return response()->json(['message' => 'Opció no trobada'], 404);
        }

        return response()->json($option);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'text' => 'required|string|max:255',
        ]);

        $option = $this->optionService->createOption($validatedData);
        return response()->json($option, 201);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'text' => 'sometimes|required|string|max:255',
        ]);

        $option = $this->optionService->updateOption($id, $validatedData);
        return response()->json($option, 200);
    }

    public function destroy($id)
    {
        $this->optionService->deleteOption($id);
        return response()->json(null, 204);
    }
}
