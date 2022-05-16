<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    public function CardInstances()
    {
        return $this->hasMany(CardInstances::class);
    }
}
