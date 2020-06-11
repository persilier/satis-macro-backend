<?php


namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cviebrock\EloquentSluggable\Sluggable;

class Institution extends Model
{
    use Sluggable, UuidAsId, SoftDeletes, SecureDelete;
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
        'slug', 'name', 'acronyme', 'iso_code', 'logo', 'institution_type_id', 'orther_attributes'
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

}