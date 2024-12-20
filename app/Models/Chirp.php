<?php

namespace Database\Factories;
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Events\ChirpCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Chirp extends Model
{
    use HasFactory; 
    
    protected $fillable = [
        'message', 'user_id'
    ];

    protected $dispatchesEvents = [
        'created' => ChirpCreated::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    // Règles de validation pour le modèle Chirp
    public static function rules()
    {
        return [
            'message' => 'required|max:255',
        ];
    }

    public function likes()
{
    return $this->belongsToMany(User::class, 'likes');
}

    
}
