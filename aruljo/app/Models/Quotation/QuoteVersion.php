<?php

namespace App\Models\Quotation;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Customer;
use App\Models\DistancePincode;

class QuoteVersion extends Model
{
    protected $fillable = [
        'quotation_id', 'version_number', 'subtotal', 'gst_rate', 'net_total',
        'distance_km', 'delivery_location_id', 'remarks', 'is_active', 'created_by',
        'customer_id', 'customer_name', 'customer_contact',
        'customer_address_line1', 'customer_address_line2',
        'customer_district', 'customer_state', 'customer_pincode', 'customer_gst_number',
        'pdf_date', 'pdf_subject', 'pdf_terms', 'pdf_delivery'
    ];


    /** -----------------------------
     * Relationships
     * ----------------------------- */

    // Belongs to parent quotation
    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    // Trucks in this version
    public function trucks()
    {
        return $this->hasMany(QuoteTruck::class, 'quote_version_id');
    }

    // Products allocated across trucks
    public function products()
    {
        return $this->hasManyThrough(
            QuoteTruckProduct::class,
            QuoteTruck::class,
            'quote_version_id',
            'quote_truck_id',
            'id',
            'id'
        );
    }

    // Price details for this version
    public function priceDetails()
    {
        return $this->hasMany(QuotePriceDetail::class, 'quote_version_id');
    }

    // Creator (user who made this version)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Linked Customer (live record)
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    // Delivery location reference
    public function deliveryLocation()
    {
        return $this->belongsTo(DistancePincode::class, 'delivery_location_id');
    }
    public function additionalFields()
    {
        return $this->hasMany(QuoteAdditionalField::class, 'quote_version_id')->orderBy('sort_order');
    }

}
