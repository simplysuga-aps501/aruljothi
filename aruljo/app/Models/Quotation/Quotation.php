<?php

namespace App\Models\Quotation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Lead;
use App\Models\User;
use App\Models\Customer;

class Quotation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lead_id',
        'customer_id', // ✅ newly added
        'quote_number',
        'total_amount',
        'current_version',
        'status',
        'created_by',
        'modified_by',
    ];

    /** Relationships **/

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function versions()
    {
        return $this->hasMany(QuoteVersion::class);
    }

    public function activeVersion()
    {
        return $this->belongsTo(QuoteVersion::class, 'current_version');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Helper relationships **/

    public function products()
    {
        return $this->hasManyThrough(
            QuoteTruckProduct::class,
            QuoteTruck::class,
            'quote_version_id',
            'quote_truck_id',
            'current_version',
            'id'
        );
    }

    public function priceDetails()
    {
        return $this->hasManyThrough(
            QuotePriceDetail::class,
            QuoteVersion::class,
            'quotation_id',
            'quote_version_id',
            'id',
            'id'
        );
    }
}
