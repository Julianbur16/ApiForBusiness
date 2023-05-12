<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class product extends Model
{
    use HasFactory;
    public function whatsapps(): BelongsToMany
    {
        return $this->belongsToMany(Whatsapp::class);
    }
}
