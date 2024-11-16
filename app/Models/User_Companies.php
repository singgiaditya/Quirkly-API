<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User_Companies extends Model
{
    use HasFactory;

    protected $table = 'user_companies';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'company_id',
        'role',
        'joined_at',
    ];

    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }

    public function company(): HasMany {
        return $this->hasMany(Companies::class);
    }
}
