<?php

namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Satis2020\ServicePackage\Traits\UuidAsId;

class Revival extends Model
{
    use UuidAsId;

    const STATUS_AWAITING = "awaiting";
    const STATUS_CONSIDERED = "considered";

    protected $fillable = [
        "claim_id",
        "message",
        "staff_unit_id",
        "claim_status",
        "targeted_staff_id",
        "institution_id",
        "created_by",
        "status",
    ];

    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Staff::class,"created_by");
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class,"targeted_staff_id");
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function getStatusAttribute()
    {
        if ($this->claim()->first()->status!=$this->attributes['claim_status']){
            $this->status = self::STATUS_CONSIDERED;
            $this->save();
            $this->attributes['status'] = self::STATUS_CONSIDERED;
        }

        return $this->attributes['status'];
    }
}
