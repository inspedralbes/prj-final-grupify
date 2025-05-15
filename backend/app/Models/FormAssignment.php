<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class FormAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'form_id',
        'course_id',
        'division_id',
        'responses_count',
    ];

    /**
     * Get the teacher who assigned the form.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the form that was assigned.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Get the course to which the form was assigned.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the division to which the form was assigned.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

        /**
     * Get the students' responses for this assignment.
     */
    public function responses()
    {
        return $this->hasMany(FormUser::class, 'form_id', 'form_id')
            ->where('course_id', $this->course_id)
            ->where('division_id', $this->division_id);
    }
    
    /**
     * Recalculate the responses_count based on answered=1 in form_user table
     */
    public static function recalculateResponsesCounts()
    {
        $assignments = self::all();
        
        foreach ($assignments as $assignment) {
            $count = DB::table('form_user')
                ->where('form_id', $assignment->form_id)
                ->where('course_id', $assignment->course_id)
                ->where('division_id', $assignment->division_id)
                ->where('answered', 1)
                ->count();
            
            $assignment->responses_count = $count;
            $assignment->save();
        }
    }
}
