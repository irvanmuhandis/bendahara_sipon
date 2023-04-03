<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trans>
 */
class TransFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'account_id' => Account::where('account_name', '=', 'Kebutuhan')->first()->id,
            'wallet_id' => rand(1, 3),
            'title' => $this->faker->sentence(),
            'out' => $out = rand(0, 1000),
            'in' => $out == 0 ? rand(1000, 2000) : 0,
            'user_id' => rand(1, 10)
        ];
    }
}
