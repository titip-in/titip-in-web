<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'from_loc', 'to_loc', 'notes', 'status'])]
class JastipRequest extends Model
{
    use HasUuids;
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
        
    public function category() 
    { 
        return $this->belongsTo(Category::class); 
    }
}
