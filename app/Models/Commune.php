<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commune extends Model
{
    use HasFactory;

    protected $fillable = ['district_id', 'code', 'libelle'];

    /**
     * Une commune appartient à un district
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Une commune a plusieurs fokontany
     */
    public function fokontany()
    {
        return $this->hasMany(Fokontany::class);
    }

    /**
     * Une commune a plusieurs décès
     */
    public function deces()
    {
        return $this->hasMany(Deces::class);
    }

    /**
     * Une commune a plusieurs naissances
     */
    public function naissances()
    {
        return $this->hasMany(Naissance::class);
    }
}