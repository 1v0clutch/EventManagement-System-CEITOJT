<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DefaultEventDate extends Model
{
    protected $fillable = [
        'default_event_id',
        'school_year',
        'semester',

        'date',
        'end_date',
        'month',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'end_date' => 'date',
        'month' => 'integer',
        'semester' => 'integer',

    ];

    /**
     * Get the default event this date belongs to.
     */
    public function defaultEvent(): BelongsTo
    {
        return $this->belongsTo(DefaultEvent::class);
    }

    /**
     * Get the user who created this date entry.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class , 'created_by');
    }

    /**
     * Scope a query to only include dates for a specific school year.
     */
    public function scopeForSchoolYear($query, string $schoolYear)
    {
        return $query->where('school_year', $schoolYear);
    }

    /**
     * Scope a query to only include dates for a specific month.
     */
    public function scopeForMonth($query, int $month)
    {
        return $query->where('month', $month);
    }

    /**
     * Scope a query to only include dates for a specific semester.
     */
    public function scopeForSemester($query, int $semester)
    {
        return $query->where('semester', $semester);
    }

    /**
     * Scope a query to order by date.
     */
    public function scopeOrderedByDate($query)
    {
        return $query->orderBy('date');
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
        return match ($this->semester) {
                1 => 'First Semester',
                2 => 'Second Semester',
                3 => 'Mid-Year',
                default => 'Unknown',
            };
    }

}
