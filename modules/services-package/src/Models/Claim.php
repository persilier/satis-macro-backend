<?php

namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Spatie\Translatable\HasTranslations;

/**
 * Class Claim
 * @package Satis2020\ServicePackage\Models
 */
class Claim extends Model
{
    use HasTranslations, UuidAsId, SoftDeletes, SecureDelete;

    /**
     * The attributes that are translatable
     *
     * @var array
     */
    public $translatable = [];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'event_occured_at', 'completed_at', 'archived_at', 'created_at', 'updated_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reference', 'description', 'claim_object_id', 'claimer_id', 'relationship_id', 'account_targeted_id',
        'institution_targeted_id', 'unit_targeted_id', 'request_channel_slug', 'response_channel_slug',
        'event_occured_at', 'claimer_expectation', 'amount_disputed', 'amount_currency_slug', 'is_revival',
        'created_by', 'completed_by', 'completed_at', 'active_treatment_id', 'archived_at', 'status', 'time_limit'
    ];


    protected $appends = ['timeExpire', 'accountType'];

    /**
     * @return mixed
     */
    public function gettimeExpireAttribute()
    {
        $diff = null;

        if ($this->time_limit && $this->created_at && ($this->status !== 'archived')) {

            $dateExpire = $this->created_at->copy()->addWeekdays($this->time_limit);
            $diff = now()->diffInDays(($dateExpire), false);
        }

        return $diff;
    }

    /**
     * @return |null
     */
    public function getaccountTypeAttribute(){

        return $this->accountTargeted ? AccountType::find($this->accountTargeted->account_type_id) : NULL;
    }





    /**
     * Get the claimObject associated with the claim
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function claimObject()
    {
        return $this->belongsTo(ClaimObject::class);
    }

    /**
     * Get the claimer associated with the claim
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function claimer()
    {
        return $this->belongsTo(Identite::class);
    }

    /**
     * Get the relationship associated with the claim
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function relationship()
    {
        return $this->belongsTo(Relationship::class);
    }

    /**
     * Get the accountTargeted associated with the claim
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountTargeted()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the institutionTargeted associated with the claim
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institutionTargeted()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Get the unitTargeted associated with the claim
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unitTargeted()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the requestChannel associated with the claim
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requestChannel()
    {
        return $this->belongsTo(Channel::class, 'request_channel_slug', 'slug');
    }

    /**
     * Get the responseChannel associated with the claim
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function responseChannel()
    {
        return $this->belongsTo(Channel::class, 'response_channel_slug', 'slug');
    }

    /**
     * Get the amountCurrency associated with the claim
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function amountCurrency()
    {
        return $this->belongsTo(Currency::class, 'amount_currency_slug', 'slug');
    }

    /**
     * Get the staff who register the claim
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    /**
     * Get the staff who complete the claim
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function completedBy()
    {
        return $this->belongsTo(Staff::class, 'completed_by');
    }

    /**
     * Get all of the claim's files.
     */
    public function files()
    {
        return $this->morphMany(File::class, 'attachmentable');
    }

    /**
     * Get the treatments associated with the claim
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function treatments()
    {
        return $this->hasMany(Treatment::class);
    }

    /**
     * Get the active treatment associated with the claim
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function activeTreatment()
    {
        return $this->belongsTo(Treatment::class);
    }

    /**
     * Get the discussions associated with the claim
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function discussions()
    {
        return $this->hasMany(Discussion::class);
    }



}
