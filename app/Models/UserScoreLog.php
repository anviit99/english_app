<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserScoreLog extends Model
{
    protected $fillable = [
        'user_id', 'lesson_id', 'change', 'reason', 'logged_at'
    ];

    public $timestamps = false;
}
