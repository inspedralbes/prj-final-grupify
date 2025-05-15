<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormUser extends Model
{
    use HasFactory;

    protected $table = 'form_user';

    protected $fillable = [
        'user_id',
        'course_id',
        'division_id',
        'form_id',
        'answered',
    ];

    protected $casts = [
        'answered' => 'boolean',
    ];

    /**
     * Get the user associated with the form response.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the form associated with the response.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Get the course associated with the response.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the division associated with the response.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Get the assignment associated with this form user.
     */
    public function assignment()
    {
        return $this->belongsTo(FormAssignment::class, 'form_id', 'form_id')
            ->where('course_id', $this->course_id)
            ->where('division_id', $this->division_id);
    }
    
    /**
     * Marca este formulario como respondido para el usuario.
     */
    public function markAsAnswered()
    {
        $this->answered = true;
        $this->save();
        
        return $this;
    }
    
    /**
     * Marca este formulario como no respondido para el usuario.
     */
    public function markAsUnanswered()
    {
        $this->answered = false;
        $this->save();
        
        return $this;
    }
}
