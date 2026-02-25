<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    /** @use HasFactory<\Database\Factories\GameFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'game_member');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }
}
