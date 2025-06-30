<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    public const CASH_METHOD = 0;
    public const ONLINE_METHOD = 1;

    public const PAYMENT_METHODS = [
        self::CASH_METHOD => 'Cash Payment',
        self::ONLINE_METHOD => 'Online Payment',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'billing_id',
        'payment_date',
        'amount_paid',
        'payment_method',
        'balance',
        'remarks',
    ];

    /**
     * Get the billing that owns the payment.
     *
     * @return BelongsTo
     */
    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class);
    }
}
