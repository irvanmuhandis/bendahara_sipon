<?php

namespace Database\Factories;

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
        $count = User::count(); // get the total number of records in the users table
        //  $types = ['bulanan','one time','tahunan'];
        return [
            'bill_amount' => $num = rand(1000, 10000),
            'bill_remainder' => $num,
            'due_date' => $this->faker->dateTime('+ 1 month')->format('Y-m'),
            'user_id' => rand(1, $count),
            'account_id' => rand(2, 5),
            'operator_id' => rand(1, 3),
            'payment_status' => 1
        ];
    }
}
