<?php

use Faker\Generator as Faker;
use Illuminate\Support\Str;
use SkrdIo\JobsOverview\Models\JobConclusion;

$factory->define(JobConclusion::class, function (Faker $faker) {
    return [
        'type' =>
            'App\\Jobs\\' .
            str_replace(' ', '', Str::title($faker->words(3, true))),
        'is_fail' => $faker->numberBetween(1, 100) <= 10,
        'concluded_at' => $faker->dateTimeBetween('-7 days', 'now'),
    ];
});
