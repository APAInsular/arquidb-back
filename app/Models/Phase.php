<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Record;

class Phase extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'phase',
        'title',
        'observations',
        'objections',
        'record_date',
        'state',
        'sign_date',
        'expedient_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'expedient_id' => 'integer',
        'phase' => 'string' // Añadir esto para consistencia
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!preg_match('/^\d{3,4}$/', $model->phase) || $model->phase < '000' || $model->phase > '9999') {
                throw new \InvalidArgumentException('El campo phase debe ser un número entre 000 y 9999 (3 o 4 dígitos).');
            }
        });
    }
    protected static function booted()
    {
        static::created(function ($phase) {
            self::logAction($phase, 'create');
        });

        static::updated(function ($phase) {
            self::logAction($phase, 'update');
        });

        static::deleted(function ($phase) {
            self::logAction($phase, 'delete');
        });
    }

    protected static function logAction($phase, $action)
    {
        Record::create([
            'user_id' => Auth::id(),
            'name' => optional(Auth::user())->name,
            'action' => $action,
            'affected_table' => 'phases',
            'affected_record_id' => $phase->id,
        ]);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function expedient(): BelongsTo
    {
        return $this->belongsTo(Expedient::class);
    }

    public function scopeCenters($query, $centerId)
    {

        if (Auth::user()->hasRole('superAdmin')) {
            return $query;
        } else {
            return $query->whereHas('expedient', function ($q) use ($centerId) {
                $q->where('center_id', $centerId);
            });
        }
    }
}
