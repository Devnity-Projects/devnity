<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'document', 'email', 'phone', 'responsible', 'responsible_email',
        'responsible_phone', 'state_registration', 'municipal_registration', 'zip_code',
        'address', 'number', 'complement', 'neighborhood', 'city', 'state', 'country',
        'status', 'notes'
    ];
}

