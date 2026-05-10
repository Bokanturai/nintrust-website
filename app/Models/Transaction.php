<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payerid',
        'referenceId',
        'transaction_ref',
        'service_type',
        'service_description',
        'description',
        'amount',
        'type',
        'gateway',
        'status',
        'payer_name',
        'payer_phone',
        'payer_email',
        'performed_by',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
