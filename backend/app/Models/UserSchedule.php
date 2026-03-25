<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSchedule extends Model
{
    protected $fillable = [
        'user_id',
        'day',
        'start_time',
        'end_time',
        'description',
<<<<<<< HEAD
        'color',
        'semester',
        'school_year'
=======
        'color'
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
