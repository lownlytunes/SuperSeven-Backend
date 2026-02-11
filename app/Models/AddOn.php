<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AddOn extends Model
{
    use HasFactory;

    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'add_on_name',
        'add_on_details',
        'add_on_price',
        'status',
    ];

    /**
     * Get the packages for the add-on.
     *
     * @return BelongsToMany
    */
    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'availed_addon')->using(AvailedAddon::class);
    }
}