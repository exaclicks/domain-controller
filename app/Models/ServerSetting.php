<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServerSetting extends Model
{
    use HasFactory;
    protected $table = 'server_settings';
    public $timestamps = true;


    protected $fillable = [
        'is_server_busy',
    ];
}
