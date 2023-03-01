<?php

namespace Satis2020\ServicePackage\Models;

use DateTime;
use DatePeriod;
use DateInterval;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Satis2020\ServicePackage\Models\Treatment;
use Satis2020\Escalation\Models\TreatmentBoard;
use Satis2020\ServicePackage\Models\AccountType;
use Satis2020\ServicePackage\Traits\ActivePilot;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Satis2020\ServicePackage\Services\StaffService;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Satis2020\ServicePackage\Traits\AwaitingAssignment;
use Satis2020\ServicePackage\Repositories\TreatmentRepository;
use Satis2020\ServicePackage\Traits\ClaimTrait;

/**
 * Class Claim
 * @package Satis2020\ServicePackage\Models
 */
class Claim extends Model
{
    use HasTranslations, UuidAsId, SoftDeletes, SecureDelete, DataUserNature, ActivePilot, AwaitingAssignment,ClaimTrait;
    const PERSONAL_ACCOUNT = 'A TITRE PERSONNEL';
    const CLAIM_INCOMPLETE = "incomplete";
    const CLAIM_FULL = "full";
    const CLAIM_TRANSFERRED_TO_UNIT = "transferred_to_unit";
    const CLAIM_TRANSFERRED_TO_TARGET_INSTITUTION = "transferred_to_targeted_institution";
    const CLAIM_ASSIGNED_TO_STAFF = "assigned_to_staff";
    const CLAIM_TREATED = "treated";
    const CLAIM_VALIDATED = "validated";
    const CLAIM_TRANSFERRED_TO_STAFF_FOR_SATISFACTION = "transferred_to_staff_for_satisfactiion";
    const CLAIM_ARCHIVED = "archived";
    const CLAIM_CLOSED = "closed";
    const CLAIM_UNSATISFIED = "unsatisfied";


    const CLAIM_TRANSFERRED_TO_COMITY = "transferred_to_comity";
    const CLAIM_AT_DISCUSSION = "at_discussion";
    const CLAIM_RESOLVED = "resolved";


    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    /*protected static function booted()
    {
        static::addGlobalScope('toBeProcessed', function (Builder $builder) {
            $builder->whereNull('revoked_at');
        });
    }*/

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
    protected $dates = [
        'deleted_at',
        'event_occured_at',
        'completed_at',
        'archived_at',
        'created_at',
        'updated_at',
        'revoked_at',
        'closed_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reference',
        'description',
        'lieu',
        'claim_object_id',
        'claimer_id',
        'relationship_id',
        'account_targeted_id',
        'institution_targeted_id',
        'unit_targeted_id',
        'request_channel_slug',
        'response_channel_slug',
        'event_occured_at',
        'claimer_expectation',
        'amount_disputed',
        'amount_currency_slug',
        'is_revival',
        'created_by',
        'completed_by',
        'completed_at',
        'active_treatment_id',
        'archived_at',
        'status',
        'time_limit',
        'revoked_at',
        'revoked_by',
        'account_number',
        'plain_text_description',
        'closed_at',
        'treatment_board_id',
        'escalation_status',
        'time_unit',
        'time_staff',
        'time_treatment',
        'time_validation',
        'time_measure_satisfaction'
    ];

    protected $appends = [
        'timeExpire', 
        'accountType', 
        'canAddAttachment', 
        'lastRevival',
        'canAddAttachment',
        "oldActiveTreatment", 
        'dateExpire',
        'is_rejected',
        'is_duplicate',
       //'timeUnit',
        //'timeStaff',
        'timeLimitTreatment',
       // 'timeValidation',
       // 'timeMeasureSatisfaction' 
    ];


    public function gettimeLimitTreatmentAttribute()
    {
        $duration_done = null;
        $ecart = null;
        if ($this->time_limit && $this->created_at && ($this->status !== 'archived')) {

            $claimInfo = $this->activeTreatment;
            if ($claimInfo && $claimInfo->solved_at !== null) {
               
                $duration_done = $this->daysWithoutWeekEnd($claimInfo->assigned_to_staff_at,$claimInfo->solved_at);
                $ecart = $this->conversion($this->time_treatment) -  $duration_done;
  
            }
        }

        return [
            "global_delay" => $this->time_limit,
            "Quota_delay_assigned" => $this->time_treatment,
            "duration_done" => $duration_done,
            "ecart" =>  $ecart,
           
        ];
    }


    public function daysWithoutWeekEnd($start,$end)
    {       
        
        $interval = $end->diff($start);
        
        // total days
        $days = $interval->days;
        
        // create an iterateable period of date (P1D equates to 1 day)
        $period = new DatePeriod($start, new DateInterval('P1D'), $end);
        
        foreach($period as $dt) {
            $curr = $dt->format('D');
        
            // substract if Saturday or Sunday
            if ($curr == 'Sat' || $curr == 'Sun') {
                $days--;
            }
        }

       return $days;
    }

   


    // public function getSatisfactionHistoryAttribute()
    // {
    //     if ($this->activeTreatment) {
    //         $treatments = collect($this->activeTreatment->satisfaction_history)->map(function ($item) {
    //             $item = collect($item);
    //             $item['satisfaction_measured_by'] =  Staff::with('identite.user', 'unit')->find($item->get("satisfaction_measured_by"));
    //             return $item->only([
    //                 "is_claimer_satisfied",
    //                 "satisfaction_measured_by",
    //                 "satisfaction_measured_at",
    //                 "unsatisfied_reason",
    //                 "note"
    //             ]);
    //         });
    //         return $treatments;
    //     } else {
    //         return [];
    //     }
    // }

    /**
     * @return mixed
     */
    public function gettimeExpireAttribute()
    {
        $diff = null;
        $dateExpire = $this->getDateExpireAttribute();
        if ($dateExpire && ($this->status !== 'archived')) {
            $dateExpire = $this->created_at->copy()->addWeekdays($this->time_limit);
            $diff = now()->diffInDays($dateExpire, false);
        }

        return $diff;
    }
    public function getDateExpireAttribute()
    {
        $dateExpire = null;
        if ($this->time_limit && $this->created_at && ($this->status !== 'archived')) {
            $dateExpire = $this->created_at->copy()->addWeekdays($this->time_limit);
        }

        return $dateExpire;
    }

    /**
     * @return |null
     */
    public function getaccountTypeAttribute()
    {

        return $this->accountTargeted ? AccountType::find($this->accountTargeted->account_type_id)->name : self::PERSONAL_ACCOUNT;
    }


    /**
     * Get the claimObject associated with the claim
     * @return BelongsTo
     */
    public function claimObject()
    {
        return $this->belongsTo(ClaimObject::class);
    }

    /**
     * Get the claimer associated with the claim
     * @return BelongsTo
     */
    public function claimer()
    {
        return $this->belongsTo(Identite::class);
    }

    /**
     * Get the relationship associated with the claim
     * @return BelongsTo
     */
    public function relationship()
    {
        return $this->belongsTo(Relationship::class);
    }

    /**
     * Get the accountTargeted associated with the claim
     * @return BelongsTo
     */
    public function accountTargeted()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the institutionTargeted associated with the claim
     * @return BelongsTo
     */
    public function institutionTargeted()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Get the unitTargeted associated with the claim
     * @return BelongsTo
     */
    public function unitTargeted()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the requestChannel associated with the claim
     * @return BelongsTo
     */
    public function requestChannel()
    {
        return $this->belongsTo(Channel::class, 'request_channel_slug', 'slug');
    }

    /**
     * Get the responseChannel associated with the claim
     * @return BelongsTo
     */
    public function responseChannel()
    {
        return $this->belongsTo(Channel::class, 'response_channel_slug', 'slug');
    }

    /**
     * Get the amountCurrency associated with the claim
     * @return BelongsTo
     */
    public function amountCurrency()
    {
        return $this->belongsTo(Currency::class, 'amount_currency_slug', 'slug');
    }

    /**
     * Get the staff who register the claim
     * @return BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    /**
     * Get the staff who complete the claim
     * @return BelongsTo
     */
    public function completedBy()
    {
        return $this->belongsTo(Staff::class, 'completed_by');
    }

    /**
     * Get all of the claim's files.
     * @return MorphMany
     */
    public function files()
    {
        return $this->morphMany(File::class, 'attachmentable');
    }

    /**
     * Get all of the claim's files attach at treatment.
     * @return MorphMany
     */
    public function filesAtTreatment()
    {
        return $this->morphMany(File::class, 'attachmentable')->where('attach_at', File::ATTACH_AT_TREATMENT);
    }

    /**
     * Get the treatments associated with the claim
     * @return HasMany
     */
    public function treatments()
    {
        return $this->hasMany(Treatment::class);
    }

    /**
     * Get the active treatment associated with the claim
     * @return BelongsTo
     */
    public function activeTreatment()
    {
        return $this->belongsTo(Treatment::class);
    }

    /**
     * Get the active treatment associated with the claim
     * @return Builder|Model|object
     */
    public function getOldActiveTreatmentAttribute()
    {
        if (isEscalationClaim($this)) {
            return (new TreatmentRepository)->getClaimOldTreatment($this->id);
        }

        return null;
    }

    /**
     * Get the discussions associated with the claim
     * @return HasMany
     */
    public function discussions()
    {
        return $this->hasMany(Discussion::class);
    }

    /**
     * Get the staff who register the claim
     * @return BelongsTo
     */
    public function revokedBy()
    {
        return $this->belongsTo(Staff::class, 'revoked_by');
    }

    function getCanAddAttachmentAttribute()
    {
        $canAttach = false;
        if (Auth::user()){
            $staffId = request()->query('staff', $this->staff()->id);
            $staff = (new StaffService())->getStaffById($staffId);

            if ($staff != null) {
                if ($this->status == Claim::CLAIM_ASSIGNED_TO_STAFF || ($this->status == Claim::CLAIM_UNSATISFIED && $this->escalation_status == Claim::CLAIM_ASSIGNED_TO_STAFF)) {
                    $canAttach = $this->activeTreatment->responsible_staff_id == $staff->id;
                }

                /* if ($this->status==Claim::CLAIM_VALIDATED){
                    $canAttach = $staff->id == $staff->institution->active_pilot_id;
                }*/
            }
            if (Auth::user()) {
                if ($this->status == Claim::CLAIM_FULL && $this->allowOnlyActivePilot($this->staff())) {
                    $canAttach = true;
                }
            }
        }


        return $canAttach;
    }

    /**
     * @return HasMany
     */
    public function revivals()
    {
        return $this->hasMany(Revival::class);
    }

    public function getLastRevivalAttribute()
    {
        return collect($this->revivals)->sortByDesc('created_at')->first();
    }

    public function getIsRejectedAttribute()
    {
        if (
            !is_null($this->activeTreatment) && !is_null($this->activeTreatment->rejected_at) && !is_null($this->activeTreatment->rejected_reason)
            && !is_null($this->activeTreatment->responsibleUnit)
        ) {
            return true;
        }
        return false;
    }

    public function getIsDuplicateAttribute()
    {
        return $this->getDuplicatesQuery($this->getClaimsQuery($this->institution_targeted_id), $this)->exists();
    }

    /**
     * @return mixed
     */
    public function getPlainTextDescriptionAttribute()
    {
        return $this->attributes['plain_text_description'] == null ?
            $this->attributes['description'] :
            $this->attributes['plain_text_description'];
    }

    /**
     * @return BelongsTo
     */
    public function treatmentBoard()
    {
        return $this->belongsTo(TreatmentBoard::class);
    }
}
