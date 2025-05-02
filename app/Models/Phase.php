<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function expedient(): BelongsTo
    {
        return $this->belongsTo(Expedient::class);
    }
}
