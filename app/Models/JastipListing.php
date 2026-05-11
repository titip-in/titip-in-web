<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'from_loc', 'to_loc', 'deadline', 'status', 'image_url', 'lat', 'lng'])]
class JastipListing extends Model
{
    use HasUuids;

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
}
