<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class Follow extends Model
// {
//     use HasFactory;
//     protected $fillable = ['follower_id', 'following_id'];

//     // Relación para el usuario que sigue (follower)
//     public function follower()
//     {
//         return $this->belongsTo(User::class, 'follower_id')->withoutPivot('follower_id', 'following_id');
//     }

//     // Relación para el usuario seguido (following)
//     public function following()
//     {
//         return $this->belongsTo(User::class, 'following_id')->withoutPivot('following_id', 'follower_id');;
//     }
// }
