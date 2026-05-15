<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'category_id', 'title', 'description', 'price', 'condition', 'status', 'embedding'])]
class PrelovedListing extends Model
{
    use HasUuids, HasFactory;

    protected $hidden = [
        'embedding',
    ];

    public function user() 
    {
        return $this->belongsTo(User::class);
    }
    
    public function category() 
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->morphMany(ListingImage::class, 'imageable');
    }
}