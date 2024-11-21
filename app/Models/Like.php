<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'post_id'];
    // Relación para el usuario que sigue (follower)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación para el usuario seguido (following)
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
