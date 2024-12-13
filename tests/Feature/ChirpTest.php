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

public function test_un_chirp_ne_peut_pas_etre_mis_a_jour_avec_un_contenu_vide()
{
    // Créer un utilisateur
    $utilisateur = User::factory()->create();

    // Créer un chirp associé à cet utilisateur
    $chirp = Chirp::factory()->create(['user_id' => $utilisateur->id]);

    // Se connecter en tant qu'utilisateur
    $this->actingAs($utilisateur);

    // Tenter de mettre à jour le chirp avec un contenu vide
    $reponse = $this->put("/chirps/{$chirp->id}", [
        'message' => '',
    ]);

    // Vérifier que la validation échoue
    $reponse->assertSessionHasErrors(['message']);

    // Vérifier que le contenu du chirp dans la base n'a pas changé
    $this->assertDatabaseHas('chirps', [
        'id' => $chirp->id,
        'message' => $chirp->message, // Contenu initial
    ]);
}

public function test_un_chirp_ne_peut_pas_etre_mis_a_jour_avec_un_contenu_trop_long()
{
    // Créer un utilisateur
    $utilisateur = User::factory()->create();

    // Créer un chirp associé à cet utilisateur
    $chirp = Chirp::factory()->create(['user_id' => $utilisateur->id]);

    // Se connecter en tant qu'utilisateur
    $this->actingAs($utilisateur);

    // Tenter de mettre à jour le chirp avec un contenu de plus de 255 caractères
    $reponse = $this->put("/chirps/{$chirp->id}", [
        'message' => str_repeat('a', 256),
    ]);

    // Vérifier que la validation échoue
    $reponse->assertSessionHasErrors(['message']);

    // Vérifier que le contenu du chirp dans la base n'a pas changé
    $this->assertDatabaseHas('chirps', [
        'id' => $chirp->id,
        'message' => $chirp->message, // Contenu initial
    ]);
}

public function test_un_utilisateur_ne_peut_pas_creer_plus_de_10_chirps()
{
    // Créer un utilisateur
    $utilisateur = User::factory()->create();

    // Créer 10 chirps pour cet utilisateur
    Chirp::factory()->count(10)->create(['user_id' => $utilisateur->id]);

    // Se connecter en tant qu'utilisateur
    $this->actingAs($utilisateur);

    // Tenter de créer un 11ᵉ chirp
    $reponse = $this->post('/chirps', [
        'message' => 'Un chirp supplémentaire',
    ]);

    // Vérifier que la réponse est une erreur 403
    $reponse->assertStatus(403);

    // Vérifier que le 11ᵉ chirp n'a pas été créé
    $this->assertDatabaseCount('chirps', 10);
}

public function test_seuls_les_chirps_recents_sont_affiches()
{
    // Créer un utilisateur
    $utilisateur = User::factory()->create();

    // Créer des "chirps" avec des dates différentes
    Chirp::factory()->create([
        'user_id' => $utilisateur->id,
        'created_at' => now()->subDays(2), // Chirp récent
    ]);

    Chirp::factory()->create([
        'user_id' => $utilisateur->id,
        'created_at' => now()->subDays(8), // Chirp ancien
    ]);

    // Effectuer une requête GET sur la page d'accueil
    $reponse = $this->actingAs($utilisateur)->get('/');

    // Vérifier que seul le "chirp" récent est affiché
    $reponse->assertSee(now()->subDays(2)->toDateString());
    $reponse->assertDontSee(now()->subDays(8)->toDateString());
}




}
