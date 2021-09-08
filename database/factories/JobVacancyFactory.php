<?php

namespace Database\Factories;

use App\Models\JobVacancy;
use App\Helpers\Lorem;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobVacancyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = JobVacancy::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $createdAt = date("Y-m-d H:i:s", rand(strtotime("-6 month"), strtotime("-1 min")));
        $endsAt = date('Y-m-d', strtotime("+30 day", strtotime($createdAt)));

        return [
            'slug' => md5(uniqid(rand(), true)),
            'title' => $this->faker->text($maxNbChars = 150),
            'description' => $this->faker->text($maxNbChars = 2000),
            'posted_by' => \App\Models\User::inRandomOrder()->first()->id,
            'job_id' => \App\Models\Job::inRandomOrder()->first()->id,
            'city_id' => \App\Models\City::inRandomOrder()->first()->id,
            'is_active' => rand(0,10) >= 2,
            'created_at' => $createdAt,
            'ends_at' => $endsAt,
            'deleted_at' => null,
        ];
    }
}
