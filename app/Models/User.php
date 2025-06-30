<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const MAX_ATTEMPTS  = 5;
    public const CLIENT_TYPE  = 1;
    public const COORDINATOR_TYPE = 2;
    public const OWNER_TYPE = 3;
    public const SECRETARY_TYPE = 4;
    public const PHOTOGRAPHER_TYPE = 5;
    public const EDITOR_TYPE = 6;
    public const FREELANCER_TYPE = 7;
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'disabled';

    public const ROLE_TYPES = [
        self::CLIENT_TYPE => 'Client',
        self::COORDINATOR_TYPE => 'Coordinator',
        self::OWNER_TYPE => 'Owner',
        self::SECRETARY_TYPE => 'Secretary',
        self::PHOTOGRAPHER_TYPE => 'Photographer',
        self::EDITOR_TYPE => 'Editor',
        self::FREELANCER_TYPE => 'Freelancer',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'mid_name',
        'last_name',
        'email',
        'password',
        'contact_num',
        'address',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the employee record associated with the user.
     *
     * @return HasOne
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Get the payment record associated with the user.
     *
     * @return HasOne
     */
    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    /**
     * Get the bookings for the Customer user.
     *
     * @return HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * The bookings that belong to the user.
     *
     * @return BelongsToMany
     */
    public function workloads(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'workload')->using(Workload::class);
    }

    /**
     * Get the feedbacks for the Customer user.
     *
     * @return HasMany
     */
    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * Get the user's supplier.
     *
     * @return Attribute
     */
    public function customerType(): Attribute
    {
        return Attribute::make(get: fn() => $this->customer->customer_type);
    }

    /**
     * Get the user's full name.
     *
     * @return Attribute
     */
    public function fullName(): Attribute
    {
        return Attribute::make(get: function (mixed $value, array $attributes) {
            return $attributes['first_name'] . ' ' . $attributes['mid_name'] . ' ' . $attributes['last_name'];
        });
    }
}
