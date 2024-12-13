<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\Request;
use Illuminate\Http\Response; 
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;


class ChirpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    $chirps = Chirp::all();  // Récupérer tous les chirps
    return view('welcome', compact('chirps'));  // Passer les chirps à la vue
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:255',
        ]);

        Chirp::create([
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        return response()->json(['message' => 'Chirp créé avec succès!'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Chirp $chirp): View
    {
        Gate::authorize('update', $chirp);
 
        return view('chirps.edit', [
            'chirp' => $chirp,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chirp $chirp)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Chirp $chirp)
    {
        // Vérifier si l'utilisateur est propriétaire du chirp
    $this->authorize('update', $chirp);

    // Valider les données
    $validated = $request->validate([
        'content' => 'required|string|max:255',
    ]);

    // Mettre à jour le chirp
    $chirp->update($validated);

    return response()->json(['message' => 'Chirp mis à jour avec succès!'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chirp $chirp): RedirectResponse
    {
        Gate::authorize('delete', $chirp);
 
        $chirp->delete();
 
        return redirect(route('chirps.index'));
    }
}
