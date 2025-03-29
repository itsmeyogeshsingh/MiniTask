<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title', 'description', 'priority', 'due_date', 'status'
    ];

    protected $casts = [
        'status' => 'string',
    ];
}
