<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DistancePincode;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use App\Traits\TracksModifiedBy;
use App\Models\User;

class TpDistrictRate extends Model implements AuditableContract
{
    use HasFactory, Auditable, TracksModifiedBy;

    protected $table = 'tp_district_rates';

    protected $fillable = [
        'location_id',
        'office_id',
        'rate',
        'remarks',
    ];

    /**
     * Each rate belongs to one location (distance_pincode).
     */
    public function location()
    {
        return $this->belongsTo(DistancePincode::class, 'location_id');
    }

    /**
     * Optionally belongs to one transport office.
     */
    public function office()
    {
        return $this->belongsTo(TpOffice::class, 'office_id');
    }

    /**
     * The user who last modified this record
     */
    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    /**
     * The user who created this record (if column exists)
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The user who last updated this record (if column exists)
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Transform audit entries to show readable names instead of IDs.
     */
    public function transformAudit(array $data): array
    {
        // Office
        if (isset($data['new_values']['office_id'])) {
            $newOffice = TpOffice::find($data['new_values']['office_id']);
            $data['new_values']['office_id'] = $newOffice->name ?? $data['new_values']['office_id'];
        }
        if (isset($data['old_values']['office_id'])) {
            $oldOffice = TpOffice::find($data['old_values']['office_id']);
            $data['old_values']['office_id'] = $oldOffice->name ?? $data['old_values']['office_id'];
        }

        // Users (created_by, updated_by, modified_by)
        foreach (['created_by', 'updated_by', 'modified_by'] as $field) {
            if (isset($data['new_values'][$field])) {
                $user = User::find($data['new_values'][$field]);
                $data['new_values'][$field] = $user->name ?? $data['new_values'][$field];
            }
            if (isset($data['old_values'][$field])) {
                $user = User::find($data['old_values'][$field]);
                $data['old_values'][$field] = $user->name ?? $data['old_values'][$field];
            }
        }

        return $data;
    }
}
