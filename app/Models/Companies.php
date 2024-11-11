<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Companies extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'password',
        'created_by',
    ];


    public function user_companies(): BelongsTo
    {
        return $this->belongsTo(User_Companies::class);
    }

    public function teams(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function user_point(): BelongsTo {
        return $this->belongsTo(User_Points::class);
    }

}
