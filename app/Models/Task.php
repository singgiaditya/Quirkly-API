<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'assign_to',
    ];

    public function projects(): HasMany{
        return $this->hasMany(Project::class);
    }

    public function assign_to() : HasOne{
        return $this->hasOne(User::class);
    }

    public function  comments(): HasMany{
        return $this->hasMany(comment::class);
    }
}
