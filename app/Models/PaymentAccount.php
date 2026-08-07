<?php

namespace App\Models;

use Database\Factories\PaymentAccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $bank_name
 * @property string $account_number
 * @property string $account_holder
 * @property string|null $branch
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[
    Fillable([
        'bank_name',
        'account_number',
        'account_holder',
        'branch',
        'is_active',
    ]),
]
class PaymentAccount extends Model
{
    /** @use HasFactory<PaymentAccountFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the uppercase initial for the bank avatar.
     */
    public function initial(): string
    {
        return mb_strtoupper(mb_substr($this->bank_name, 0, 1));
    }
}
