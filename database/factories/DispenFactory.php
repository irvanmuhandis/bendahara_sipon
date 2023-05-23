<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dispen>
 */
class DispenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'dispen_desc'=>$this->faker->sentence(),
            'pay_at'=>date(now()),
            'dispen_periode'=>$this->faker->randomElement(['2023-02','2023-07','2023-04','2023-06']),
            'user_id'=>rand(1,5),
            ];
    }
}
