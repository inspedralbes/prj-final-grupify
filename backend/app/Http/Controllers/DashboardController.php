<?php

// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // Método para el dashboard de Admin
    public function adminDashboard()
    {
        $user = Auth::user();
        return response()->json([
            'message' => 'Welcome to Admin Dashboard',
            'user' => $user
        ]);
    }

    // Método para el dashboard de Profesor
    public function teacherDashboard()
    {
        $user = Auth::user();
        return response()->json([
            'message' => 'Welcome to Teacher Dashboard',
            'user' => $user
        ]);
    }

    // Método para el dashboard de Alumno
    public function studentDashboard()
    {
        $user = Auth::user();
        return response()->json([
            'message' => 'Welcome to Student Dashboard',
            'user' => $user
        ]);
    }
}
