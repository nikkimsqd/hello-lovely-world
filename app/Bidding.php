<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bidding extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'userID', 'quotationPrice', 'endDate', 'deadlineOfProduct', 'measurementID', 'category', 'notes', 'orderID', 'status', 'bidID', 'quantity', 'fabChoice', 'numOfPerson', 'nameOfWearers'];

    public function owner()
    {
        return $this->hasOne('App\User', 'id', 'userID');
    }

    public function productFile()
    {
        return $this->hasMany('App\File', 'typeID', 'id');
    }

    public function order()
    {
        return $this->hasOne('App\Order', 'transactionID', 'id');
    }

    public function measurement()
    {
        return $this->hasOne('App\Measurement', 'id', 'measurementID');
    }

    public function category()
    {
        return $this->hasOne('App\Category', 'id', 'category');
    }

    public function bids()
    {
        return $this->hasMany('App\Bid', 'biddingID', 'id');
    }

    public function bid()
    {
        return $this->hasOne('App\Bid', 'id', 'bidID');
    }
}
