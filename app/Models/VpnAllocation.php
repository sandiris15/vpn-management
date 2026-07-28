<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VpnAllocation extends Model
{
    use HasFactory;

    protected $table = 'vpn_allocations';

    protected $fillable = [
        'username',
        'password',
        'profile',
        'service',
        'remote_address',
        'port_www',
        'port_winbox',
        'port_api',
    ];
}
