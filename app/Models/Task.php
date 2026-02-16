<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\BelongsToTenant;

class Task extends Model
{
    use HasFactory, BelongsToTenant;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'metadata' => 'array',
    ];

    protected $fillable = [
        'column_id',
        'organization_id',
        'sprint_id',
        'title',
        'description',
        'position',
        'metadata',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function column()
    {
        return $this->belongsTo(Column::class);
    }
    
    public function sprint()
    {
        return $this->belongsTo(Sprint::class);
    }
}
