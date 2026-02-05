<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'symbol'];

    public function requestItems()
    {
        return $this->hasMany(RequestItem::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->symbol ? "{$this->name} ({$this->symbol})" : $this->name;
    }
}
