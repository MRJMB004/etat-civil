<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'libelle'];

    /**
     * Une région a plusieurs districts
     */
    public function districts()
    {
        return $this->hasMany(District::class);
    }

    /**
     * Une région a plusieurs décès
     */
    public function deces()
    {
        return $this->hasMany(Deces::class);
    }

    /**
     * Une région a plusieurs naissances
     */
    public function naissances()
    {
        return $this->hasMany(Naissance::class);
    }
}