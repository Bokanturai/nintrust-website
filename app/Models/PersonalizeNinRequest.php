<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalizeNinRequest extends Model
{
    protected $fillable = [
        'user_id',
        'tnx_id',
        'refno',
        'tracking_id',
        'status',
        'reason',
        'refunded_at',
        'result_file',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->belongsTo(Transaction::class, 'tnx_id');
    }
}
