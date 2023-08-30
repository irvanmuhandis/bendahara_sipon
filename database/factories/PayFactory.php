<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\User;
use App\Models\Debts;
use App\Models\Santri;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pay>
 */
class PayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $id = rand(1, 5);
        // $type = [Debts::class, Bill::class];
        // $choice = $this->faker->randomElement($type);
        // $pay = 0;
        // if ($choice == Debts::class) {
        //     $debt = Debts::find($id)->first()->debt;
        //     $pay = rand(0, $debt);
        // };
        // if ($choice == Bill::class) {
        //     $bill = Bill::find($id)->first()->bill_amount;
        //     $pay = rand(0, $bill);
        // }

        $santri = Santri::inRandomOrder()->first();
        return [
            'payment' => rand(1000,9000),
            'nis'=>$santri->nis,
            'operator_id'=>rand(1,3),
            'wallet_id' => Wallet::find(rand(1, 3))->id,
            'payable_id' => $id,
            'payable_type' => Bill::class
        ];
    }
}
