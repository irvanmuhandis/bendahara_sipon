<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $types = [1, 2, 3];

        $type = $this->faker->randomElement($types);

        $name = $this->faker->randomElement(['Syahriah', 'Madin', 'Wifi', 'Utang']);

        return [
            'account_name' => $name,
            'account_type' => $type,
            'created_at'=>now(),
            'updated_at'=>now()
        ];
    }
}
