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

    /**
     * Get the verifications for the service.
     */
    public function verifications()
    {
        return $this->hasMany(Verification::class);
    }

    /**
     * Get the amount by service code.
     *
     * @param string $code
     * @return float|int
     */
    public static function getAmountByCode($code)
    {
        $service = self::where('service_code', $code)->first();
        return $service ? $service->amount : 0;
    }
}
