<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING     = 0;
    public const STATUS_APPROVED = 1;
    public const STATUS_REJECTED = 2;
    public const STATUS_FOR_RESCHEDULE = 3;

    public const STATUS = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_FOR_RESCHEDULE => 'For Reschedule',
    ];

    public const STATUS_UNASSIGNED = 0;
    public const STATUS_SCHEDULED = 1;
    public const STATUS_UPLOADED = 2;
    public const STATUS_FOR_EDIT = 3;
    public const STATUS_EDITING = 4;
    public const STATUS_FOR_RELEASE = 5;
    public const STATUS_COMPLETED = 6;

    public const DELIVERABLE_STATUS = [
        self::STATUS_UNASSIGNED => 'Unassigned',
        self::STATUS_SCHEDULED => 'Scheduled',
        self::STATUS_UPLOADED => 'Uploaded',
        self::STATUS_FOR_EDIT => 'For Edit',
        self::STATUS_EDITING => 'Editing',
        self::STATUS_FOR_RELEASE => 'For Release',
        self::STATUS_COMPLETED => 'Completed',
    ];

    public const WORKLOAD_STATUS = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_UPLOADED => 'Uploaded',
        self::STATUS_EDITING => 'Editing',
        self::STATUS_FOR_RELEASE => 'For Release',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'package_id',
        'booking_date',
        'ceremony_time',
        'event_name',
        'booking_address',
        'booking_status',
        'deliverable_status',
        'completion_date',
        'discount',
        'link',
        'sent_completed_mail'
    ];

    /**
     * Get the customer that owns the booking.
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the package that owns the booking.
     *
     * @return BelongsTo
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get the add-ons for the booking.
     *
     * @return BelongsToMany
     */
    public function addOns(): BelongsToMany
    {
        return $this->belongsToMany(AddOn::class, 'availed_addon')->using(AvailedAddon::class);
    }

    /**
     * The employees that belong to the booking.
     *
     * @return BelongsToMany
     */
    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workload')
            ->using(Workload::class)
            ->withPivot('id', 'workload_status', 'date_uploaded')
            ->withTimestamps();
    }

    /**
     * Get the feedback associated with the booking.
     *
     * @return HasOne
     */
    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class);
    }

    /**
     * Get the billing record associated with the booking.
     *
     * @return HasOne
     */
    public function billing(): HasOne
    {
        return $this->hasOne(Billing::class);
    }

    public function hasFeedback(): Attribute
    {
        return Attribute::make(function () {
            return $this->feedback()->exists();
        });
    }
}
