<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NinModification extends Model
{
    protected $fillable = [
        'user_id',
        'tnx_id',
        'refno',
        'nin_number',
        'photo',
        'first_name',
        'middle_name',
        'surname',
        'dob',
        'phone_number',
        'address',
        'status',
        'description',
        'reason',
        'origin_address',
        'full_address',
        'documents',
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
