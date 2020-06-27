<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Task;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\DB;

$factory->define(Task::class, function (Faker $faker) {
    return [
        'title' => $faker->jobTitle,
        'description' => $faker->sentence,
        'status' => $faker->randomElement(Task::STATUS_ENUM),
        'user_id' => DB::table('users')->inRandomOrder()->first('user_id')->user_id
    ];
});