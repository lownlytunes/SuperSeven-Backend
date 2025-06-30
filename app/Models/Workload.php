<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Workload extends Pivot
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'booking_id',
        'workload_status',
        'date_uploaded'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'workload';

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
    public $timestamps = true;
}
