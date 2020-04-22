<?php
namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Illuminate\Database\Eloquent\SoftDeletes;
class TypeClient extends Model
{

    use UuidAsId, SoftDeletes, SecureDelete;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'name',
        'description',
        'institutions_id'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institutions_id', 'id');
    }


}
