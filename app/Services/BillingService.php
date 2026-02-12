<?php

namespace App\Services;

use App\Models\Booking;

class BillingService
{
    public function getFilterBillingData()
    {
        return [
            'others' => [
                'type' => 'or',
                'condition' => "category = " . Booking::CATEGORY_OTHERS,
            ],
            'birthday' => [
                'type' => 'or',
                'condition' => "category = " . Booking::CATEGORY_BIRTHDAY,
            ],
            'prenup' => [
                'type' => 'or',
                'condition' => "category = " . Booking::CATEGORY_PRENUP,
            ],
            'debut' => [
                'type' => 'or',
                'condition' => "category = " . Booking::CATEGORY_DEBUT,
            ],
            'wedding' => [
                'type' => 'or',
                'condition' => "category = " . Booking::CATEGORY_WEDDING,
            ],
        ];
    }
}