<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User_Points extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'points',
    ];

    public function users(): HasMany {
        return $this->hasMany(User::class);
    }

    public function company(): HasMany {
        return $this->hasMany(Companies::class);
    }

}
