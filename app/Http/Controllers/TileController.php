<?php

namespace App\Http\Controllers;

use App\Models\Tile;
use Illuminate\Http\Request;

class TileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tiles = Tile::orderBy('order')->orderBy('id')->get();
        return view('dashboard.index', compact('tiles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
        ]);

        $validated['order'] = Tile::max('order') + 1;

        Tile::create($validated);

        return redirect()->route('dashboard.index')
            ->with('success', 'Kachel erfolgreich hinzugefügt!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tile $tile)
    {
        $tile->delete();

        return redirect()->route('dashboard.index')
            ->with('success', 'Kachel erfolgreich gelöscht!');
    }
}
