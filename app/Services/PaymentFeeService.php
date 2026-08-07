<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationFee;
use App\Models\PaymentFee;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentFeeService
{
    /**
     * Resolve the active fees for an application without creating a snapshot.
     *
     * @return Collection<int, PaymentFee>
     */
    public function resolve(Application $application): Collection
    {
        $fees = new Collection;

        foreach (['registration', 'assessment', 'admin'] as $type) {
            $query = PaymentFee::query()
                ->where('fee_type', $type)
                ->where('is_active', true);

            if ($type === 'admin') {
                $fee = $query
                    ->whereNull('academic_year_id')
                    ->whereNull('school_level_id')
                    ->orderBy('sort_order')
                    ->first();
            } else {
                $fee = $query
                    ->where('academic_year_id', $application->academic_year_id)
                    ->where('school_level_id', $application->school_level_id)
                    ->orderBy('sort_order')
                    ->first()
                    ?? PaymentFee::query()
                        ->where('fee_type', $type)
                        ->where('is_active', true)
                        ->whereNull('academic_year_id')
                        ->whereNull('school_level_id')
                        ->orderBy('sort_order')
                        ->first();
            }

            if ($fee) {
                $fees->push($fee);
            }
        }

        return $fees->sortBy('sort_order')->values();
    }

    /**
     * Ensure an immutable fee snapshot exists for the application.
     *
     * @return Collection<int, ApplicationFee>
     */
    public function ensureSnapshot(Application $application): Collection
    {
        $existing = $application->fees()->get();

        if ($existing->isNotEmpty()) {
            return $existing;
        }

        $fees = $this->resolve($application);

        if ($fees->isEmpty()) {
            throw new RuntimeException('No active payment fees are configured for this application.');
        }

        return DB::transaction(function () use ($application, $fees): Collection {
            if ($application->fees()->exists()) {
                return $application->fees()->get();
            }

            foreach ($fees as $fee) {
                $application->fees()->create([
                    'payment_fee_id' => $fee->id,
                    'fee_type' => $fee->fee_type,
                    'name' => $fee->name,
                    'description' => $fee->description,
                    'amount' => $fee->amount,
                    'sort_order' => $fee->sort_order,
                ]);
            }

            return $application->fees()->get();
        });
    }

    /**
     * Get the application snapshot or create one from current active fees.
     *
     * @return Collection<int, ApplicationFee>
     */
    public function feesFor(Application $application): Collection
    {
        return $this->ensureSnapshot($application);
    }

    /**
     * Calculate payment totals from an application fee collection.
     *
     * @param  Collection<int, ApplicationFee>  $fees
     * @return array{subtotal: int, admin_fee: int, total: int}
     */
    public function totals(Collection $fees): array
    {
        $subtotal = $fees
            ->where('fee_type', '!=', 'admin')
            ->sum('amount');
        $adminFee = (int) $fees
            ->where('fee_type', 'admin')
            ->sum('amount');

        return [
            'subtotal' => $subtotal,
            'admin_fee' => $adminFee,
            'total' => $subtotal + $adminFee,
        ];
    }
}
