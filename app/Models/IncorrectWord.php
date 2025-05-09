<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class IncorrectWord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'word',
        'correct_word',
    ];

    // Nếu bạn muốn tạo mối quan hệ với User (nếu có)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
