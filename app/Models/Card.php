<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'column_id',
        'user_id',
        'title',
        'description',
        'position',
        'priority',
        'due_date',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'due_date' => 'datetime',
    ];

    public function column()
    {
        return $this->belongsTo(Column::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class , 'user_id');
    }

    public function dependencies()
    {
        return $this->belongsToMany(Card::class , 'card_dependencies', 'card_id', 'depends_on_card_id')
            ->withTimestamps();
    }

    public function dependents()
    {
        return $this->belongsToMany(Card::class , 'card_dependencies', 'depends_on_card_id', 'card_id')
            ->withTimestamps();
    }
}
