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
    protected $casts = ['others' => 'array'];

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
         'type_clients_id', 'category_clients_id', 'identites_id', 'others'
    ];

    /**
     * Get the type_client associated with the type client-from-my-institution
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type_client()
    {
        return $this->belongsTo(TypeClient::class, 'type_clients_id');
    }

    /**
     * Get the category_client associated with the Categorie client-from-my-institution
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category_client()
    {
        return $this->belongsTo(CategoryClient::class, 'category_clients_id');
    }

    public function identite()
    {
        return $this->belongsTo(Identite::class, 'identites_id');
    }

    /**
     * Get the accounts associated with the client-from-my-institution
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }


}