<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    protected $fillable = ['name', 'description', 'settings'];

    protected $casts = [
        'settings' => 'array',
    ];

    public function columns()
    {
        return $this->hasMany(Column::class)->orderBy('position');
    }

    public function members()
    {
        return $this->belongsToMany(User::class , 'board_user')->withTimestamps();
    }
}
