<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RoleController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\RegisteredUserController;
use Illuminate\Auth\Events\Authenticated;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SociogramRelationshipController;
use App\Http\Controllers\CescRelationshipController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CourseDivisionUserController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\BitacoraNoteController;
use App\Http\Controllers\InvitationController;

Route::put('user/{id}/status', [UserController::class, 'updateStatus']);

Route::get('/questions/{questionId}/average-rating', [AnswerController::class, 'getAverageRating']);
// Ruta pública (sin autenticación) para verificar si el formulario ha sido completado
Route::get('/check-form-completion-public/{course_id}/{division_id}/{form_id}', 
    [App\Http\Controllers\FormController::class, 'checkFormCompletionPublic']);

// RURTAS SIN USOO 

// Route::middleware(['auth:sanctum', 'role:admin'])->get('/admin-dashboard', [DashboardController::class, 'adminDashboard']);
// Route::middleware(['auth:sanctum', 'role:teacher'])->get('/teacher-dashboard', [DashboardController::class, 'teacherDashboard']);
// Route::middleware(['auth:sanctum', 'role:student'])->get('/student-dashboard', [DashboardController::class, 'studentDashboard']);

// Asegurará que la solicitud sea procesada como una solicitud de la API
Route::middleware('api')->resource('courses', CourseController::class);

// Rutas para roles
Route::get('roles', [RoleController::class, 'index']); // Todos pueden ver los roles
Route::get('roles/{id}', [RoleController::class, 'show']); // Todos pueden ver los detalles de un rol
// Solo los administradores pueden crear, editar y eliminar roles
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('roles', [RoleController::class, 'store']);
    Route::put('roles/{id}', [RoleController::class, 'update']);
    Route::delete('roles/{id}', [RoleController::class, 'destroy']);
});
Route::resource('courses', CourseController::class);
Route::get('/courses-with-divisions', [CourseController::class, 'getCoursesWithDivisions']);

// Rutas para CourseDivisionUser
Route::apiResource('course-division-user', CourseDivisionUserController::class);
Route::post('/course-division-user/bulk', [CourseDivisionUserController::class, 'bulkAssign']);
Route::post('/course-division-user/users', [CourseDivisionUserController::class, 'getUsersByCourseAndDivision']);

Route::resource('subjects', SubjectController::class);

// CRUD USERS
Route::resource('users', UserController::class)->names([
    'index' => 'users.index',
    'create' => 'users.create',
    'store' => 'users.store',
    'show' => 'users.show',
    'edit' => 'users.edit',
    'update' => 'users.update',
    'destroy' => 'users.destroy'
]);

// Ruta para asignar un rol a un usuario (solo admin puede hacerlo)
Route::middleware(['auth:sanctum', 'role:admin'])->put('/users/{id}/role', [UserController::class, 'assignRole']);

// Ruta para asignar curso y división a un usuario
Route::post('/users/{userId}/assign-course-division', [UserController::class, 'assignCourseAndDivision'])->name('api.users.assignCourseDivision');

// Ruta para obtener los cursos de un usuario
Route::get('/users/{id}/courses', [UserController::class, 'getUserCourses']);

// RUTA PARA GUARDAR FORMULARIO EN BBDD
Route::post('forms-save', [FormController::class, 'storeFormWithQuestions']);

// RUTA PARA ASIGNAR FORMULARIO A USUARIO
Route::middleware(['auth:sanctum', 'role:profesor,tutor,admin'])->post('/assign-form-to-user', [FormController::class, 'assignFormToUser']);

// RUTA PARA OBTENER FORMULARIOS DE USUARIO
Route::get('/forms/user/{userId}', [FormController::class, 'getFormsByUserId']);

// RUTA PARA OBTENER PREGUNTAS CON RESPUESTAS DE UN FORMULARIO
Route::get('/forms/{formId}/questions-and-answers', [FormController::class, 'getQuestions']);

Route::post('/forms/{formId}/submit-responses', [AnswerController::class, 'submitResponses']);

// RUTA PARA ACTUALIZAR ESTADO DE FORMULARIO
Route::patch('/forms/{formId}/status', [FormController::class, 'updateFormStatus']);

// RUTA PARA OBTENER USUARIOS QUE HAN RESPONDIDO UN FORMULARIO
Route::get('/forms/{formId}/users', [AnswerController::class, 'getUsersByForm']);

// RUTA PARA PROFESORES: Ver estado de respuestas sin ver el contenido
Route::middleware(['auth:sanctum', 'role:profesor,tutor,admin'])->post('/forms/response-status', [AnswerController::class, 'getFormResponseStatus']);

// RUTA PARA OBTENER RESPUESTAS DE UN USUARIO A UN FORMULARIO
Route::middleware(['auth:sanctum', 'role:orientador,admin'])->get('/forms/{formId}/users/{userId}/answers', [AnswerController::class, 'getAnswersByUser']);

// RUTA PARA OBTENER USUARIOS QUE HAN RESPONDIDO SOCIOGRAMA
Route::middleware(['auth:sanctum'])->get('/forms/{formId}/responded-users', [SociogramRelationshipController::class, 'getRespondedUsers']);

// RUTA PARA OBTENER USUARIOS QUE HAN RESPONDIDO CESC
Route::middleware(['auth:sanctum'])->get('/forms/{formId}/responded-users-cesc', [CescRelationshipController::class, 'getRespondedUsers']);

// RUTA PARA OBTENER RESPUESTA DE UN USUARIO DEL SOCIOGRAMA
Route::middleware(['auth:sanctum', 'role:tutor,admin'])->get('/forms/{formId}/users/{userId}/relationships', [SociogramRelationshipController::class, 'getAnswersByUser']);

// RUTA PARA OBTENER RESPUESTA DE UN USUARIO DEL CESC
Route::middleware(['auth:sanctum', 'role:tutor,admin'])->get('/forms/{formId}/users/{userId}/relationships-cesc', [CescRelationshipController::class, 'getAnswersByUser']);

// RUTA PARA OBTENER DIVISIONES SEGUN COURSE
Route::get('/course-divisions', [CourseController::class, 'getDivisionsByCourse']);

//RUTA PARA OBTENER LAS RESPUESTA DE SOCIOGRAMADA (TODA)
Route::get('/forms/all-responses-sociogram', [SociogramRelationshipController::class, 'getAllResponses']);

//RUTA PARA OBTENER LAS RESPUESTA DEL CESC (TODA)
Route::get('/forms/all-responses-cesc', [CescRelationshipController::class, 'getAllResponses']);

// RUTA PARA ASIGNAR FORMULARIO SEGUN CURSO Y DIVISION
Route::post('/forms/assign-to-course-division', [FormController::class, 'assignFormToCourseAndDivision']);

//RUTA PARA VER SI UN FORMULARIO ESTA CONTESTADO POR TODOS LOS ALUMNOS DE UNA CLASE
Route::middleware(['auth:sanctum'])->get('/check-form-completion/{course_id}/{division_id}/{form_id}', [FormController::class, 'checkClassFormCompletion']);

// RUTA PARA OBETENER TODAS LAS RESPUESTAS A LOS FORMULARIOS
Route::get('/all-responses', [AnswerController::class, 'getAllResponses']);

//RUTA PARA OBTENER TODOS LOS FORMULARIOS ACTIVOS
Route::get('/forms/active', [FormController::class, 'getActiveForms']);

//RUTA PARA OBTENER TODAS LAS RESPUESTA AL SOCIOGRAMA DE UN CURSO Y DIVISION ESPECIFICA
Route::middleware(['auth:sanctum', 'role:tutor,admin'])->post('/sociogram/responses', [SociogramRelationshipController::class, 'getResponsesByCourseAndDivision']);

//RUTA PARA OBTENER TODAS LAS RESPUESTA DEL CESC DE UN CURSO Y DIVISION ESPECIFICA
Route::middleware(['auth:sanctum', 'role:tutor,admin'])->post('/cesc/responses', [CescRelationshipController::class, 'getResponsesByCourseAndDivision']);

Route::get('/roles', [RoleController::class, 'index']);

Route::resource('divisions', DivisionController::class);

// Modificar la ruta del recurso forms para aplicar diferentes middlewares por método
Route::group(['prefix' => 'forms'], function () {
    // Rutas que todos los roles pueden acceder
    Route::get('/', [FormController::class, 'index']);
    Route::get('/{form}', [FormController::class, 'show']);
    
    // Rutas que solo profesores y admin pueden acceder (no orientadores ni tutores)
    Route::middleware(['auth:sanctum', 'role:profesor,admin'])->group(function () {
        Route::post('/', [FormController::class, 'store']);
        Route::put('/{form}', [FormController::class, 'update']);
        Route::patch('/{form}', [FormController::class, 'update']);
        Route::delete('/{form}', [FormController::class, 'destroy']);
    });
    
    // Rutas que pueden acceder profesores, tutores y admin (no orientadores)
    Route::middleware(['auth:sanctum', 'role:profesor,tutor,admin'])->group(function () {
        Route::post('/forms-save', [FormController::class, 'storeFormWithQuestions']);
    });
});

Route::resource('questions', QuestionController::class);

// Modificar la ruta del recurso answers para aplicar diferentes middlewares por método
Route::group(['prefix' => 'answers'], function () {
    // Rutas que solo profesores, orientadores y admin pueden acceder (no tutores)
    Route::middleware(['auth:sanctum', 'role:profesor,orientador,admin'])->group(function () {
        Route::get('/', [AnswerController::class, 'index']);
        Route::get('/{answer}', [AnswerController::class, 'show']);
    });
    
    // Rutas para guardar respuestas (todos los roles autenticados pueden responder)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/', [AnswerController::class, 'store']);
    });
    
    // Rutas que solo profesores y admin pueden acceder
    Route::middleware(['auth:sanctum', 'role:profesor,admin'])->group(function () {
        Route::put('/{answer}', [AnswerController::class, 'update']);
        Route::patch('/{answer}', [AnswerController::class, 'update']);
        Route::delete('/{answer}', [AnswerController::class, 'destroy']);
    });
});

// Ruta para pedir todas las preguntas y respuestas de un formulario
Route::get('forms/{formId}/questions', [FormController::class, 'getQuestionsAndAnswers']);

// Ruta para obtener los datos de un estudiante (curso, division)
Route::get('/get-students', [UserController::class, 'getStudents']);

// Ruta para obtener los datos de un profesor (curso, division)
Route::get('/get-teachers', [UserController::class, 'getTeachers']);

Route::post('/login', [AuthenticatedSessionController::class, 'login']);
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::middleware('auth:sanctum')->post('/logout', [AuthenticatedSessionController::class, 'logout']);

// Ruta para obtener el usuario autenticado
Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'getAuthenticatedUser']);

// Rutas para formularios
Route::resource('forms', FormController::class);

// Ruta personalizada para guardar un formulario con preguntas
Route::post('forms-save', [FormController::class, 'storeFormWithQuestions']);

// Ruta para obtener las preguntas y respuestas de un formulario
Route::get('forms/{formId}/questions', [FormController::class, 'getQuestionsAndAnswers']);
Route::post('forms/{formId}/submit-answers', [AnswerController::class, 'storeMultipleAnswers']);
Route::get('forms/{id}', [FormController::class, 'show']);

// CRUD para preguntas y respuestas
Route::resource('questions', QuestionController::class);
Route::resource('answers', AnswerController::class);

// Rutas para grupos con autenticación Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::resource('groups', GroupController::class);
    Route::get('groups/{id}/members', [GroupController::class, 'getMembers']);
    Route::post('/groups/{id}/addStudentsToGroup', [GroupController::class, 'addStudentsToGroup']);
    Route::put('/groups/{id}', [GroupController::class, 'update']);
    Route::delete('/groups/{groupId}/removeStudentFromGroup', [GroupController::class, 'removeStudentFromGroup']);
    Route::post('/groups', [GroupController::class, 'store']);
    Route::delete('/groups/{id}', [GroupController::class, 'destroy']);
});

// Rutas para relaciones sociométricas
Route::prefix('sociogram-relationships')->group(function () {
    Route::get('/sociogram-relationships', [SociogramRelationshipController::class, 'getRelationships']);
    Route::get('/', [SociogramRelationshipController::class, 'index']); // Listar todas las relaciones
    Route::get('/user/{id}', [SociogramRelationshipController::class, 'byUser']); // Filtrar por usuario
    Route::post('/', [SociogramRelationshipController::class, 'store']); // Guardar relaciones
    Route::delete('/{id}', [SociogramRelationshipController::class, 'destroy']); // Eliminar una relación específica
});

// Rutas para relaciones sociométricas del CESC
Route::prefix('cesc-relationships')->group(function () {
    // PARA USAR
    Route::get('/cesc-relationships', [CescRelationshipController::class, 'getCesc']);
    Route::get('/', [CescRelationshipController::class, 'index']); // Listar todas las relaciones
    Route::get('/user/{id}', [CescRelationshipController::class, 'byUser']); // Filtrar por usuario
    Route::post('/', [CescRelationshipController::class, 'store']); // Guardar relaciones
    Route::delete('/{id}', [CescRelationshipController::class, 'destroy']); // Eliminar una relación específica
    //Route::post('/calcular-resultados', [CescRelationshipController::class, 'calcularResultados']);
    //Route::get('/calcular-resultados', [CescRelationshipController::class, 'calcularResultados']);
    Route::get('/cesc-results', [CescRelationshipController::class, 'verResultados']);
    Route::get('/cesc-results/{id}', [CescRelationshipController::class, 'verResultadosPorGrupo']);
});

// Rutas para los comentarios
Route::prefix('comments')->group(function () {
    // Crear un comentario
    Route::post('/', [CommentController::class, 'store']);
    // Mostrar todos los comentarios
    Route::get('/', [CommentController::class, 'index']);
    // Mostrar un comentario específico por su ID
    Route::get('/{id}', [CommentController::class, 'show']);
    // Actualizar un comentario existente
    Route::put('/{id}', [CommentController::class, 'update']);
    // Eliminar un comentario
    Route::delete('/{id}', [CommentController::class, 'destroy']);
    // Obtener los comentarios por alumno
    Route::get('/students/{studentId}', [CommentController::class, 'getCommentsForStudent']);
    // Obtener los comentarios hechos por un profesor
    Route::get('/teachers/{teacherId}', [CommentController::class, 'getCommentsByTeacher']);
});

// Obtener comentarios de un grupo
Route::get('groups/{idGroup}/comments', [CommentController::class, 'getCommentsForGroup']);

// Crear un comentario asociado a un grupo
Route::post('/groups/{idGroup}/comments', [CommentController::class, 'addCommentToGroup']);
Route::put('/groups/{idGroup}/comments/{commentId}', [CommentController::class, 'updateCommentInGroup']);
Route::delete('/groups/{idGroup}/comments/{commentId}', [CommentController::class, 'deleteCommentFromGroup']);

// Google
Route::post('/google-login', [RegisteredUserController::class, 'googleLogin']);

// Rutas para las notificaciones
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [UserNotificationController::class, 'index']);
    Route::post('/notifications', [UserNotificationController::class, 'store']);
    Route::get('/teacher-notifications', [UserNotificationController::class, 'teacherNotifications']);
    Route::delete('/notifications/{id}', [UserNotificationController::class, 'destroy']);
});

// Rutas para las bitácoras
Route::prefix('bitacoras')->group(function () {
    Route::get('/', [BitacoraController::class, 'index']);
    Route::post('/', [BitacoraController::class, 'store']);
    Route::get('/{id}', [BitacoraController::class, 'show']);
    Route::put('/{id}', [BitacoraController::class, 'update']);
    Route::delete('/{id}', [BitacoraController::class, 'destroy']);

    // Rutas para las notas de bitácora
    Route::get('/{bitacoraId}/notes', [BitacoraNoteController::class, 'index']);
    Route::post('/{bitacoraId}/notes', [BitacoraNoteController::class, 'store']);
    Route::get('/{bitacoraId}/notes/{id}', [BitacoraNoteController::class, 'show']);
    Route::put('/{bitacoraId}/notes/{noteId}', [BitacoraNoteController::class, 'update']);
    Route::delete('/{bitacoraId}/notes/{noteId}', [BitacoraNoteController::class, 'destroy']);
    Route::delete('/{groupId}/notes', [BitacoraNoteController::class, 'deleteAllNotes']);
});

Route::get('/bitacoras/{bitacoraId}/user/{userId}/notes', [BitacoraNoteController::class, 'getUserNotes']);
Route::get('bitacoras/{groupId}/notes', [BitacoraController::class, 'getNotes']);

// Rutas para las invitaciones
// Rutas públicas para consultar una invitación (útil en el front para validar el token)
Route::get('/invitations/{token}', [InvitationController::class, 'show']);

// Rutas protegidas con autenticación (por ejemplo, usando Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Gestión de invitaciones: crear y listar (para el profesor)
    Route::post('/invitations', [InvitationController::class, 'store']);
    Route::get('/invitations', [InvitationController::class, 'index']);

});

// RUTAS PARA OBTENER RESULTADOS DEL CESC
Route::middleware(['auth:sanctum', 'role:orientador,admin'])->post('/cesc/calcular-resultados', [CescRelationshipController::class, 'calcularResultados']);
Route::get('/cesc/ver-resultados', [CescRelationshipController::class, 'verResultados']); // Ruta pública para obtener resultados
Route::get('/cesc/graficas-tags', [CescRelationshipController::class, 'getTagsGraphData']); // Ruta pública para obtener gráficas