<?php

namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailClaimConfiguration extends Model
{

    use UuidAsId, SoftDeletes, SecureDelete;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'email', 'host', 'port', 'protocol', 'password', 'institution_id', 'subscriber_id', 'type', 'app_tenant', 'app_client_secret', 'app_client_id'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Get the claims associated with the currency
     * @return BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}
