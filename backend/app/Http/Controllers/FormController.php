<?php


namespace App\Http\Controllers;


use App\Models\Form;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\FormService;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\FormAssignedMail;
/**
 * @OA\Tag(
 *     name="Forms",
 *     description="Endpoints per gestionar els formularis"
 * )
 */
class FormController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/forms/active",
     *     summary="Obtener todos los formularios activos",
     *     tags={"Forms"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de formularios activos obtenida correctamente",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Form"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No hay formularios activos disponibles"
     *     )
     * )
     */
    public function getActiveForms()
    {
        // Obtener todos los formularios con estado activo (status = 1)
        $activeForms = Form::where('status', 1)->get();

        // Verificar si hay formularios activos
        if ($activeForms->isEmpty()) {
            return response()->json(['message' => 'No hay formularios activos disponibles'], 404);
        }

        // Devolver los formularios activos
        return response()->json($activeForms, 200);
    }
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
    
        $users = User::whereHas('divisions', function ($query) use ($courseId, $divisionId) {
            $query->where('course_id', $courseId)
                  ->where('division_id', $divisionId);
        })->get();
    
        if ($users->isEmpty()) {
            return response()->json(['message' => 'No se encontraron usuarios en esta combinación de curso y división.'], 404);
        }
    
        $form = Form::find($formId);
    

        foreach ($users as $user) {
            if (!$user->forms()->where('form_id', $formId)->exists()) {
                $user->forms()->attach($formId, [
                    'course_id' => $courseId,
                    'division_id' => $divisionId,
                ]);
    
                // Enviar correo al usuario
                Mail::to($user->email)->send(new FormAssignedMail($form, $user));
            }
        }
    
        return response()->json(['message' => 'Formulario asignado y notificación enviada correctamente.'], 200);
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

        // Check if this is a self-evaluation form and make it global
        if (stripos($validatedData['title'], 'autoevaluacion') !== false || 
            stripos($validatedData['title'], 'autoevaluación') !== false ||
            stripos($validatedData['title'], 'self-evaluation') !== false) {
            $validatedData['is_global'] = true;
        }

        // Set form creator ID
        if (!isset($validatedData['teacher_id'])) {
            $validatedData['teacher_id'] = auth()->id();
        }

        Log::info('Validated data: ' . json_encode($validatedData));

        $form = $this->formService->createForm($validatedData);

        return response()->json(['form' => $form], 201);
    }
    //verificar si el formulario esta completo (contestado por todos los alumnos de una clase)
    public function checkClassFormCompletion($course_id, $division_id, $form_id)
    {
        // Verificar el rol del usuario que está accediendo
        $userRole = auth()->user()->role->name;
        
        // Obtener información del formulario para verificar si es un sociograma o CESC
        $form = Form::find($form_id);
        if (!$form) {
            return response()->json(['message' => 'Formulario no encontrado'], 404);
        }
        
        $isSociogramOrCesc = stripos($form->title, 'sociograma') !== false || 
                            stripos($form->title, 'cesc') !== false;
        
        // Si es un sociograma o CESC y el usuario no es tutor ni admin, denegar acceso
        if ($isSociogramOrCesc && $userRole !== 'tutor' && $userRole !== 'admin') {
            return response()->json(['message' => 'Solo tutores pueden verificar formularios de sociograma y CESC.'], 403);
        }
        
        // Si no es el creador del formulario ni el formulario es global, denegar acceso
        if ($userRole === 'profesor' && $form->teacher_id != auth()->id() && !$form->is_global) {
            return response()->json(['message' => 'Solo puedes verificar formularios que has creado.'], 403);
        }
        
        // El orientador solo puede ver los formularios que ha creado él mismo
        if ($userRole === 'orientador' && $form->teacher_id != auth()->id()) {
            return response()->json(['message' => 'Como orientador, solo puedes verificar formularios que has creado.'], 403);
        }
        
        // Contar el total de estudiantes de la clase
        $studentsCount = DB::table('course_division_user')
            ->join('users', 'course_division_user.user_id', '=', 'users.id')
            ->where('course_division_user.course_id', $course_id)
            ->where('course_division_user.division_id', $division_id)
            ->where('users.role_id', 2)  // Filtrar solo estudiantes
            ->count();
            
        // Contar cuántos estudiantes de esa clase han respondido el formulario
        $answeredCount = DB::table('form_user')
            ->where('form_user.form_id', $form_id)
            ->where('form_user.course_id', $course_id)
            ->where('form_user.division_id', $division_id)
            ->where('form_user.answered', 1)
            ->count();
            
        // Obtener la lista de estudiantes que han respondido y que no han respondido
        // Para tutores (que pueden ver detalles de sociogramas/CESC) y administradores
        if ($userRole === 'tutor' || $userRole === 'admin') {
            $studentsAnswered = DB::table('form_user')
                ->join('users', 'form_user.user_id', '=', 'users.id')
                ->where('form_user.form_id', $form_id)
                ->where('form_user.course_id', $course_id)
                ->where('form_user.division_id', $division_id)
                ->where('form_user.answered', 1)
                ->where('users.role_id', 2) // Solo estudiantes
                ->select('users.id', 'users.name', 'users.last_name', 'users.email')
                ->get();
                
            $studentsNotAnswered = DB::table('course_division_user')
                ->join('users', 'course_division_user.user_id', '=', 'users.id')
                ->leftJoin('form_user', function ($join) use ($form_id) {
                    $join->on('users.id', '=', 'form_user.user_id')
                         ->where('form_user.form_id', '=', $form_id)
                         ->where('form_user.answered', '=', 1);
                })
                ->whereNull('form_user.user_id')
                ->where('course_division_user.course_id', $course_id)
                ->where('course_division_user.division_id', $division_id)
                ->where('users.role_id', 2) // Solo estudiantes
                ->select('users.id', 'users.name', 'users.last_name', 'users.email')
                ->get();
                
            return response()->json([
                'all_answered' => $studentsCount === $answeredCount,
                'total_students' => $studentsCount,
                'answered_count' => $answeredCount,
                'pending_count' => $studentsCount - $answeredCount,
                'students_answered' => $studentsAnswered,
                'students_not_answered' => $studentsNotAnswered
            ]);
        } else {
            // Para profesores y orientadores, dar información limitada (solo contadores y estadísticas)
            $studentsStatus = DB::table('course_division_user')
                ->join('users', 'course_division_user.user_id', '=', 'users.id')
                ->leftJoin('form_user', function ($join) use ($form_id) {
                    $join->on('users.id', '=', 'form_user.user_id')
                         ->where('form_user.form_id', '=', $form_id);
                })
                ->where('course_division_user.course_id', $course_id)
                ->where('course_division_user.division_id', $division_id)
                ->where('users.role_id', 2) // Solo estudiantes
                ->select('users.id', 'users.name', 'users.last_name', 
                         DB::raw('CASE WHEN form_user.answered = 1 THEN true ELSE false END as has_answered'))
                ->get();
                
            return response()->json([
                'all_answered' => $studentsCount === $answeredCount,
                'total_students' => $studentsCount,
                'answered_count' => $answeredCount,
                'pending_count' => $studentsCount - $answeredCount,
                'students_status' => $studentsStatus
            ]);
        }
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
        // Obtener el usuario autenticado
        $user = auth()->user();
        $userRole = $user ? $user->role->name : null;
        $userId = $user ? $user->id : null;

        // Comienza la consulta para obtener los formularios
        $query = Form::with('questions.answers'); // Incluye preguntas y respuestas

        // Filtros según el rol del usuario:
        if ($userRole === 'profesor') {
            // Profesores solo pueden ver sus propios formularios y los globales
            $query->where(function($q) use ($userId) {
                $q->where('teacher_id', $userId)
                  ->orWhere('is_global', 1);
            });
        } 
        elseif ($userRole === 'tutor') {
            // Tutores pueden ver todos los formularios, incluyendo sociogramas y CESC
            // No necesita restricción adicional aquí
        }
        elseif ($userRole === 'orientador') {
            // Orientadores pueden ver sus propios formularios y los globales
            $query->where(function($q) use ($userId) {
                $q->where('teacher_id', $userId)
                  ->orWhere('is_global', 1);
            });
        }
        elseif (!$userRole) {
            // Si no hay usuario autenticado, solo mostrar formularios globales
            $query->where('is_global', 1);
        }
        // Si es admin, puede ver todos los formularios (no necesita restricción)

        // Obtener los formularios filtrados
        $forms = $query->get();

        // Verificar si la solicitud espera una respuesta JSON
        if ($request->expectsJson()) {
            return response()->json($forms, 200);
        }
        
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
