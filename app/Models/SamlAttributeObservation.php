<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SamlAttributeObservation extends Model
{
    use HasFactory;

    protected $fillable = ['saml_client_id', 'name', 'first_seen_at', 'last_seen_at', 'observation_count'];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'observation_count' => 'integer',
    ];
}
