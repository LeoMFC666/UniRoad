<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactLead extends Model
{
    protected $fillable = [
        'name',
        'email',
        'integration_status',
    ];

    protected $casts = [
        'name' => 'encrypted',
        'email' => 'encrypted',
    ];
}
