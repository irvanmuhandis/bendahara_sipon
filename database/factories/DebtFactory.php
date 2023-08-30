<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Santri;
use App\Models\Account;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Debts>
 */
class DebtFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $end = rand(1000, 9999);
        $remain = rand(0, $end);
        $status = 1;
        switch ($end) {
            case $remain:
                $status = 1;
                break;
            case 0:
                $status = 3;
                break;
            default:
                $status = 2;
                break;
        }

        $santri = Santri::inRandomOrder()->first();
        return [
            'account_id' => Account::where('account_name','=','Utang')->first()->id,
            'payment_status' => $status,
            'title' => 'Utang',
            'wallet_id' => Wallet::first()->id,
            'operator_id'=>rand(1,3),
            'amount' => $end,
            'nis' => $santri->nis,
            'remainder' => $remain,
        ];
    }
}
