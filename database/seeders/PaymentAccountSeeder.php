<?php

namespace Database\Seeders;

use App\Models\PaymentAccount;
use Illuminate\Database\Seeder;

class PaymentAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'bank_name' => 'Bank Central Asia (BCA)',
                'account_number' => '123 456 7890',
                'account_holder' => 'Yayasan Pendidikan Stella Gracia',
                'branch' => 'Kupang Utama',
            ],
            [
                'bank_name' => 'Bank Mandiri',
                'account_number' => '098 765 4321',
                'account_holder' => 'Yayasan Pendidikan Stella Gracia',
                'branch' => 'Kupang Merdeka',
            ],
        ];

        foreach ($accounts as $account) {
            PaymentAccount::updateOrCreate(
                ['account_number' => $account['account_number']],
                $account,
            );
        }
    }
}
