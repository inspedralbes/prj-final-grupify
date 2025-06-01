<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\User;
use App\Models\FormUser;
use App\Models\CescRelationship;
use App\Models\TagCesc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class CescRelationshipController extends Controller
{
    /**
     * Obtener los usuarios que respondieron a un formulario Cesc.
     */
    public function getRespondedUsers($formId)
    {
        try {
            // Validar la existencia del formulario
            $form = Form::find($formId);
            if (!$form) {
                return response()->json(['message' => 'Formulario no encontrado'], 404);
            }

            // Obtener los usuarios que han respondido basado en la tabla form_user
            $respondedUserIds = \App\Models\FormUser::where('form_id', $formId)
                ->where('answered', true)
                ->pluck('user_id')
                ->unique()
                ->toArray();
                
            // También obtener los IDs de usuarios con relaciones CESC (como respaldo)
            $relationshipUserIds = CescRelationship::whereHas('question', function ($query) use ($formId) {
                $query->where('form_id', $formId);
            })
                ->pluck('user_id')
                ->unique()
                ->toArray();
                
            // Combinar ambos conjuntos de IDs para mayor certeza
            $userIds = array_unique(array_merge($respondedUserIds, $relationshipUserIds));

            // Verificar si existen usuarios
            if (empty($userIds)) {
                return response()->json(['message' => 'No hay usuarios que hayan respondido este formulario'], 404);
            }

            // Obtener los detalles de los usuarios
            $users = User::whereIn('id', $userIds)->get(['id', 'name', 'last_name']);

            // Retornar la lista de usuarios
            return response()->json($users, 200);
        } catch (\Exception $e) {
            // Manejar cualquier excepción
            return response()->json(['message' => 'Error interno en el servidor', 'error' => $e->getMessage()], 500);
        }
    }

    public function getAnswersByUser($formId, $userId)
    {
        try {
            // Verificar si el formulario existe
            $form = Form::find($formId);
            if (!$form) {
                return response()->json(['message' => 'Formulario no encontrado'], 404);
            }

            // Verificar si el usuario existe
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['message' => 'Usuario no encontrado'], 404);
            }

            // Obtener las preguntas del formulario 
            $questions = $form->questions()->get();
            
            if ($questions->isEmpty()) {
                return response()->json([
                    'form_title' => $form->title,
                    'user_name' => $user->name,
                    'user_lastname' => $user->last_name,
                    'user_image' => $user->image,
                    'relationships' => [],
                    'message' => 'No hay preguntas en este formulario'
                ], 200);
            }

            // Obtener las relaciones CESC donde el usuario es el que respondió (no el peer)
            $relationships = CescRelationship::where('user_id', $userId)
                ->whereIn('question_id', $questions->pluck('id'))
                ->with(['peer', 'question', 'tag'])
                ->get();

            // Si no hay relaciones, devolver una respuesta vacía
            if ($relationships->isEmpty()) {
                return response()->json([
                    'form_title' => $form->title,
                    'user_name' => $user->name,
                    'user_lastname' => $user->last_name,
                    'user_image' => $user->image,
                    'relationships' => [],
                    'message' => 'El usuario no ha completado el cuestionario CESC'
                ], 200);
            }

            // Agrupar las relaciones por question_id
            $groupedRelationships = [];
            
            foreach ($relationships as $relationship) {
                $questionId = $relationship->question_id;
                
                if (!isset($groupedRelationships[$questionId])) {
                    $groupedRelationships[$questionId] = [
                        'question_id' => $questionId,
                        'question_title' => $relationship->question ? $relationship->question->title : 'Pregunta sin título',
                        'peers' => []
                    ];
                }
                
                // Añadir el peer a la lista de peers de esta pregunta
                $peer = $relationship->peer;
                if ($peer) {
                    $groupedRelationships[$questionId]['peers'][] = [
                        'id' => $peer->id,
                        'name' => $peer->name,
                        'last_name' => $peer->last_name,
                        'tag_id' => $relationship->tag_id,
                        'tag_name' => $relationship->tag ? $relationship->tag->name : 'Etiqueta desconocida'
                    ];
                }
            }

            // Devolver las relaciones con el título del formulario
            return response()->json([
                'form_title' => $form->title,
                'user_name' => $user->name,
                'user_lastname' => $user->last_name,
                'user_image' => $user->image,
                'relationships' => $groupedRelationships,
            ], 200);
            
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Error en getAnswersByUser CESC: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            // Return a more informative error message
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage(),
                'trace' => $e->getTrace()
            ], 500);
        }
    }

    /**
     * Listar todas las relaciones Cesc.
     */
    public function index()
    {
        $relationships = CescRelationship::with(['user', 'peer', 'question', 'tag'])->get();
        return response()->json($relationships, 200);
    }

    /**
     * Filtrar relaciones por usuario que respondió.
     */
    public function byUser($userId)
    {
        $relationships = CescRelationship::where('user_id', $userId)
            ->with(['peer', 'question', 'tag'])
            ->get();

        return response()->json($relationships, 200);
    }

    /**
     * Guardar nuevas relaciones Cesc.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'form_id' => 'required|exists:forms,id', // Añadido: recibir el ID del formulario desde la solicitud
            'relationships' => 'required|array',
            'relationships.*.peer_id' => 'required|exists:users,id',
            'relationships.*.question_id' => 'required|exists:questions,id',
            'relationships.*.tag_id' => 'required|exists:tags_cesc,id',
        ]);

        // Iniciar una transacción para garantizar consistencia
        DB::beginTransaction();
        
        try {
            // Guardar cada relación individual
            foreach ($data['relationships'] as $relationship) {
                CescRelationship::create([
                    'user_id' => $data['user_id'],
                    'peer_id' => $relationship['peer_id'],
                    'question_id' => $relationship['question_id'],
                    'tag_id' => $relationship['tag_id'],
                ]);
            }
            
            // Obtener el formulario usando el ID proporcionado en la solicitud
            $form = Form::find($data['form_id']);
            if (!$form) {
                throw new \Exception("Formulario no encontrado con ID: " . $data['form_id']);
            }

            // Obtener el usuario
            $user = User::find($data['user_id']);
            if (!$user) {
                throw new \Exception("Usuario no encontrado.");
            }
            
            // Obtener el curso y división del usuario
            $courseDivision = DB::table('course_division_user')
                ->where('user_id', $data['user_id'])
                ->orderBy('id', 'desc')
                ->first();
                
            if (!$courseDivision) {
                throw new \Exception("Usuario sin curso/división asignados.");
            }
            
            // Actualizar o crear el registro en form_user con answered = 1
            DB::table('form_user')->updateOrInsert(
                [
                    'user_id' => $data['user_id'],
                    'form_id' => $form->id,
                    'course_id' => $courseDivision->course_id,
                    'division_id' => $courseDivision->division_id,
                ],
                [
                    'answered' => 1, // Marca como respondido (1 en lugar de true para asegurar compatibilidad)
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            
            // Actualizar el contador de respuestas en el formulario si es necesario
            if (Schema::hasTable('form_assignments')) {
                $formAssignment = \App\Models\FormAssignment::where('form_id', $form->id)
                    ->where('course_id', $courseDivision->course_id)
                    ->where('division_id', $courseDivision->division_id)
                    ->first();
                    
                if ($formAssignment) {
                    // Contar el número de usuarios que han respondido el formulario 
                    $answeredCount = DB::table('form_user')
                        ->where('form_id', $form->id)
                        ->where('course_id', $courseDivision->course_id)
                        ->where('division_id', $courseDivision->division_id)
                        ->where('answered', 1)
                        ->count();
                    
                    // Actualizar el contador de respuestas
                    $formAssignment->responses_count = $answeredCount;
                    $formAssignment->save();
                }
            }
            
            // Confirmar la transacción
            DB::commit();
            
            // Registrar la acción completada
            \Illuminate\Support\Facades\Log::info('Cesc completado', [
                'user_id' => $data['user_id'],
                'form_id' => $form->id,
                'course_id' => $courseDivision->course_id,
                'division_id' => $courseDivision->division_id
            ]);
            
            // Devolver una respuesta exitosa
            return response()->json(['message' => 'Relaciones guardadas correctamente y formulario marcado como respondido.'], 201);
            
        } catch (\Exception $e) {
            // Si hay algún error, revertir la transacción
            DB::rollback();
            
            // Registrar el error
            \Illuminate\Support\Facades\Log::error('Error al guardar relaciones CESC', [
                'error' => $e->getMessage(),
                'user_id' => $data['user_id'] ?? null,
                'form_id' => $data['form_id'] ?? null
            ]);
            
            // Devolver un mensaje de error
            return response()->json(['message' => 'Error al guardar las relaciones: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Obtener los resultados de CESC para todos los estudiantes.
     * 
     * Este método retorna los resultados agrupados por estudiante y etiqueta.
     */
    public function verResultados()
    {
        try {
            // Obtener todas las relaciones CESC con sus respectivas etiquetas
            $relationships = CescRelationship::with(['peer', 'tag'])
                ->whereHas('peer') // Asegurarse de que el peer exista
                ->whereHas('tag')  // Asegurarse de que la etiqueta exista
                ->get();
            
            if ($relationships->isEmpty()) {
                return response()->json([
                    'message' => 'No hay resultados CESC disponibles',
                    'data' => []
                ], 200);
            }
            
            // Agrupar resultados por peer_id y tag_id para contar menciones
            $results = [];
            foreach ($relationships as $rel) {
                $peerId = $rel->peer_id;
                $tagId = $rel->tag_id;
                
                // Inicializar si no existe
                if (!isset($results[$peerId])) {
                    $results[$peerId] = [
                        'peer_id' => $peerId,
                        'peer_name' => $rel->peer ? $rel->peer->name : 'Desconocido',
                        'peer_last_name' => $rel->peer ? $rel->peer->last_name : '',
                        'tags' => []
                    ];
                }
                
                // Incrementar contador para este tag
                if (!isset($results[$peerId]['tags'][$tagId])) {
                    $results[$peerId]['tags'][$tagId] = [
                        'tag_id' => $tagId,
                        'tag_name' => $rel->tag ? $rel->tag->name : 'Desconocido',
                        'count' => 1
                    ];
                } else {
                    $results[$peerId]['tags'][$tagId]['count']++;
                }
            }
            
            // Formatear resultados para la respuesta final
            $formattedResults = [];
            foreach ($results as $result) {
                foreach ($result['tags'] as $tag) {
                    $formattedResults[] = [
                        'peer_id' => $result['peer_id'],
                        'peer_name' => $result['peer_name'],
                        'peer_last_name' => $result['peer_last_name'],
                        'tag_id' => $tag['tag_id'],
                        'tag_name' => $tag['tag_name'],
                        'vote_count' => $tag['count'] // Renombrar 'count' a 'vote_count' para compatibilidad
                    ];
                }
            }
            
            return response()->json($formattedResults, 200);
            
        } catch (\Exception $e) {
            // Log del error
            \Log::error('Error al obtener resultados CESC: ' . $e->getMessage());
            
            // Retornar error
            return response()->json([
                'message' => 'Error al obtener resultados CESC',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener los resultados de CESC para un curso y división específicos.
     */
    public function verResultadosPorGrupo($id)
    {
        try {
            // Extraer course_id y division_id de la string $id (formato esperado: "course_id-division_id")
            $parts = explode('-', $id);
            if (count($parts) != 2) {
                return response()->json([
                    'message' => 'Formato de ID inválido. Use "course_id-division_id"'
                ], 400);
            }
            
            $courseId = $parts[0];
            $divisionId = $parts[1];
            
            // Obtener los IDs de roles de estudiante
            $studentRoleIds = DB::table('roles')->where('name', 'alumne')->pluck('id')->toArray();
            
            // Obtener los students_ids en este curso y división
            $studentIds = DB::table('course_division_user as cdu')
                ->join('users as u', 'cdu.user_id', '=', 'u.id')
                ->where('cdu.course_id', $courseId)
                ->where('cdu.division_id', $divisionId)
                ->whereIn('u.role_id', $studentRoleIds)
                ->pluck('cdu.user_id')
                ->toArray();
            
            if (empty($studentIds)) {
                return response()->json([
                    'message' => 'No hay estudiantes en este curso y división',
                    'data' => []
                ], 200);
            }
            
            // Obtener relaciones CESC donde los peers estén en la lista de students_ids
            $relationships = CescRelationship::with(['peer', 'tag'])
                ->whereIn('peer_id', $studentIds)
                ->whereHas('peer')
                ->whereHas('tag')
                ->get();
            
            if ($relationships->isEmpty()) {
                return response()->json([
                    'message' => 'No hay resultados CESC disponibles para este grupo',
                    'data' => []
                ], 200);
            }
            
            // Agrupar resultados por peer_id y tag_id para contar menciones
            $results = [];
            foreach ($relationships as $rel) {
                $peerId = $rel->peer_id;
                $tagId = $rel->tag_id;
                
                // Inicializar si no existe
                if (!isset($results[$peerId])) {
                    $results[$peerId] = [
                        'peer_id' => $peerId,
                        'peer_name' => $rel->peer ? $rel->peer->name : 'Desconocido',
                        'peer_last_name' => $rel->peer ? $rel->peer->last_name : '',
                        'tags' => []
                    ];
                }
                
                // Incrementar contador para este tag
                if (!isset($results[$peerId]['tags'][$tagId])) {
                    $results[$peerId]['tags'][$tagId] = [
                        'tag_id' => $tagId,
                        'tag_name' => $rel->tag ? $rel->tag->name : 'Desconocido',
                        'count' => 1
                    ];
                } else {
                    $results[$peerId]['tags'][$tagId]['count']++;
                }
            }
            
            // Formatear resultados para la respuesta final
            $formattedResults = [];
            foreach ($results as $result) {
                foreach ($result['tags'] as $tag) {
                    $formattedResults[] = [
                        'peer_id' => $result['peer_id'],
                        'peer_name' => $result['peer_name'],
                        'peer_last_name' => $result['peer_last_name'],
                        'tag_id' => $tag['tag_id'],
                        'tag_name' => $tag['tag_name'],
                        'vote_count' => $tag['count'] // Renombrar 'count' a 'vote_count' para compatibilidad
                    ];
                }
            }
            
            return response()->json($formattedResults, 200);
            
        } catch (\Exception $e) {
            // Log del error
            \Log::error('Error al obtener resultados CESC por grupo: ' . $e->getMessage());
            
            // Retornar error
            return response()->json([
                'message' => 'Error al obtener resultados CESC por grupo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener datos para los gráficos de tags CESC agrupados por cursos.
     * Esta función permite a los orientadores ver datos de todos los cursos de su nivel educativo.
     */
    public function getTagsGraphData() 
    {
        try {
            // Obtener el ID del rol de alumno
            $studentRoleIds = DB::table('roles')->where('name', 'alumne')->pluck('id')->toArray();
            
            // Obtener el usuario actual
            $userId = request()->header('X-Auth-User-Id');
            if (!$userId) {
                // Si no hay ID en los headers, intentar obtenerlo de session
                $userId = session('user_id');
            }
            
            if (!$userId) {
                // Si aún no tenemos ID, usamos un enfoque de fallback basado en el email
                // Asumimos que es el orientador por defecto
                $userEmail = 'orientador@test.com';
                $user = DB::table('users')->where('email', $userEmail)->first();
                if ($user) {
                    $userId = $user->id;
                }
            }
            
            \Log::info("Procesando solicitud para el usuario ID: {$userId}");
            
            // Determinar el nivel educativo del orientador basado en sus asignaciones de curso
            $nivelEducativo = 'eso'; // Por defecto, establecemos ESO
            
            if ($userId) {
                // Obtener los cursos asignados al orientador
                $cursos = DB::table('course_division_user as cdu')
                    ->join('courses as c', 'cdu.course_id', '=', 'c.id')
                    ->where('cdu.user_id', $userId)
                    ->pluck('c.name')
                    ->toArray();
                
                \Log::info("Cursos asignados al usuario: " . implode(', ', $cursos));
                
                // Verificar si hay cursos de bachillerato
                $hasBatxillerat = false;
                foreach ($cursos as $curso) {
                    if (strpos($curso, 'BATX') !== false || strpos($curso, 'BACHILLER') !== false) {
                        $hasBatxillerat = true;
                        break;
                    }
                }
                
                // Si tiene cursos de bachillerato, usamos ese nivel
                if ($hasBatxillerat) {
                    $nivelEducativo = 'bachillerato';
                }
            }
            
            \Log::info("Nivel educativo determinado: {$nivelEducativo}");
            
            // Obtener los cursos correspondientes al nivel educativo
            if ($nivelEducativo == 'eso') {
                $filteredCourseIds = DB::table('courses')
                    ->where('name', 'like', '%ESO%')
                    ->pluck('id')
                    ->toArray();
            } else {
                $filteredCourseIds = DB::table('courses')
                    ->where('name', 'like', '%BATX%')
                    ->orWhere('name', 'like', '%BACHILLER%')
                    ->pluck('id')
                    ->toArray();
            }
            
            \Log::info("Cursos filtrados para nivel {$nivelEducativo}: " . implode(', ', $filteredCourseIds));
            
            // Obtener TODOS los IDs de estudiantes del nivel educativo correspondiente, 
            // incluso los que no tienen relaciones CESC
            $allStudentIds = DB::table('course_division_user as cdu')
                ->join('users as u', 'cdu.user_id', '=', 'u.id')
                ->whereIn('cdu.course_id', $filteredCourseIds)
                ->whereIn('u.role_id', $studentRoleIds)
                ->select('u.id')
                ->distinct()
                ->pluck('id')
                ->toArray();
                
            \Log::info("Total de estudiantes encontrados en el nivel educativo {$nivelEducativo}: " . count($allStudentIds));
            
            // Obtener todas las relaciones CESC con sus respectivos tags donde el peer es un alumno del nivel educativo
            $relationships = CescRelationship::with(['peer', 'tag', 'question'])
                ->whereIn('peer_id', $allStudentIds)
                ->whereHas('peer', function ($query) use ($studentRoleIds) {
                    $query->whereIn('role_id', $studentRoleIds);
                })
                ->whereHas('tag')
                ->get();
                
            if ($relationships->isEmpty()) {
                return response()->json([], 200);
            }
            
            // Obtener información de los cursos y divisiones de cada estudiante
            $peerCourseInfo = [];
            
            foreach ($relationships as $rel) {
                $peerId = $rel->peer_id;
                
                // Si ya hemos obtenido la información del curso para este peer, saltamos
                if (isset($peerCourseInfo[$peerId])) {
                    continue;
                }
                
                // Obtener información del curso y división del peer
                $courseInfo = DB::table('course_division_user as cdu')
                    ->join('courses as c', 'cdu.course_id', '=', 'c.id')
                    ->join('divisions as d', 'cdu.division_id', '=', 'd.id')
                    ->where('cdu.user_id', $peerId)
                    ->select('cdu.course_id', 'c.name as course_name', 'cdu.division_id', 'd.division as division_name')
                    ->orderBy('cdu.id', 'desc')
                    ->first();
                    
                if ($courseInfo) {
                    $peerCourseInfo[$peerId] = [
                        'course_id' => $courseInfo->course_id,
                        'course_name' => $courseInfo->course_name,
                        'division_id' => $courseInfo->division_id,
                        'division_name' => $courseInfo->division_name
                    ];
                }
            }
            
            // Agrupar los conteos de tags por curso y división
            $tagCountsByCourse = [];
            
            // Primero, obtener todos los cursos/divisiones del nivel educativo
            $courseDivisions = DB::table('courses as c')
                ->join('divisions as d', function($join) {
                    $join->on(DB::raw('1'), '=', DB::raw('1')); // Cross join
                })
                ->whereIn('c.id', $filteredCourseIds)
                ->select('c.id as course_id', 'c.name as course_name', 'd.id as division_id', 'd.division as division_name')
                ->get();
                
            // Inicializar todos los cursos/divisiones con ceros
            foreach ($courseDivisions as $cd) {
                $key = "{$cd->course_id}-{$cd->division_id}";
                
                // Verificar si hay estudiantes en este curso/división
                $hasStudents = DB::table('course_division_user as cdu')
                    ->join('users as u', 'cdu.user_id', '=', 'u.id')
                    ->where('cdu.course_id', $cd->course_id)
                    ->where('cdu.division_id', $cd->division_id)
                    ->whereIn('u.role_id', $studentRoleIds)
                    ->exists();
                    
                if ($hasStudents) {
                    $tagCountsByCourse[$key] = [
                        'course_id' => $cd->course_id,
                        'course_name' => $cd->course_name,
                        'division_id' => $cd->division_id,
                        'division_name' => $cd->division_name,
                        'tag_1_count' => 0, // Popular
                        'tag_2_count' => 0, // Rechazado
                        'tag_3_count' => 0, // Agresivo
                        'tag_4_count' => 0, // Prosocial
                        'tag_5_count' => 0, // Víctima
                        'total_students' => 0,
                        'total_tags' => 0
                    ];
                }
            }
            
            foreach ($relationships as $rel) {
                $peerId = $rel->peer_id;
                $tagId = $rel->tag_id;
                
                // Si no tenemos información del curso para este peer, continuamos
                if (!isset($peerCourseInfo[$peerId])) {
                    continue;
                }
                
                $courseId = $peerCourseInfo[$peerId]['course_id'];
                $courseName = $peerCourseInfo[$peerId]['course_name'];
                $divisionId = $peerCourseInfo[$peerId]['division_id'];
                $divisionName = $peerCourseInfo[$peerId]['division_name'];
                
                // Crear clave única para este curso y división
                $key = "{$courseId}-{$divisionId}";
                
                // Inicializar el registro para este curso/división si no existe (no debería ocurrir porque ya inicializamos todos)
                if (!isset($tagCountsByCourse[$key])) {
                    $tagCountsByCourse[$key] = [
                        'course_id' => $courseId,
                        'course_name' => $courseName,
                        'division_id' => $divisionId,
                        'division_name' => $divisionName,
                        'tag_1_count' => 0, // Popular
                        'tag_2_count' => 0, // Rechazado
                        'tag_3_count' => 0, // Agresivo
                        'tag_4_count' => 0, // Prosocial
                        'tag_5_count' => 0, // Víctima
                        'total_students' => 0,
                        'total_tags' => 0
                    ];
                }
                
                // Incrementar el contador para el tag correspondiente
                $tagField = "tag_{$tagId}_count";
                $tagCountsByCourse[$key][$tagField]++;
                $tagCountsByCourse[$key]['total_tags']++;
            }
            
            // Obtener el número total de estudiantes para TODOS los cursos y divisiones
            $allCourseStudentCounts = DB::table('course_division_user as cdu')
                ->join('users as u', 'cdu.user_id', '=', 'u.id')
                ->whereIn('cdu.course_id', $filteredCourseIds)
                ->whereIn('u.role_id', $studentRoleIds)
                ->select('cdu.course_id', 'cdu.division_id', DB::raw('COUNT(DISTINCT u.id) as student_count'))
                ->groupBy('cdu.course_id', 'cdu.division_id')
                ->get()
                ->keyBy(function ($item) {
                    return "{$item->course_id}-{$item->division_id}";
                });
                
            // Actualizar los conteos de estudiantes para todos los cursos/divisiones
            foreach ($tagCountsByCourse as $key => &$courseData) {
                $count = $allCourseStudentCounts[$key]->student_count ?? 0;
                $courseData['total_students'] = $count;
                
                \Log::info("Curso: {$courseData['course_name']} {$courseData['division_name']}, Estudiantes: {$count}");
            }
            
            // Obtener el total de estudiantes únicos de todos los cursos/divisiones
            $totalUniqueStudents = count($allStudentIds);
            
            // Contabilizar el total general de estudiantes
            $totalStudentsCount = DB::table('course_division_user as cdu')
                ->join('users as u', 'cdu.user_id', '=', 'u.id')
                ->whereIn('u.role_id', $studentRoleIds)
                ->whereIn('cdu.course_id', $filteredCourseIds)
                ->count('cdu.user_id');
                
            \Log::info("Total de estudiantes (con duplicados): {$totalStudentsCount}");
            \Log::info("Total de estudiantes únicos: {$totalUniqueStudents}");
            
            // Convertir a array para la respuesta JSON
            $result = array_values($tagCountsByCourse);
            
            // Añadir metadatos para debugging y reconciliación de números
            $metadata = [
                'course_id' => 0,
                'course_name' => 'METADATA',
                'division_id' => 0,
                'division_name' => 'INFO',
                'tag_1_count' => 0,
                'tag_2_count' => 0,
                'tag_3_count' => 0,
                'tag_4_count' => 0,
                'tag_5_count' => 0,
                'total_students' => $totalUniqueStudents, // Total real de estudiantes ÚNICOS
                'total_tags' => 0,
                'is_metadata' => true,
            ];
            
            // Solo añadir metadatos en entorno de desarrollo
            if (app()->environment('local')) {
                array_unshift($result, $metadata);
            }
            
            // Log del total de estudiantes (suma por curso vs únicos)
            $courseSum = array_sum(array_column($result, 'total_students'));
            \Log::info("Total de estudiantes (suma por cursos): {$courseSum}");
            \Log::info("Total de estudiantes únicos: {$totalUniqueStudents}");
            
            return response()->json($result, 200);
            
        } catch (\Exception $e) {
            // Log del error
            \Log::error('Error al obtener datos para gráficos CESC: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            // Retornar error
            return response()->json([
                'message' => 'Error al obtener datos para gráficos CESC',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener los 5 mejores estudiantes para un tag específico en un curso/división
     */
    public function getTopStudentsByTag($courseId, $divisionId, $tagId)
    {
        try {
            // Obtener el ID del rol de alumno
            $studentRoleIds = DB::table('roles')->where('name', 'alumne')->pluck('id')->toArray();
            
            // Validar que el tag existe
            $tagExists = TagCesc::where('id', $tagId)->exists();
            if (!$tagExists) {
                return response()->json([
                    'message' => 'Tag no encontrado'
                ], 404);
            }
            
            // Obtener el usuario actual
            $userId = request()->header('X-Auth-User-Id');
            if (!$userId) {
                // Si no hay ID en los headers, intentar obtenerlo de session
                $userId = session('user_id');
            }
            
            if (!$userId) {
                // Si aún no tenemos ID, usamos un enfoque de fallback basado en el email
                // Asumimos que es el orientador por defecto
                $userEmail = 'orientador@test.com';
                $user = DB::table('users')->where('email', $userEmail)->first();
                if ($user) {
                    $userId = $user->id;
                }
            }
            
            // Determinar el nivel educativo del orientador basado en sus asignaciones de curso
            $nivelEducativo = 'eso'; // Por defecto, establecemos ESO
            
            if ($userId) {
                // Obtener los cursos asignados al orientador
                $cursos = DB::table('course_division_user as cdu')
                    ->join('courses as c', 'cdu.course_id', '=', 'c.id')
                    ->where('cdu.user_id', $userId)
                    ->pluck('c.name')
                    ->toArray();
                
                // Verificar si hay cursos de bachillerato
                $hasBatxillerat = false;
                foreach ($cursos as $curso) {
                    if (strpos($curso, 'BATX') !== false || strpos($curso, 'BACHILLER') !== false) {
                        $hasBatxillerat = true;
                        break;
                    }
                }
                
                // Si tiene cursos de bachillerato, usamos ese nivel
                if ($hasBatxillerat) {
                    $nivelEducativo = 'bachillerato';
                }
            }
            
            // Verificar que el curso solicitado pertenece al nivel educativo del orientador
            $courseName = DB::table('courses')->where('id', $courseId)->value('name');
            
            $courseNivelMatch = false;
            if ($nivelEducativo == 'eso' && strpos($courseName, 'ESO') !== false) {
                $courseNivelMatch = true;
            } elseif ($nivelEducativo == 'bachillerato' && (strpos($courseName, 'BATX') !== false || strpos($courseName, 'BACHILLER') !== false)) {
                $courseNivelMatch = true;
            }
            
            if (!$courseNivelMatch) {
                \Log::warning("El curso {$courseName} no pertenece al nivel educativo {$nivelEducativo} del orientador");
                return response()->json([
                    'message' => 'El curso solicitado no pertenece al nivel educativo asignado',
                    'students' => []
                ], 200);
            }
            
            // Obtener los IDs de los estudiantes en este curso/división
            $studentIds = DB::table('course_division_user as cdu')
                ->join('users as u', 'cdu.user_id', '=', 'u.id')
                ->where('cdu.course_id', $courseId)
                ->where('cdu.division_id', $divisionId)
                ->whereIn('u.role_id', $studentRoleIds)
                ->select('u.id')
                ->distinct()
                ->pluck('id')
                ->toArray();
                
            \Log::info('Estudiantes encontrados en curso/división:', [
                'course_id' => $courseId,
                'division_id' => $divisionId,
                'student_count' => count($studentIds)
            ]);
                
            if (empty($studentIds)) {
                return response()->json([
                    'message' => 'No hay estudiantes en este curso y división',
                    'students' => []
                ], 200);
            }
            
            // Obtener conteo de menciones por estudiante para este tag
            $studentCounts = DB::table('cesc_relationships as cr')
                ->join('users as u', 'cr.peer_id', '=', 'u.id')
                ->where('cr.tag_id', $tagId)
                ->whereIn('cr.peer_id', $studentIds)
                ->select('cr.peer_id', 'u.name', 'u.last_name', DB::raw('COUNT(*) as points'))
                ->groupBy('cr.peer_id', 'u.name', 'u.last_name')
                ->orderBy('points', 'desc')
                ->get();
                
            \Log::info('Estudiantes con menciones para el tag:', [
                'tag_id' => $tagId,
                'student_count' => $studentCounts->count()
            ]);
                
            // Devolver los resultados
            return response()->json([
                'students' => $studentCounts
            ], 200);
            
        } catch (\Exception $e) {
            // Log del error
            \Log::error('Error al obtener top estudiantes por tag: ' . $e->getMessage());
            
            // Retornar error
            return response()->json([
                'message' => 'Error al obtener estudiantes por tag',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
