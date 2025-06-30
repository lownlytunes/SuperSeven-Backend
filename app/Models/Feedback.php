<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 0;
    public const STATUS_UNPOSTED = 1;
    public const STATUS_POSTED = 3;

    public const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_UNPOSTED => 'Unposted',
        self::STATUS_POSTED => 'Posted',
    ];

    protected $table = 'feedbacks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'user_id',
        'feedback_date',
        'feedback_details',
    ];

    /**
     * Get the booking that owns the feedback.
     *
     * @return BelongsTo
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the customer that owns the feedback.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
