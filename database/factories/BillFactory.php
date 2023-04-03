<?php

namespace Database\Factories;

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
      //  $types = ['bulanan','one time','tahunan'];
        return [
            'bill_amount' => $num = rand(1000, 10000),
            'bill_remainder' => rand(1000, $num),
            'due_date' => $this->faker->dateTime('+ 1 month')->format('Y-m-d'),
            'user_id' => rand(1,3),
            'account_id' => rand(1,4),
            'payment_status'=> rand(1,3)
        ];
    }
}
