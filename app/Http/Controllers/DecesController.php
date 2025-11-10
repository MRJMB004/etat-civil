<?php

namespace App\Http\Controllers;

use App\Models\Deces;
use Illuminate\Http\Request;

class DecesController extends Controller
{
    public function index(Request $request)
    {
        $query = Deces::with(['region', 'district', 'commune', 'causeDeces']);
        
        // Filtres
        if ($request->has('annee') && $request->annee) {
            $query->where('ANNEE_DECES', $request->annee);
        }
        
        if ($request->has('region_id') && $request->region_id) {
            $query->where('region_id', $request->region_id);
        }
        
        $deces = $query->paginate(50);
        
        return view('deces.index', compact('deces'));
    }
    
    public function statistiques()
    {
        $statsAnnee = Deces::statistiquesParAnnee();
        $statsRegion = Deces::statistiquesParRegion();
        $statsCause = Deces::statistiquesParCause();
        
        return view('deces.statistiques', compact('statsAnnee', 'statsRegion', 'statsCause'));
    }
    
    public function dashboard()
    {
        $totalDeces = Deces::count();
        $totalNaissances = \App\Models\Naissance::count();
        $decesRecent = Deces::orderBy('created_at', 'desc')->take(10)->get();
        
        return view('dashboard', compact('totalDeces', 'totalNaissances', 'decesRecent'));
    }
}