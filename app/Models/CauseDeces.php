<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CauseDeces extends Model
{
    use HasFactory;

    protected $table = 'causes_deces';

    protected $fillable = ['code', 'libelle', 'description'];

    /**
     * Décès ayant cette cause
     */
    public function deces()
    {
        return $this->hasMany(Deces::class, 'cause_deces_id');
    }
}