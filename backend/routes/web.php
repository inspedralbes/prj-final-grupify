<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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
use App\Http\Controllers\GroupController;

use Illuminate\Auth\Events\Authenticated;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::post('/logout', function () {
    Auth::logout();
    // Redireccionar al login del frontend
    return redirect('http://localhost:3000/login');
})->name('logout');

Route::get('/forms/{id}', [FormController::class, 'show'])->name('forms.show');

Route::post('/users/{userId}/assign-course-division', [UserController::class, 'assignCourseAndDivision'])->name('users.assignCourseDivision');

Route::resource('roles', RoleController::class);
Route::resource('courses', CourseController::class);
Route::resource('subjects', SubjectController::class);
Route::resource('users', UserController::class);
Route::resource('divisions', DivisionController::class);
Route::resource('forms', FormController::class);
Route::resource('questions', QuestionController::class);
Route::resource('answers', AnswerController::class);
Route::resource('groups', GroupController::class);


