<?php

namespace Database\Factories;

use App\Models\Chirp;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChirpFactory extends Factory
{
    protected $model = Chirp::class;

    public function definition()
    {
        return [
            'message' => $this->faker->sentence,  // Créer un contenu aléatoire
            'user_id' => \App\Models\User::factory(),  // Associer un utilisateur existant
        ];
    }
}
