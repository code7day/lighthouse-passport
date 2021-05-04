<?php

namespace Code7day\LighthousePassportLogin;

use Code7day\LighthousePassportLogin\Notifications\VerifyEmail;

trait MustVerifyEmailGraphQL
{
    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail());
    }
}
