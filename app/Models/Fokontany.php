<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fokontany extends Model
{
    use HasFactory;

    protected $table = 'fokontany';

    protected $fillable = ['commune_id', 'code', 'libelle', 'idfkt'];

    /**
     * Un fokontany appartient à une commune
     */
    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    /**
     * Un fokontany a plusieurs décès
     */
    public function deces()
    {
        return $this->hasMany(Deces::class);
    }

    /**
     * Un fokontany a plusieurs naissances
     */
    public function naissances()
    {
        return $this->hasMany(Naissance::class);
    }
}