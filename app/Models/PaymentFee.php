<?php

namespace App\Models;

use Database\Factories\PaymentFeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $fee_type
 * @property string $name
 * @property string|null $description
 * @property int $amount
 * @property int|null $academic_year_id
 * @property int|null $school_level_id
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['fee_type', 'name', 'description', 'amount', 'academic_year_id', 'school_level_id', 'is_active', 'sort_order'])]
class PaymentFee extends Model
{
    /** @use HasFactory<PaymentFeeFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schoolLevel(): BelongsTo
    {
        return $this->belongsTo(SchoolLevel::class);
    }

    public function applicationFees(): HasMany
    {
        return $this->hasMany(ApplicationFee::class);
    }
}
