<?php

namespace App\Exceptions;

use Slim\Exception\HttpSpecializedException;

class UserNotFoundException extends HttpSpecializedException
{
    protected $code = 404;
    protected $message = "User not found";
}