<?php

namespace Database\Factories;

use App\Models\Santri;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bill>
 */
class BillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {

        $santri = Santri::inRandomOrder()->first();
        return [
            'amount' => $num = rand(1000, 10000),
            'remainder' => $num,
            'due_date' => $this->faker->dateTime('+ 1 month')->format('Y-m'),
            'nis' => $santri->nis,
            'account_id' => rand(2, 5),
            'operator_id' => rand(1, 3),
            'payment_status' => 1
        ];
    }
}
