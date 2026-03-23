<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatedAcademicEvent extends Model
{
    protected $fillable = [
        'name',
        'month',
        'semester',
        'school_year',
        'date',
        'end_date',
        'created_by',
        'order',
    ];

    protected $casts = [
        'month' => 'integer',
        'semester' => 'integer',
        'order' => 'integer',
        'date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the user who created this academic event.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include events for a specific school year.
     */
    public function scopeForSchoolYear($query, string $schoolYear)
    {
        return $query->where('school_year', $schoolYear);
    }

    /**
     * Scope a query to only include events for a specific semester.
     */
    public function scopeForSemester($query, int $semester)
    {
        return $query->where('semester', $semester);
    }

    /**
     * Scope a query to only include events for a specific month.
     */
    public function scopeForMonth($query, int $month)
    {
        return $query->where('month', $month);
    }

    /**
     * Scope a query to order events by their order field.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Determine the semester based on the month.
     * 
     * @param int $month Month number (1-12)
     * @return int Semester (1, 2, or 3)
     */
    public static function getSemesterFromMonth(int $month): int
    {
        // First Semester: September (9) to January (1)
        if (in_array($month, [9, 10, 11, 12, 1])) {
            return 1;
        }
        // Second Semester: February (2) to June (6)
        if (in_array($month, [2, 3, 4, 5, 6])) {
            return 2;
        }
        // Mid-Year: July (7) to August (8)
        return 3;
    }

    /**
     * Get the semester name.
     * 
     * @return string
     */
    public function getSemesterNameAttribute(): string
    {
        return match($this->semester) {
            1 => 'First Semester',
            2 => 'Second Semester',
            3 => 'Mid-Year',
            default => 'Unknown',
        };
    }
}
