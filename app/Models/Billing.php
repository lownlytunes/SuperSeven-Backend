<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Billing extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_UNPAID = 0;
    public const STATUS_PARTIAL = 1;
    public const STATUS_PAID = 2;

    public const STATUS = [
        self::STATUS_UNPAID => 'Unpaid',
        self::STATUS_PARTIAL => 'Partial',
        self::STATUS_PAID => 'Paid',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'package_amount',
        'add_on_amount',
        'total_amount',
        'billing_status',
    ];

    /**
     * Get the booking that owns the billing.
     *
     * @return BelongsTo
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the payments for the billing.
     *
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany('created_at');
    }
}
