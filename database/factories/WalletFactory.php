<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wallet>
 */
class WalletFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $banks = ['BNI', 'BRI', 'CASH'];
        $bank = $this->faker->randomElement($banks).'_'.$this->faker->name();

        return [
            'wallet_name'=>$bank,
            'prev_saldo'=>rand(1000,10000),
            'saldo'=>rand(1000,10000)
        ];
    }
}
