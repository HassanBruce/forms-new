<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use BelongsToCompany;

    public const TYPES = ['flight' => 'Flight', 'hotel' => 'Hotel', 'transport' => 'Transport', 'visa' => 'Visa', 'other' => 'Other'];

    protected $guarded = ['id', 'company_id'];
}
