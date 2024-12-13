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

public function test_un_utilisateur_peut_modifier_son_chirp()
{
    // Créer un utilisateur et un chirp
    $utilisateur = User::factory()->create();
    $chirp = Chirp::factory()->create([
        'user_id' => $utilisateur->id,
        'message' => 'Contenu initial',
    ]);

    // Authentifier l'utilisateur
    $this->actingAs($utilisateur);

    // Effectuer une requête PUT pour mettre à jour le chirp
    $reponse = $this->put("/chirps/{$chirp->id}", [
        'message' => 'Chirp modifié',
    ]);

    // Vérifier que la requête retourne le bon statut
    $reponse->assertStatus(200);

    // Vérifier que le contenu a été modifié dans la base de données
    $this->assertDatabaseHas('chirps', [
        'id' => $chirp->id,
        'message' => 'Chirp modifié',
    ]);
}

public function test_un_utilisateur_peut_supprimer_son_chirp()
{
    // Crée un utilisateur
    $utilisateur = User::factory()->create();

    // Crée un "chirp" associé à l'utilisateur
    $chirp = Chirp::factory()->create(['user_id' => $utilisateur->id]);

    // Connecte l'utilisateur
    $this->actingAs($utilisateur);

    // Envoie une requête DELETE pour supprimer le "chirp"
    $reponse = $this->delete("/chirps/{$chirp->id}");

    // Vérifie que la requête retourne un statut 200 (succès)
    $reponse->assertStatus(200);

    // Vérifie que le "chirp" n'existe plus dans la base de données
    $this->assertDatabaseMissing('chirps', [
        'id' => $chirp->id,
    ]);
}

public function test_un_utilisateur_ne_peut_pas_modifier_le_chirp_d_un_autre_utilisateur()
{
    // Créer deux utilisateurs
    $utilisateur1 = User::factory()->create();
    $utilisateur2 = User::factory()->create();

    // Créer un chirp pour l'utilisateur 1
    $chirp = Chirp::factory()->create(['user_id' => $utilisateur1->id]);

    // Se connecter en tant qu'utilisateur 2
    $this->actingAs($utilisateur2);

    // Essayer de modifier le chirp de l'utilisateur 1
    $reponse = $this->put("/chirps/{$chirp->id}", [
        'message' => 'Tentative de modification',
    ]);

    // Vérifier que l'utilisateur n'a pas la permission (statut 403)
    $reponse->assertStatus(403);

    // Vérifier que le message du chirp n'a pas changé
    $this->assertDatabaseHas('chirps', [
        'id' => $chirp->id,
        'message' => $chirp->message, // Le message original
    ]);
}

public function test_un_utilisateur_ne_peut_pas_supprimer_le_chirp_d_un_autre_utilisateur()
{
    // Créer deux utilisateurs
    $utilisateur1 = User::factory()->create();
    $utilisateur2 = User::factory()->create();

    // Créer un chirp pour l'utilisateur 1
    $chirp = Chirp::factory()->create(['user_id' => $utilisateur1->id]);

    // Se connecter en tant qu'utilisateur 2
    $this->actingAs($utilisateur2);

    // Essayer de supprimer le chirp de l'utilisateur 1
    $reponse = $this->delete("/chirps/{$chirp->id}");

    // Vérifier que l'utilisateur n'a pas la permission (statut 403)
    $reponse->assertStatus(403);

    // Vérifier que le chirp existe toujours dans la base de données
    $this->assertDatabaseHas('chirps', [
        'id' => $chirp->id,
    ]);
}




}
