<?php


namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Satis\CountriesPackage\Traits\HasCountryTrait;
use Satis2020\ServicePackage\Services\InstitutionService;
use Satis2020\ServicePackage\Services\StateService;
use Satis2020\ServicePackage\Traits\ActivityTrait;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\SecureForceDeleteWithoutException;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cviebrock\EloquentSluggable\Sluggable;
use Spatie\Activitylog\Traits\LogsActivity;

class Institution extends Model
{
    use Sluggable, UuidAsId, SoftDeletes, SecureDelete, SecureForceDeleteWithoutException, LogsActivity, ActivityTrait,HasCountryTrait;

    protected static $logName = 'institution';
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['orther_attributes' => 'json'];

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
        'slug', 'name', 'acronyme', 'iso_code', 'default_currency_slug', 'logo', 'institution_type_id',
        'orther_attributes', 'active_pilot_id','country_id'
    ];


    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    /**
     * Get the institution logo.
     *
     * @param  string  $value
     * @return string
     */
    public function getLogoAttribute($value)
    {
        return empty($value) ? null : asset('storage' . $value);
    }

    /**
     * Get the units associated with the institution
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * Get the amountCurrency associated with the claim
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function defaultCurrency()
    {
        return $this->belongsTo(Currency::class, 'default_currency_slug', 'slug');
    }

    /**
     * Get the activePilot associated with the institution
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function activePilot()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the activePilot associated with the institution
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function leadActivePilot()
    {
        return $this->belongsTo(Staff::class,"active_pilot_id","id");
    }

    /**
     * Get the activePilot associated with the institution
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function allActivePilot()
    {
        return $this->hasMany(ActivePilot::class);
    }

    /**
     * Get the positions associated with the institution
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function positions()
    {
        return $this->belongsToMany(Position::class);
    }

    /**
     * Get the institutionType associated with the institution
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institutionType()
    {
        return $this->belongsTo(InstitutionType::class);
    }

    /**
     * Get the staff associated with the institution
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    /**
     * Get the accounts associated with the institution
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function client_institutions()
    {
        return $this->hasMany(ClientInstitution::class);
    }

    /**
     * Get the claims associated with the institution
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function claims()
    {
        return $this->hasMany(Claim::class, 'institution_targeted_id');
    }

    /**
     * Get the institutionMessageApi record associated with the institution.
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function institutionMessageApi()
    {
        return $this->hasOne(InstitutionMessageApi::class);
    }

    public function emailClaimConfiguration()
    {
        return $this->hasOne(EmailClaimConfiguration::class);
    }

}
