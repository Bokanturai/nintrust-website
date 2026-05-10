<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentService extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'user_id',
        'service_id',
        'transaction_id',
        'service_type',
        'field_code',
        'ticket_id',
        'batch_id',
        'request_id',
        'our_id',
        'tracking_id',
        'request_email',
        'email_auth',
        'other_bank',
        'bvn',
        'nin',
        'number',
        'first_name',
        'last_name',
        'middle_name',
        'gender',
        'dob',
        'email',
        'amount',
        'lga',
        'state',
        'field_name',
        'service_name',
        'service_field_name',
        'bank',
        'description',
        'modification_data',
        'affidavit',
        'affidavit_file_url',
        'file_url',
        'passport_url',
        'nin_slip_url',
        'cac_file',
        'memart_file',
        'status_report_file',
        'tin_file',
        'field',
        'performed_by',
        'approved_by',
        'completed_by',
        'submission_date',
        'status',
        'comment',
        'company_name',
        'registration_number',
        'phone_number',
        'city',
        'house_number',
        'street_name',
        'country',
        'cac_certificate',
    ];

    protected $casts = [
        'modification_data' => 'array',
        'api_response' => 'array', // Note: api_response is not in the new table, using 'description' or metadata for that if needed, or I can add it back if it's missing. Actually I used api_response in the controller earlier. 
    ];

    /**
     * Get the user who made the submission.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the service requested.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the transaction associated with this submission.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
