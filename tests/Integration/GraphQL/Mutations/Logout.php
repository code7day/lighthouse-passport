<?php

namespace Code7day\LighthousePassportLogin\Tests\Integration\GraphQL\Mutations;

use Illuminate\Support\Facades\Event;
use Code7day\LighthousePassportLogin\Events\UserLoggedOut;
use Code7day\LighthousePassportLogin\Tests\TestCase;
use Code7day\LighthousePassportLogin\Tests\User;

class Logout extends TestCase
{
    public function test_it_invalidates_token_on_logout()
    {
        Event::fake([UserLoggedOut::class]);

        $this->artisan('migrate', ['--database' => 'testbench']);
        $user = factory(User::class)->create();
        $this->createClientPersonal($user);
        $token = $user->createToken('test Token');
        $token = $token->accessToken;
        $response = $this->postGraphQL([
            'query' => 'mutation {
                logout {
                    status
                    message
                }
            }',
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);
        $responseBody = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('logout', $responseBody['data']);
        $this->assertArrayHasKey('status', $responseBody['data']['logout']);
        $this->assertArrayHasKey('message', $responseBody['data']['logout']);
        Event::assertDispatched(UserLoggedOut::class, function (UserLoggedOut $event) use ($user) {
            return $user->id === $event->user->id;
        });
    }
}
