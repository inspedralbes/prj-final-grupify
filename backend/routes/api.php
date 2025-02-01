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
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CourseDivisionUserController;
use App\Http\Controllers\UserNotificationController;

Route::post('/login', [AuthenticatedSessionController::class, 'login']);

Route::put('user/{id}/status', [UserController::class, 'updateStatus']);

Route::middleware(['auth:sanctum', 'role:admin'])->get('/admin-dashboard', [DashboardController::class, 'adminDashboard']);
Route::middleware(['auth:sanctum', 'role:teacher'])->get('/teacher-dashboard', [DashboardController::class, 'teacherDashboard']);
Route::middleware(['auth:sanctum', 'role:student'])->get('/student-dashboard', [DashboardController::class, 'studentDashboard']);

// Asegurará que la solicitud sea procesada como una solicitud de la API
Route::middleware('api')->resource('courses', CourseController::class);

Route::resource('roles', RoleController::class);
Route::resource('courses', CourseController::class);
Route::get('/courses-with-divisions', [CourseController::class, 'getCoursesWithDivisions']);
Route::apiResource('course-division-user', CourseDivisionUserController::class);

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

// Ruta para obtener los cursos de un usuario
Route::get('/users/{id}/courses', [UserController::class, 'getUserCourses']);

// RUTA PARA GUARDAR FORMULARIO EN BBDD
Route::post('forms-save', [FormController::class, 'storeFormWithQuestions']);

// RUTA PARA ASIGNAR FORMULARIO A USUARIO
Route::post('/assign-form-to-user', [FormController::class, 'assignFormToUser']);

// RUTA PARA OBTENER FORMULARIOS DE USUARIO
Route::get('/forms/user/{userId}', [FormController::class, 'getFormsByUserId']);

// RUTA PARA OBTENER PREGUNTAS CON RESPUESTAS DE UN FORMULARIO
Route::get('/forms/{formId}/questions-and-answers', [FormController::class, 'getQuestions']);

Route::post('/forms/{formId}/submit-responses', [AnswerController::class, 'submitResponses']);

// RUTA PARA ACTUALIZAR ESTADO DE FORMULARIO
Route::patch('/forms/{formId}/status', [FormController::class, 'updateFormStatus']);

// RUTA PARA OBTENER USUARIOS QUE HAN RESPONDIDO UN FORMULARIO
Route::get('/forms/{formId}/users', [AnswerController::class, 'getUsersByForm']);

// RUTA PARA OBTENER RESPUESTAS DE UN USUARIO A UN FORMULARIO
Route::get('/forms/{formId}/users/{userId}/answers', [AnswerController::class, 'getAnswersByUser']);

// RUTA PARA OBTENER USUARIOS QUE HAN RESPONDIDO SOCIOGRAMA
Route::get('/forms/{formId}/responded-users', [SociogramRelationshipController::class, 'getRespondedUsers']);

// RUTA PARA OBTENER RESPUESTA DE UN USUARIO DEL SOCIOGRAMA
Route::get('/forms/{formId}/users/{userId}/relationships', [SociogramRelationshipController::class, 'getAnswersByUser']);

// RUTA PARA OBTENER DIVISIONES SEGUN COURSE
Route::get('/course-divisions', [CourseController::class, 'getDivisionsByCourse']);

// RUTA PARA ASIGNAR FORMULARIO SEGUN CURSO Y DIVISION
Route::post('/forms/assign-to-course-division', [FormController::class, 'assignFormToCourseAndDivision']);

Route::get('/roles', [RoleController::class, 'index']);

Route::resource('divisions', DivisionController::class);
Route::resource('forms', FormController::class);
Route::resource('questions', QuestionController::class);
Route::resource('answers', AnswerController::class);

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
});