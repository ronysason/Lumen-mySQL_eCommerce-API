<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    $fillable = ['id','content'];
    public $incrementing = false;

    public function items() {
        return $this->hasMany('App\Cart_Item');
    }

}
