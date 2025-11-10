<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profession extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'libelle'];

    /**
     * Décès où cette profession est celle du défunt
     */
    public function decesDefunts()
    {
        return $this->hasMany(Deces::class, 'profession_defunt_id');
    }

    /**
     * Décès où cette profession est celle du déclarant
     */
    public function decesDeclarants()
    {
        return $this->hasMany(Deces::class, 'profession_declarant_id');
    }

    /**
     * Naissances où cette profession est celle de la mère
     */
    public function naissancesMeres()
    {
        return $this->hasMany(Naissance::class, 'profession_mere_id');
    }

    /**
     * Naissances où cette profession est celle du père
     */
    public function naissancesPeres()
    {
        return $this->hasMany(Naissance::class, 'profession_pere_id');
    }
}