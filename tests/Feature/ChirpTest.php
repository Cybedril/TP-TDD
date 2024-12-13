<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Chirp;


class ChirpTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_utilisateur_peut_creer_un_chirp()
    {
        // Simuler un utilisateur connecté
        $utilisateur = User::factory()->create();
        $this->actingAs($utilisateur);

        // Envoyer une requête POST pour créer un chirp
        $response = $this->post('/chirps', [
            'message' => 'Mon premier chirp !'
        ]);

        // Vérifier que le chirp a été ajouté à la base de données
        $response->assertStatus(201);
        $this->assertDatabaseHas('chirps', [
            'message' => 'Mon premier chirp !',
            'user_id' => $utilisateur->id,
        ]);
    }

    public function test_un_chirp_ne_peut_pas_avoir_un_contenu_vide()
{
    // Créer un utilisateur
    $utilisateur = User::factory()->create();

    // Simuler que l'utilisateur est connecté
    $this->actingAs($utilisateur);

    // Essayer de créer un "chirp" avec un contenu vide
    $reponse = $this->post('/chirps', [
        'message' => ''
    ]);

    // Vérifier qu'une erreur de validation a été ajoutée pour le champ 'content'
    $reponse->assertSessionHasErrors(['message']);
}

public function test_un_chirp_ne_peut_pas_depasse_255_caracteres()
{
    // Créer un utilisateur
    $utilisateur = User::factory()->create();

    // Simuler que l'utilisateur est connecté
    $this->actingAs($utilisateur);

    // Essayer de créer un "chirp" avec un contenu de 256 caractères
    $reponse = $this->post('/chirps', [
        'message' => str_repeat('a', 256)  // Crée une chaîne de 256 caractères
    ]);

    // Vérifier qu'une erreur de validation a été ajoutée pour le champ 'content'
    $reponse->assertSessionHasErrors(['message']);
}

public function test_les_chirps_sont_affiches_sur_la_page_d_accueil()
{
    // Créer 3 chirps avec des messages spécifiques
    Chirp::factory()->create(['message' => 'Test Chirp Message 1']);
    Chirp::factory()->create(['message' => 'Test Chirp Message 2']);
    Chirp::factory()->create(['message' => 'Test Chirp Message 3']);

    // Faire une requête GET sur la page d'accueil
    $response = $this->get('/');

    // Afficher le contenu de la réponse pour vérification
    $responseContent = $response->getContent();
    echo $responseContent;

    // Vérifier que chaque chirp est bien présent dans la réponse
    $response->assertSee('Test Chirp Message 1');
    $response->assertSee('Test Chirp Message 2');
    $response->assertSee('Test Chirp Message 3');
}



}
