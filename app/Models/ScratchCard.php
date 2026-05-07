<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScratchCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'fee',
        'serial_number',
        'pin',
        'status',
        'refno',
        'tnx_id',
        'user_id',
        'purchased_at',
        'active',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'active' => 'boolean',
    ];

    /**
     * The user who purchased the card.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

     public function transactions()
    {
        return $this->belongsTo(Transaction::class, 'tnx_id');
    }
}
