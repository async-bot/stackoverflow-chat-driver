<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Authentication\Exception;

class CaptchaRequired extends Authentication
{
    public function __construct()
    {
        parent::__construct('The StackOverflow authentication requested a captcha');
    }
}
