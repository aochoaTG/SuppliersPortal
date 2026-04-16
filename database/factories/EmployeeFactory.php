<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employee_number' => $this->faker->unique()->numerify('####'),
            'first_name'      => $this->faker->firstName(),
            'last_name'       => $this->faker->lastName(),
            'email'           => $this->faker->unique()->safeEmail(),
            'phone'           => $this->faker->phoneNumber(),
            'job_title'       => $this->faker->jobTitle(),
            'department'      => $this->faker->word(),
            'company'         => $this->faker->company(),
            'is_active'       => 'SI',
            'user_id'         => null,
        ];
    }
}
