<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'category_id', 'title', 'description', 'from_loc', 'to_loc', 'deadline', 'status', 'lat', 'lng', 'embedding', 'boosted_at'])]
class JastipListing extends Model
{
    use HasUuids, HasFactory;

    protected $hidden = [
        'embedding',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'lat' => 'decimal:8',
            'lng' => 'decimal:8',
        ];
    }

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
