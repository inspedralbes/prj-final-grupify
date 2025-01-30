<?php


namespace App\Http\Controllers;


use App\Models\Form;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\FormService;
use Illuminate\Support\Facades\Log;
use App\Models\User;


/**
 * @OA\Tag(
 *     name="Forms",
 *     description="Endpoints per gestionar els formularis"
 * )
 */
class FormController extends Controller
{
    public function assignFormToCourseAndDivision(Request $request)
{
    // Validar los datos
    $validated = $request->validate([
        'course_id' => 'required|exists:courses,id',
        'division_id' => 'required|exists:divisions,id',
        'form_id' => 'required|exists:forms,id',
    ]);

    $courseId = $validated['course_id'];
    $divisionId = $validated['division_id'];
    $formId = $validated['form_id'];

    // Obtener los usuarios que están en ese curso y división
    $users = User::whereHas('divisions', function ($query) use ($courseId, $divisionId) {
        $query->where('course_id', $courseId)
            ->where('division_id', $divisionId);
    })->get();

    if ($users->isEmpty()) {
        return response()->json(['message' => 'No se encontraron usuarios en esta combinación de curso y división.'], 404);
    }

    // Asignar el formulario a los usuarios encontrados
    foreach ($users as $user) {
        // Comprobamos si ya está asignado para evitar duplicados
        if (!$user->forms()->where('form_id', $formId)->exists()) {
            $user->forms()->attach($formId, [
                'course_id' => $courseId,
                'division_id' => $divisionId,
            ]);
        }
    }

    return response()->json(['message' => 'Formulario asignado correctamente a los usuarios.'], 200);
}



    public function updateFormStatus(Request $request, $formId)
    {
        $form = Form::find($formId);

        if (is_null($form)) {
            return response()->json(['message' => 'Formulario no encontrado'], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:0,1',  // 0 = desactivado, 1 = activo
        ]);

        // Actualizar el estado del formulario
        $form->status = $validated['status'];
        $form->save();

        return response()->json(['message' => 'Estado del formulario actualizado correctamente']);
    }




    public function getQuestions($formId)
    {
        // Cargar el formulario con las preguntas y las opciones de cada pregunta
        $form = Form::with('questions.options')->find($formId);

        // Verificar si el formulario existe
        if (!$form) {
            return response()->json(['message' => 'Formulario no encontrado'], 404);
        }

        // Extraer solo las preguntas con sus opciones
        $questions = $form->questions;

        // Devolver las preguntas y opciones
        return response()->json($questions, 200);
    }


    public function getFormsByUserId($userId)
    {
        // Buscar al usuario junto con sus formularios y el campo 'answered' de la tabla pivot
        $user = User::with(['forms' => function ($query) {
            $query->where('status', 1) // Filtrar solo formularios activos
                ->withPivot('answered'); // Incluir el campo 'answered' de la tabla pivot
        }])->find($userId);

        // Verificar si el usuario existe
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // Obtener los formularios del usuario con la columna 'answered' de la tabla pivot
        $forms = $user->forms->map(function ($form) {
            return [
                'id' => $form->id,
                'title' => $form->title,
                'answered' => $form->pivot->answered, // Acceder al valor de 'answered' en la tabla pivot
            ];
        });

        // Devolver los formularios y su estado 'answered'
        return response()->json($forms);
    }




    public function assignFormToUser(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'form_id' => 'required|exists:forms,id',
        ]);

        $user = User::find($validatedData['user_id']);
        $form = Form::find($validatedData['form_id']);

        // Asocia el formulario al usuario en la tabla intermedia
        $user->forms()->attach($form->id);

        return response()->json(['message' => 'Formulario asignado correctamente al usuario.'], 200);
    }


    protected $formService;

    public function __construct(FormService $formService)
    {
        $this->formService = $formService;
    }

    public function storeFormWithQuestions(Request $request, FormService $formService)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|unique:forms,title',
            'description' => 'nullable|string',
            'questions' => 'required|array',
            'questions.*.title' => 'required|string',
            'questions.*.type' => 'required|string|in:text,number,multiple,checkbox',
            'questions.*.placeholder' => 'nullable|string',
            'questions.*.context' => 'nullable|string',
            'questions.*.options' => 'nullable|array',
            'questions.*.options.*.text' => 'required_with:questions.*.options|string',
            'questions.*.options.*.value' => 'nullable|integer',
            'teacher_id' => 'nullable|exists:users,id',
            'is_global' => 'nullable|boolean',
            'date_limit' => 'required|date',
            'time_limit' => 'nullable|date_format:H:i',
        ]);

        Log::info('Validated data: ' . json_encode($validatedData));

        $form = $this->formService->createForm($validatedData);

        return response()->json(['form' => $form], 201);
    }



    /**
     * @OA\Get(
     *     path="/api/forms/{formId}/questions-and-answers",
     *     summary="Obtenir preguntes i respostes d'un formulari",
     *     tags={"Forms"},
     *     @OA\Parameter(
     *         name="formId",
     *         in="path",
     *         required=true,
     *         description="ID del formulari",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Preguntes i respostes obtingudes correctament",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Formulari no trobat"
     *     )
     * )
     */
    public function getQuestionsAndAnswers($formId)
    {
        $form = Form::with('questions.answers')->find($formId);

        if (!$form) {
            return response()->json(['message' => 'Formulario no encontrado'], 404);
        }

        return response()->json($form, 200);
    }



    /**
     * @OA\Get(
     *     path="/api/forms",
     *     summary="Llistar tots els formularis",
     *     tags={"Forms"},
     *     @OA\Response(
     *         response=200,
     *         description="Llista de formularis obtinguda correctament",
     *     )
     * )
     */

    public function index(Request $request)
    {
        // Obtener el teacher_id del usuario autenticado o de la solicitud
        $teacherId = $request->input('teacher_id');

        // Comienza la consulta para obtener los formularios
        $query = Form::with('questions.answers'); // Incluye preguntas y respuestas

        // Filtros:
        // 1. Filtrar por teacher_id si se pasa en la solicitud
        if ($teacherId) {
            $query->where('teacher_id', $teacherId);
        }

        // 2. Incluir también los formularios donde is_global es 1
        $query->orWhere('is_global', 1);

        // Obtener los formularios filtrados
        $forms = $query->get();

         // Verificar si la solicitud espera una respuesta JSON
         if ($request->expectsJson()) {
             return response()->json($forms, 200);
         }
         $forms = Form::all();
         // Si no es una solicitud JSON, se devuelve la vista
         return view('forms', compact('forms'));
     }







    /**
     * @OA\Get(
     *     path="/api/forms/{id}",
     *     summary="Obtenir un formulari específic per ID",
     *     tags={"Forms"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del formulari",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dades del formulari",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Formulari no trobat"
     *     )
     * )
     */


    public function show(Request $request, $id)
    {
        // Obtener el formulario con sus preguntas y respuestas, solo si el formulario está activo
        $form = Form::with(['questions.answers'])->where('id', $id)->where('status', 1)->first();

        if (is_null($form)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Formulario no encontrado o desactivado'], 404);
            }

            return redirect()->route('forms.index')->with('error', 'Formulario no encontrado o desactivado');
        }

        // Preparar las preguntas con sus respuestas
        $questions = $form->questions->map(function ($question) {
            return [
                'question' => $question->title,
                'answers' => $question->answers->pluck('content')->toArray(), // Obtenemos las respuestas como array
            ];
        });

        // Pasar el formulario y las preguntas a la vista
        return view('questions', compact('questions', 'form'));
    }






    /**
     * @OA\Post(
     *     path="/api/forms",
     *     summary="Crear un nou formulari",
     *     tags={"Forms"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", maxLength=255, example="Formulari d'exemple")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Formulari creat correctament",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validació"
     *     )
     * )
     */
    /**
     * Guardar un nuevo formulario
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
        ]);


        $form = Form::create($validatedData);


        if ($request->expectsJson()) {
            return response()->json($form, 201);
        }


        return redirect()->route('forms.index')->with('success', 'Formulario creado correctamente');
    }


    /**
     * @OA\Put(
     *     path="/api/forms/{id}",
     *     summary="Actualitzar un formulari existent",
     *     tags={"Forms"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del formulari a actualitzar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", maxLength=255, example="Títol actualitzat")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Formulari actualitzat correctament",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validació"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $form = Form::find($id);


        if (is_null($form)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Formulario no encontrado'], 404);
            }


            return redirect()->route('forms.index')->with('error', 'Formulario no encontrado');
        }


        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
        ]);


        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json($validator->errors(), 400);
            }


            return redirect()->back()->withErrors($validator)->withInput();
        }


        $form->update($validator->validated());


        if ($request->expectsJson()) {
            return response()->json($form, 200);
        }


        return redirect()->route('forms.index')->with('success', 'Formulario actualizado correctamente');
    }


    /**
     * @OA\Delete(
     *     path="/api/forms/{id}",
     *     summary="Eliminar un formulari",
     *     tags={"Forms"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del formulari a eliminar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Formulari eliminat correctament"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Formulari no trobat"
     *     )
     * )
     */
    public function destroy(Request $request, $id)
    {
        $form = Form::find($id);


        if (is_null($form)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Formulario no encontrado'], 404);
            }


            return redirect()->route('forms.index')->with('error', 'Formulario no encontrado');
        }


        $form->delete();


        if ($request->expectsJson()) {
            return response()->json(null, 204);
        }


        return redirect()->route('forms.index')->with('success', 'Formulario eliminado correctamente');
    }
}
