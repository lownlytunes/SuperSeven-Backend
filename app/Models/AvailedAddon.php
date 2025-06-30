<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AvailedAddon extends Pivot
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'add_on_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'availed_addon';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
