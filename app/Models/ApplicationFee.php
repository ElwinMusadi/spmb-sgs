<?php

namespace App\Models;

use Database\Factories\ApplicationFeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $application_id
 * @property int|null $payment_fee_id
 * @property string $fee_type
 * @property string $name
 * @property string|null $description
 * @property int $amount
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['application_id', 'payment_fee_id', 'fee_type', 'name', 'description', 'amount', 'sort_order'])]
class ApplicationFee extends Model
{
    /** @use HasFactory<ApplicationFeeFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['amount' => 'integer', 'sort_order' => 'integer'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function paymentFee(): BelongsTo
    {
        return $this->belongsTo(PaymentFee::class);
    }
}
