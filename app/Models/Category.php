<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'icon', 'type'])]
class Category extends Model
{
    public function prelovedItems()
    {
        return $this->hasMany(PrelovedItem::class);
    }
}
