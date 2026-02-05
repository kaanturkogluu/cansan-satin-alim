<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequestForm extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'user_id' => 'integer',
        'department_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function items()
    {
        return $this->hasMany(RequestItem::class, 'request_form_id');
    }

    public function histories()
    {
        return $this->hasMany(RequestHistory::class, 'request_form_id')->latest();
    }
}
