<?php

namespace app\base\sms\providers;

use app\traits\HasHttpRequest;

abstract class Provider
{
    use HasHttpRequest;

    public abstract function send($to, $message, $config);
}
