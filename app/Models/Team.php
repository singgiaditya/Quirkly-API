<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'description',
    ];

    public function company(): HasMany
    {
        return $this->hasMany(Companies::class);
    }

    public function user_teams(): BelongsTo
    {
        return $this->belongsTo(User_Teams::class);
    }

    public function projects(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

}
