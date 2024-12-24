<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Models\Author;
use Illuminate\Http\Request;

class PublicationController extends Controller
{
    /**
     * Affiche la liste des publications.
     */
    public function userPublications()
    {
        $userId = auth()->id();

        // Récupérer les publications de l'utilisateur connecté avec leurs relations
        $publications = Publication::with('authors', 'bonuses')
            ->where('user_id', $userId)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $publications,
        ]);
    }
    public function index()
    {
        $publications = Publication::with('user', 'authors', 'bonuses')->get();

        return response()->json([
            'success' => true,
            'data' => $publications
        ]);
    }

    /**
     * Affiche une seule publication.
     */
    public function show($id)
    {
        $publication = Publication::with('user', 'authors', 'bonuses')->find($id);

        if (!$publication) {
            return response()->json([
                'success' => false,
                'message' => 'Publication introuvable'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $publication
        ]);
    }

    /**
     * Crée une nouvelle publication.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'abstract' => 'nullable|string',
            'publication_date' => 'required|date',
            'journal' => 'required|string|max:255',
            'impact_factor' => 'nullable|numeric',
            'indexation' => 'required|in:Scopus,Web of Science,Other',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx',
            'authors' => 'required|array|min:1',
            'authors.*.name' => 'required|string|max:255',
            'authors.*.email' => 'nullable|email',
        ]);

        $publication = Publication::create(array_merge(
            $validated,
            ['user_id' => auth()->id()]
        ));

        foreach ($request->input('authors') as $author) {
            $publication->authors()->create($author);
        }

        return response()->json([
            'success' => true,
            'message' => 'Publication créée avec succès',
            'data' => $publication
        ], 201);
    }

    /**
     * Met à jour une publication existante.
     */
    public function update(Request $request, $id)
    {
        $publication = Publication::find($id);

        if (!$publication) {
            return response()->json([
                'success' => false,
                'message' => 'Publication introuvable'
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'abstract' => 'nullable|string',
            'publication_date' => 'required|date',
            'journal' => 'required|string|max:255',
            'impact_factor' => 'nullable|numeric',
            'indexation' => 'required|in:Scopus,Web of Science,Other',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx',
            'authors' => 'nullable|array|min:1',
            'authors.*.name' => 'required|string|max:255',
            'authors.*.email' => 'nullable|email',
        ]);

        $publication->update($validated);

        // Met à jour les auteurs
        if ($request->has('authors')) {
            $publication->authors()->delete();
            foreach ($request->input('authors') as $author) {
                $publication->authors()->create($author);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Publication mise à jour avec succès',
            'data' => $publication
        ]);
    }

    /**
     * Supprime une publication.
     */
    public function destroy($id)
    {
        $publication = Publication::find($id);

        if (!$publication) {
            return response()->json([
                'success' => false,
                'message' => 'Publication introuvable'
            ], 404);
        }

        $publication->delete();

        return response()->json([
            'success' => true,
            'message' => 'Publication supprimée avec succès'
        ]);
    }
}
