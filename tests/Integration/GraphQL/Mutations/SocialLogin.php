<?php

namespace Code7day\LighthousePassportLogin\Tests\Integration\GraphQL\Mutations;

use Code7day\LighthousePassportLogin\Models\SocialProvider;
use Code7day\LighthousePassportLogin\Tests\TestCase;
use Code7day\LighthousePassportLogin\Tests\User;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

class SocialLogin extends TestCase
{
    use MakesGraphQLRequests;

    public function mockSocialite(SocialProvider $provider)
    {
        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser
            ->shouldReceive('getId')
            ->andReturn($provider->provider_id)
            ->shouldReceive('getName')
            ->andReturn($provider->user->name)
            ->shouldReceive('getEmail')
            ->andReturn($provider->user->email);
        Socialite::shouldReceive('driver->userFromToken')->andReturn($abstractUser);
    }

    public function mockSocialiteWithoutUser()
    {
        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser
            ->shouldReceive('getId')
            ->andReturn('fakeId')
            ->shouldReceive('getName')
            ->andReturn('User Demo')
            ->shouldReceive('getEmail')
            ->andReturn('user@demo.com');
        Socialite::shouldReceive('driver->userFromToken')->andReturn($abstractUser);
    }

    public function mockSocialiteWithUser(User $user)
    {
        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser
            ->shouldReceive('getId')
            ->andReturn('fakeId')
            ->shouldReceive('getName')
            ->andReturn('User Demo')
            ->shouldReceive('getEmail')
            ->andReturn($user->email);
        Socialite::shouldReceive('driver->userFromToken')->andReturn($abstractUser);
    }

    public function test_it_generates_tokens_with_social_grant_for_existing_user()
    {
        $this->createClient();
        $provider = factory(SocialProvider::class)->create();
        $this->mockSocialite($provider);
        $response = $this->graphQL(/* @lang GraphQL */ '
            mutation socialLogin($input: SocialLoginInput!) {
                socialLogin(input: $input) {
                    access_token
                    refresh_token
                    user {
                        id
                        name
                        email
                    }
                }
            }
        ',
            [
                'input' => [
                    'provider' => 'github',
                    'token' => 'some-valid-token-from-github',
                ],
            ]
        );
        $decodedResponse = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('access_token', $decodedResponse['data']['socialLogin']);
        $this->assertArrayHasKey('refresh_token', $decodedResponse['data']['socialLogin']);
    }

    public function test_it_generates_tokens_with_social_grant_for_non_existing_user()
    {
        $this->createClient();
        $this->mockSocialiteWithoutUser();
        $response = $this->graphQL(/* @lang GraphQL */ '
            mutation socialLogin($input: SocialLoginInput!) {
                socialLogin(input: $input) {
                    access_token
                    refresh_token
                    user {
                        id
                        name
                        email
                    }
                }
            }
        ',
            [
                'input' => [
                    'provider' => 'github',
                    'token' => 'some-valid-token-from-github',
                ],
            ]
        );
        $decodedResponse = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('access_token', $decodedResponse['data']['socialLogin']);
        $this->assertArrayHasKey('refresh_token', $decodedResponse['data']['socialLogin']);
        $this->assertDatabaseHas('users', [
            'email' => 'user@demo.com',
            'name' => 'User Demo',
        ]);
        $createdUser = User::first();
        $this->assertDatabaseHas('social_providers', [
            'user_id' => $createdUser->id,
            'provider' => 'github',
            'provider_id' => 'fakeId',
        ]);
    }

    public function test_it_generates_tokens_with_social_grant_for_existing_user_without_social_provider()
    {
        $this->createClient();
        $user = factory(User::class)->create();
        $this->mockSocialiteWithUser($user);
        $this->assertDatabaseMissing('social_providers', [
            'user_id' => $user->id,
        ]);
        $response = $this->graphQL(/* @lang GraphQL */ '
            mutation socialLogin($input: SocialLoginInput!) {
                socialLogin(input: $input) {
                    access_token
                    refresh_token
                    user {
                        id
                        name
                        email
                    }
                }
            }
        ',
            [
                'input' => [
                    'provider' => 'github',
                    'token' => 'some-valid-token-from-github',
                ],
            ]
        );
        $decodedResponse = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('access_token', $decodedResponse['data']['socialLogin']);
        $this->assertArrayHasKey('refresh_token', $decodedResponse['data']['socialLogin']);
        $this->assertDatabaseHas('social_providers', [
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => 'fakeId',
        ]);
    }
}
