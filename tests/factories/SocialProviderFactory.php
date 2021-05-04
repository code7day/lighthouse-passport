<?php

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Code7day\LighthousePassportLogin\Tests\User;

app(Factory::class)->define(\Code7day\LighthousePassportLogin\Models\SocialProvider::class, function (Faker $faker) {
    return [
        'user_id' => factory(User::class)->create()->id,
        'provider' => 'github',
        'provider_id' => 'fakeId',
    ];
});
