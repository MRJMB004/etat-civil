<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $fillable = ['region_id', 'code', 'libelle'];

    /**
     * Un district appartient à une région
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Un district a plusieurs communes
     */
    public function communes()
    {
        return $this->hasMany(Commune::class);
    }

    /**
     * Un district a plusieurs décès
     */
    public function deces()
    {
        return $this->hasMany(Deces::class);
    }

    /**
     * Un district a plusieurs naissances
     */
    public function naissances()
    {
        return $this->hasMany(Naissance::class);
    }
}