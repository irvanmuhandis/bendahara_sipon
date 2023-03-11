<?php

namespace Database\Factories;

use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title'=>$this->faker->sentence(),
            'desc'=>$this->faker->paragraph(),
            'status'=>rand(1,3),
            'client_id'=>Client::factory()->create()->id,
            'start_time'=>$start = $this->faker->dateTimeBetween("-1 year","+1 year"),
            'end_time'=>Carbon::parse($start)->addHours(4),
        ];
    }
}
