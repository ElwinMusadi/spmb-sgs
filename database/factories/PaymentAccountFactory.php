<?php

namespace Database\Factories;

use App\Models\PaymentAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentAccount>
 */
class PaymentAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bank_name' => fake()->randomElement(['Bank Central Asia (BCA)', 'Bank Mandiri', 'Bank Negara Indonesia (BNI)']),
            'account_number' => (string) fake()->numerify('### ### ####'),
            'account_holder' => 'Yayasan Pendidikan Stella Gracia',
            'branch' => 'Kupang '.fake()->randomElement(['Utama', 'Merdeka', 'Oebobo']),
            'is_active' => true,
        ];
    }
}
