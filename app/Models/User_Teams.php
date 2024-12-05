<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User_Teams extends Model
{
    use HasFactory;

    protected $table = 'user_teams';

    protected $fillable = [
        'user_id',
        'team_id',
        'role',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function teams(): hasOne
    {
        return $this->hasOne(Team::class, 'id', "team_id" );
    }

}
