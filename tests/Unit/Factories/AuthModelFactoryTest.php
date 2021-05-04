<?php

namespace Code7day\LighthousePassportLogin\Tests\Unit\Factories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Code7day\LighthousePassportLogin\Contracts\AuthModelFactory;
use Code7day\LighthousePassportLogin\Tests\TestCase;
use Code7day\LighthousePassportLogin\Tests\User;

class AuthModelFactoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var AuthModelFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = $this->app->make(AuthModelFactory::class);
    }

    /**
     * @test
     */
    public function canMakeAuthModel(): void
    {
        $email = 'user@demo.com';
        $model = $this->factory->make(['email' => $email]);

        self::assertInstanceOf(User::class, $model);
        self::assertEquals($email, $model->email);
    }

    /**
     * @test
     */
    public function canCreateAuthModel(): void
    {
        $model = $this->factory->create([
            'name'     => 'User Demo',
            'email'    => 'user@demo.com',
            'password' => Hash::make('12345678'),
        ]);

        self::assertInstanceOf(User::class, $model);
        self::assertEquals($model->count(), 1);
    }

    /**
     * @test
     */
    public function canGetAuthModelClass(): void
    {
        self::assertEquals($this->factory->getClass(), User::class);
    }
}
