<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequestItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function requestForm()
    {
        return $this->belongsTo(RequestForm::class, 'request_form_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
