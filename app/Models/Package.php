<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;
    public const IMAGE_PATH = 'packages/images';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'package_name',
        'package_details',
        'package_price',
        'image_name',
        'image_path',
        'status'
    ];

    /**
     * Get the bookings for the package.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
