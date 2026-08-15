<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MaxDev\Models\Certificate;

class CertificateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Certificate::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
        "name"          =>          ['en'=>$this->faker->name,'ar'=>$this->faker->name],
        "logo"          =>          $this->faker->name,
        "donor_name"          =>          ['en'=>$this->faker->name,'ar'=>$this->faker->name],
        "description"          =>          ['en'=>$this->faker->name,'ar'=>$this->faker->name],
        "code"          =>          $this->faker->name,
        "link"          =>          $this->faker->name,
        'status'          =>          rand(0,1),
        "is_active"          =>          $this->faker->name,
        ];
    }
}
