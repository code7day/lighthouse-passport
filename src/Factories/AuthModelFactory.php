<?php

namespace Code7day\LighthousePassportLogin\Factories;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Model;

class AuthModelFactory implements \Code7day\LighthousePassportLogin\Contracts\AuthModelFactory
{
    /**
     * @var Repository
     */
    private $config;

    public function __construct(
        Repository $config
    ) {
        $this->config = $config;
    }

    public function make(array $attributes = []): Model
    {
        $class = $this->getClass();

        return new $class($attributes);
    }

    public function create(array $attributes = []): Model
    {
        $model = $this->make($attributes);
        $model->save();

        return $model;
    }

    public function getClass(): string
    {
        return $this->config->get('auth.providers.users.model');
    }
}
