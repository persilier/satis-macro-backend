<?php


namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use UuidAsId, SoftDeletes, SecureDelete;
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['others' => 'json'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lastname', 'firstname', 'gender', 'phone', 'email','ville', 'id_card','is_client','account_number',
        'type_clients_id', 'category_clients_id', 'units_id', 'institutions_id', 'others'
    ];

    /**
     * Get the type_clients associated with the institution
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institutions_id');
    }

    /**
     * Get the type_client associated with the type client
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type_client()
    {
        return $this->belongsTo(TypeClient::class, 'type_clients_id');
    }

    /**
     * Get the category_client associated with the Categorie client
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category_client()
    {
        return $this->belongsTo(CategoryClient::class, 'type_clients_id');
    }

    /**
     * Get the unit associated with the unit
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'units_id');
    }


}