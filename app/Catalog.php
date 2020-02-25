<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Catalog extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];


    /*
      Define the relationship with products
    */
    public function products()
    {
        return $this->belongsToMany('App\Product');
    }
}
