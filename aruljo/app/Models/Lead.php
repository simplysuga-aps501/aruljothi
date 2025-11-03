<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use Spatie\Tags\HasTags;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use App\Models\Product\Product;

class Lead extends Model implements AuditableContract
{
    use SoftDeletes, Auditable;
    use HasTags;

    protected $fillable = [
        'platform',
        'lead_date',
        'buyer_name',
        'buyer_location',
        'buyer_contact',
        'platform_keyword',
        'product_detail',
        'delivery_location',
        'delivery_location_id',
        'expected_delivery_date',
        'remarks',
        'follow_up_date',
        'status',
        'assigned_to',
    ];

    // ✅ Optional: Enable audit timestamps
    protected $auditTimestamps = true;

    public function getLastUpdatedTextAttribute()
    {
        $updatedAt = Carbon::parse($this->updated_at);
        $now = Carbon::now();

        $diffMinutes = $updatedAt->diffInMinutes($now);
        $diffHours   = $updatedAt->diffInHours($now);
        $diffDays    = $updatedAt->diffInDays($now);

        if ($diffMinutes < 60) {
            $roundedMinutes = round($diffMinutes / 15) * 15;
            return $roundedMinutes > 0 ? "{$roundedMinutes} mins ago" : "just now";
        } elseif ($diffHours < 48) {
            return "{$diffHours} hrs ago";
        } else {
            return "{$diffDays} days ago";
        }
    }

    public function getLastUpdatedOrderAttribute()
    {
        return $this->updated_at->timestamp;
    }

    public function products()
    {
        return $this->belongsToMany(
            Product::class,        // Related model
            'lead_product_map',    // Pivot table name
            'lead_id',             // Foreign key in pivot table for this model
            'product_id'           // Foreign key in pivot table for related model
        )->withPivot('quantity')
         ->withTimestamps();
    }

}
