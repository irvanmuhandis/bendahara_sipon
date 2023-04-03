<?php

namespace Database\Factories;

use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GroupHistory>
 */
class GroupHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'group_id' => Group::find(rand(1, 4))->first()->group_id,
            'user_id' => rand(1, 4),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
