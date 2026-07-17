<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemoRequest extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'industry',
        'message',
        'status',
    ];
}
