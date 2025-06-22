<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Lead extends Model implements AuditableContract
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'platform',
        'lead_date',
        'buyer_name',
        'buyer_location',
        'buyer_contact',
        'platform_keyword',
        'product_detail',
        'delivery_location',
        'expected_delivery_date',
        'remarks',
        'follow_up_date',
        'status',
        'assigned_to',
    ];

    // ✅ Optional: Enable audit timestamps
    protected $auditTimestamps = true;
}
