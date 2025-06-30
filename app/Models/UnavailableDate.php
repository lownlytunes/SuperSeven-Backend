<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnavailableDate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'reason',
        'created_by',
    ];
}
