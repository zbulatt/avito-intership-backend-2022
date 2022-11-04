<?php

namespace App\Models;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use App\Exceptions\UserNotFoundException;
use Respect\Validation\Validator as v;
use PDO;

abstract class BaseApi
{
    protected PDO $dbh;

    public function __construct()
    {
        $pdo = new Database();
        $this->dbh = $pdo->connect();
    }

    protected function validate(Request $request) : array
    {
        $json = $request->getParsedBody();
        if(v::each(v::intType())->validate($json)
            && v::each(v::greaterThan(0))->validate($json)) return $json;
        throw new HttpBadRequestException($request);
    }

    protected function checkBalance(Request $request) : bool
    {
        $json = $request->getParsedBody();

        $pdo = new Database();
        $dbh = $pdo->connect();
        $query = "SELECT user_id, balance FROM user_balance WHERE user_id = :user_id";
        $stmt = $dbh->prepare($query);
        $stmt->bindParam('user_id', $json['user_id']);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if($result) {
            if($result['balance'] >= $json['amount']) return true;
            return false;
        }
        throw new UserNotFoundException($request);
    }
}