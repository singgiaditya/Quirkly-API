<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
    ];
    
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function tasks(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

}
