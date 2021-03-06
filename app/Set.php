<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Set extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'boutiqueID', 'setName', 'setDesc', 'price', 'setStatus', 'rpID', 'quantity'];

    public function owner()
    {
        return $this->hasOne('App\Boutique', 'id', 'boutiqueID');
    }

    public function items()
    { 
        return $this->hasMany('App\Setitem', 'setID', 'id');
    }

    public function rentDetails()
    {
        return $this->hasOne('App\Rentableproduct', 'id', 'rpID');
    }

    public function inFavorites()
    {
        return $this->hasOne('App\Favorite', 'itemID', 'id');
    }
}
