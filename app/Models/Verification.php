<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'user_id',
        'service_field_id',
        'service_id',
        'idno',
        'type',
        'nin',
        'firstname',
        'middlename',
        'surname',
        'first_name',
        'middle_name',
        'last_name',
        'phoneno',
        'telephoneno',
        'email',
        'dob',
        'birthdate',
        'gender',
        'birthstate',
        'birthlga',
        'birthcountry',
        'maritalstatus',
        'registrationDate',
        'enrollmentBank',
        'enrollmentBranch',
        'watchListed',
        'levelOfAccount',
        'stateOfResidence',
        'lgaOfResidence',
        'residentialAddress',
        'address',
        'enrollment_branch',
        'enrollment_bank',
        'residence_address',
        'residence_state',
        'residence_lga',
        'residence_town',
        'religion',
        'employmentstatus',
        'educationallevel',
        'profession',
        'heigth',
        'title',
        'number_nin',
        'tax_id',
        'tax_residency',
        'amount',
        'vnin',
        'photo',
        'photo_path',
        'signature',
        'signature_path',
        'trackingId',
        'userid',
        'performed_by',
        'approved_by',
        'nok_firstname',
        'nok_middlename',
        'nok_surname',
        'nok_address1',
        'nok_address2',
        'nok_lga',
        'nok_state',
        'nok_town',
        'nok_postalcode',
        'self_origin_state',
        'self_origin_lga',
        'self_origin_place',
        'transaction_id',
        'status',
        'submission_date',
        'registration_date',
        'state',
        'lga',
        'town',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
