<?php

namespace Code7day\LighthousePassportLogin\Tests\Integration\GraphQL\Mutations;

use Illuminate\Support\Facades\Event;
use Code7day\LighthousePassportLogin\Events\UserRefreshedToken;
use Code7day\LighthousePassportLogin\Tests\TestCase;
use Code7day\LighthousePassportLogin\Tests\User;

class RefreshToken extends TestCase
{
    public function test_it_refresh_a_token()
    {
        Event::fake([UserRefreshedToken::class]);
        $this->createClient();
        $user = factory(User::class)->create();
        $response = $this->postGraphQL([
            'query' => 'mutation {
                login(input: {
                    username: "user@demo.com",
                    password: "12345678"
                }) {
                    access_token
                    refresh_token
                }
            }',
        ]);
        $responseBody = json_decode($response->getContent(), true);
        $responseRefreshed = $this->postGraphQL([
            'query' => 'mutation {
                refreshToken(input: {
                    refresh_token: "'.$responseBody['data']['login']['refresh_token'].'"
                }) {
                    access_token
                    refresh_token
                }
            }',
        ]);
        $responseBodyRefreshed = json_decode($responseRefreshed->getContent(), true);
        $this->assertNotEquals($responseBody['data']['login']['access_token'], $responseBodyRefreshed['data']['refreshToken']['access_token']);
        Event::assertDispatched(UserRefreshedToken::class, function (UserRefreshedToken $event) use ($user) {
            return $user->id === $event->user->id;
        });
    }
}
