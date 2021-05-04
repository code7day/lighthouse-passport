<?php

namespace Code7day\LighthousePassportLogin\Tests\Unit;

use Code7day\LighthousePassportLogin\Tests\TestCase;
use Code7day\LighthousePassportLogin\Tests\User;
use Laravel\Passport\Passport;

class HasLoggedInTokens extends TestCase
{
    public function test_it_gets_passport_tokens()
    {
        $this->createClient();
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        $tokens = $user->getTokens();
        $this->assertArrayHasKey('access_token', $tokens);
        $this->assertArrayHasKey('refresh_token', $tokens);
        $this->assertArrayHasKey('expires_in', $tokens);
        $this->assertArrayHasKey('token_type', $tokens);
        $this->assertNotNull($tokens['access_token']);
        $this->assertNotNull($tokens['refresh_token']);
        $this->assertNotNull($tokens['expires_in']);
        $this->assertNotNull($tokens['token_type']);
    }
}
