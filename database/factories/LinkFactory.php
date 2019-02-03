<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Link::class, function (Faker $faker) {
    return [
        'title' => $faker->name,
        'link' => $faker->url,
        'created_at' => $faker->date . ' ' . $faker->time,
        'updated_at' => $faker->date . ' ' . $faker->time,
    ];
});
