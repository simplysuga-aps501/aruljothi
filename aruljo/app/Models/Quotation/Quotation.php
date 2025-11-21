<?php

namespace App\Models\Quotation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lead_id', 'quote_number', 'total_amount',
        'current_version', 'status', 'created_by', 'modified_by'
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
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
}
