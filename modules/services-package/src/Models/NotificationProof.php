<?php

namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Satis2020\ServicePackage\Traits\UuidAsId;

class NotificationProof extends Model
{
    use UuidAsId;

    protected $fillable = [
        "to",
        "institution_id",
        "status",
        "channel",
        "sent_at",
        "message"
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function to()
    {
        return $this->belongsTo(Identite::class,"to");
    }
}
