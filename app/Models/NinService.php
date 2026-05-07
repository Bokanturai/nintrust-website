<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NinService extends Model
{
    protected $fillable = [
        'user_id',
        'tnx_id',
        'refno',
        'trackingId',
        'nin',
        'email',
        'surname',
        'first_name',
        'middle_name',
        'dob',
        'service_type',
        'status',
        'reason',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactions()
    {
        return $this->belongsTo(Transaction::class, 'tnx_id');
    }
}
