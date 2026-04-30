<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'icon', 'type'])]
class Category extends Model
{
    public function jastipListings()
    {
        return $this->hasMany(JastipListing::class);
    }

    public function jastipRequest()
    {
        return $this->hasMany(JastipRequest::class);
    }

    public function prelovedListings()
    {
        return $this->hasMany(PrelovedListing::class);
    }

    public function prelovedRequest()
    {
        return $this->hasMany(PrelovedRequest::class);
    }
}
