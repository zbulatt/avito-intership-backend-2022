<?php

namespace App\Exceptions;

use Slim\Exception\HttpSpecializedException;

class NotEnoughMoneyException extends HttpSpecializedException
{
    protected $code = 400;
    protected $message = "Not enough money";
}