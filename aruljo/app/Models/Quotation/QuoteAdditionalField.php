<?php

namespace App\Models\Quotation;

use Illuminate\Database\Eloquent\Model;

class QuoteAdditionalField extends Model
{
    protected $fillable = ['quote_version_id', 'heading', 'content', 'sort_order'];

    public function version()
    {
        return $this->belongsTo(QuoteVersion::class, 'quote_version_id');
    }
}

