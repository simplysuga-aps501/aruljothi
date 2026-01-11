<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'poc_name', 'phone', 'alternate_phone', 'email',
        'address_line1', 'address_line2', 'district', 'state',
        'pincode', 'gst_number'
    ];


    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }
}
