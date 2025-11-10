<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nationalite extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'libelle'];

    /**
     * Décès avec cette nationalité
     */
    public function deces()
    {
        return $this->hasMany(Deces::class, 'nationalite_id');
    }

    /**
     * Naissances où la mère a cette nationalité
     */
    public function naissancesMeres()
    {
        return $this->hasMany(Naissance::class, 'nationalite_mere_id');
    }

    /**
     * Naissances où le père a cette nationalité
     */
    public function naissancesPeres()
    {
        return $this->hasMany(Naissance::class, 'nationalite_pere_id');
    }
}