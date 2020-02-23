<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['id','content'];
    public $incrementing = false;

    public function items() {
        return $this->hasMany('App\Product');
    }


}
