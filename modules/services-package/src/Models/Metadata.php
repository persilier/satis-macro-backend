<?php

namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Metadata extends Model
{
    use HasTranslations, UuidAsId, SoftDeletes, SecureDelete;
    /**
     * The attributes that are translatable
     *
     * @var array
     */
    public $translatable = ['data'];

    protected $casts = [
        'data' => 'json',
    ];
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
        'name', 'data'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public static function getRoles() {
        // ... you business logic.
    }

}
