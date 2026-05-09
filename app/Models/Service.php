<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_code',
        'name',
        'category',
        'type',
        'amount',
        'description',
        'status',
    ];

    public static function getAmountByCode($code)
    {
        return \Illuminate\Support\Facades\Cache::remember("service_amount_{$code}", 3600, function () use ($code) {
            $service = self::where('service_code', $code)->where('status', 'enabled')->first();
            return $service ? $service->amount : 0;
        });
    }

    protected static function booted()
    {
        static::saved(function ($service) {
            \Illuminate\Support\Facades\Cache::forget("service_amount_{$service->service_code}");
        });
    }
}
