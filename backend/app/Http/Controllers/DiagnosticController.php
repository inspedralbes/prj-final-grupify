<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DiagnosticController extends Controller
{
    /**
     * Verificar el estado de las migraciones
     */
    public function checkMigrationStatus()
    {
        $status = [
            'form_assignments_exists' => Schema::hasTable('form_assignments'),
            'form_user_exists' => Schema::hasTable('form_user'),
        ];
        
        return response()->json($status);
    }
    
    /**
     * Verificar integridad de datos
     */
    public function checkDataIntegrity()
    {
        $issues = [];
        
        // Verificar si hay asignaciones con contadores incorrectos
        if (Schema::hasTable('form_assignments')) {
            $assignments = DB::table('form_assignments')->get();
            
            foreach ($assignments as $assignment) {
                // Contar respuestas reales
                $realCount = DB::table('form_user')
                    ->where('form_id', $assignment->form_id)
                    ->where('course_id', $assignment->course_id)
                    ->where('division_id', $assignment->division_id)
                    ->where('answered', true)
                    ->count();
                
                // Si el contador no coincide
                if ($realCount !== $assignment->responses_count) {
                    $issues[] = [
                        'type' => 'contador_incorrecto',
                        'assignment_id' => $assignment->id,
                        'form_id' => $assignment->form_id,
                        'course_id' => $assignment->course_id,
                        'division_id' => $assignment->division_id,
                        'stored_count' => $assignment->responses_count,
                        'real_count' => $realCount,
                    ];
                }
            }
        }
        
        return response()->json([
            'issues_count' => count($issues),
            'issues' => $issues
        ]);
    }
    
    /**
     * Corregir problemas de integridad
     */
    public function fixDataIssues()
    {
        $fixed = 0;
        
        // Corregir contadores incorrectos
        if (Schema::hasTable('form_assignments')) {
            $assignments = DB::table('form_assignments')->get();
            
            foreach ($assignments as $assignment) {
                // Contar respuestas reales
                $realCount = DB::table('form_user')
                    ->where('form_id', $assignment->form_id)
                    ->where('course_id', $assignment->course_id)
                    ->where('division_id', $assignment->division_id)
                    ->where('answered', true)
                    ->count();
                
                // Si el contador no coincide, actualizarlo
                if ($realCount !== $assignment->responses_count) {
                    DB::table('form_assignments')
                        ->where('id', $assignment->id)
                        ->update(['responses_count' => $realCount]);
                    
                    $fixed++;
                }
            }
        }
        
        return response()->json([
            'message' => "Se han corregido $fixed problemas",
        ]);
    }
}
