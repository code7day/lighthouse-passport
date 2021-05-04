<?php

namespace Code7day\LighthousePassportLogin\Events;

/**
 * Class ForgotPasswordRequested.
 */
class ForgotPasswordRequested
{
    /**
     * @var string
     */
    public $email;

    /**
     * ForgotPasswordRequested constructor.
     *
     * @param string $email
     */
    public function __construct(string $email)
    {
        $this->email = $email;
    }
}
