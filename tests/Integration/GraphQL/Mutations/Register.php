<?php

namespace Code7day\LighthousePassportLogin\Tests\Integration\GraphQL\Mutations;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Code7day\LighthousePassportLogin\Notifications\VerifyEmail;
use Code7day\LighthousePassportLogin\Tests\TestCase;
use Code7day\LighthousePassportLogin\Tests\UserVerifyEmail;

class Register extends TestCase
{
    public function test_it_registers_a_user()
    {
        Notification::fake();
        $this->createClient();
        $response = $this->postGraphQL([
            'query' => 'mutation {
                register(input: {
                    name: "My Name",
                    email: "user@demo.com",
                    password: "12345678",
                    password_confirmation: "12345678"
                }) {
                    tokens {
                        access_token
                        refresh_token
                        user {
                            id
                            name
                            email
                        }
                    }
                    status
                }
            }',
        ]);
        $responseBody = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('register', $responseBody['data']);
        $this->assertArrayHasKey('status', $responseBody['data']['register']);
        $this->assertArrayHasKey('tokens', $responseBody['data']['register']);
        $this->assertArrayHasKey('access_token', $responseBody['data']['register']['tokens']);
        $this->assertArrayHasKey('refresh_token', $responseBody['data']['register']['tokens']);
        $this->assertArrayHasKey('user', $responseBody['data']['register']['tokens']);
        $this->assertArrayHasKey('id', $responseBody['data']['register']['tokens']['user']);
        $this->assertArrayHasKey('name', $responseBody['data']['register']['tokens']['user']);
        $this->assertArrayHasKey('email', $responseBody['data']['register']['tokens']['user']);
        $this->assertEquals('SUCCESS', $responseBody['data']['register']['status']);
    }

    public function test_it_sends_email_verification()
    {
        config()->set('auth.providers.users.model', UserVerifyEmail::class);
        Notification::fake();
        Event::fake([Registered::class]);
        $this->createClient();
        $response = $this->postGraphQL([
            'query' => 'mutation {
                register(input: {
                    name: "My Name",
                    email: "user@demo.com",
                    password: "12345678",
                    password_confirmation: "12345678"
                }) {
                    status
                }
            }',
        ]);
        $responseBody = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('register', $responseBody['data']);
        $this->assertArrayHasKey('status', $responseBody['data']['register']);
        $this->assertEquals('MUST_VERIFY_EMAIL', $responseBody['data']['register']['status']);
        $user = UserVerifyEmail::first();
        Notification::assertSentTo(
            [$user],
            VerifyEmail::class
        );
        Event::assertDispatched(Registered::class);
    }
}
