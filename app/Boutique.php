<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Boutique extends Model
{
    protected $fillable = ['userID', 'boutiqueName', 'boutiqueAddress'];
}