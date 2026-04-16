<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id', 'category_id', 'type', 'title',
    'description', 'price', 'condition', 'images', 'is_sold'
])]
class PrelovedItem extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'is_sold' => 'boolean',
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
