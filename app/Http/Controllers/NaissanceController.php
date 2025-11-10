<?php

namespace App\Http\Controllers;

use App\Models\Naissance;
use Illuminate\Http\Request;

class NaissanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Naissance::with(['region', 'district', 'commune']);
        
        // Filtres
        if ($request->has('annee') && $request->annee) {
            $query->where('ANNEE_NAISSANCE', $request->annee);
        }
        
        if ($request->has('sexe') && $request->sexe) {
            $query->where('SEXE_ENFANT', $request->sexe);
        }
        
        $naissances = $query->paginate(50);
        
        return view('naissances.index', compact('naissances'));
    }
    
    public function statistiques()
    {
        $statsAnnee = Naissance::statistiquesParAnnee();
        $statsSexe = Naissance::statistiquesParSexe();
        $tauxAssistance = Naissance::tauxAssistanceMedicale();
        
        return view('naissances.statistiques', compact('statsAnnee', 'statsSexe', 'tauxAssistance'));
    }
}