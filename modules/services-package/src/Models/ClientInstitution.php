<?php


namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientInstitution extends Model
{
    use UuidAsId, SoftDeletes, SecureDelete;
    protected $table = 'client_institution';
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
        'client_id', 'category_client_id', 'institution_id'
    ];

    /**
     * Get the institution associated with the account
     * @return BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Get the client-from-my-institution associated with the account
     * @return BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the category_client associated with the Client Institution
     * @return BelongsTo
     */
    public function category_client()
    {
        return $this->belongsTo(CategoryClient::class);
    }

    /**
     * Get the accounts associated with the client
     * @return HasMany
     */
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

}