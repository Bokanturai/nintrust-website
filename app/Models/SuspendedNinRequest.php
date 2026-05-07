<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuspendedNinRequest extends Model
{
    protected $fillable = [
        'user_id',
        'tnx_id',
        'refno',
        'title',
        'nin',
        'surname',
        'first_name',
        'middle_name',
        'gender',
        'dob',
        'town_city',
        'state_residence',
        'lga_residence',
        'address_residence',
        'state_origin',
        'lga_origin',
        'phone',
        'email',
        'photo',
        'status',
        'reason',
        'refunded_at',
        'result_file'
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
