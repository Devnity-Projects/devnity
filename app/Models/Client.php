<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// app/Models/Client.php
class Client extends Model
{
    protected $fillable = [
        'name',
        'contact_name',
        'email',
        'phone',
        'status',
        'company',
        'document',
        'address',
        'city',
        'state',
        'country',
        'notes',
    ];
}


