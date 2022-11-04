<?php

namespace App\Models;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Exceptions\NotEnoughMoneyException;
use App\Exceptions\UserNotFoundException;
use PDO;

class Api extends BaseApi
{
    public function getBalance(Request $request, Response $response)
    {
        $json = $this->validate($request);

        $stmt = $this->dbh->prepare("SELECT user_id, balance FROM user_balance WHERE user_id = ?");
        $stmt->execute([$json['user_id']]);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);

        if ($data) {
            $response->getBody()->write(json_encode($data));
            return $response;
        }

        throw new UserNotFoundException($request);
    }

    public function refill(Request $request, Response $response)
    {
        $json = $this->validate($request);

        try {
            $this->dbh->beginTransaction();

            $stmt = $this->dbh->prepare("UPDATE user_balance SET balance = balance + ? WHERE user_id = ?");
            $stmt->execute([$json['amount'], $json['user_id']]);

            if($stmt->rowCount() == 0) throw new UserNotFoundException($request);

            $query = "INSERT INTO transactions (user_id, service_id, amount, date) VALUES (?, ?, ?, NOW())";
            $stmt = $this->dbh->prepare($query);
            $stmt->execute([$json['user_id'], 0, $json['amount']]);

            $this->dbh->commit();

        } catch (\PDOException $e) {
            $this->dbh->rollBack();
            return $response->withStatus(500);
        }

        return $response;
    }

    public function reserve(Request $request, Response $response)
    {
        $json = $this->validate($request);

        if (!$this->checkBalance($request)) throw new NotEnoughMoneyException($request);

        try {
            $this->dbh->beginTransaction();

            $query = "UPDATE user_balance SET balance = balance - ? WHERE user_id = ?";
            $stmt = $this->dbh->prepare($query);
            $stmt->execute([$json['amount'], $json['user_id']]);

            $query = "INSERT INTO reserve (order_id, user_id, service_id, amount) VALUES (?, ?, ?, ?)";
            $stmt = $this->dbh->prepare($query);
            $stmt->execute([$json['order_id'], $json['user_id'], $json['service_id'], $json['amount']]);

            $query = "INSERT INTO transactions (user_id, service_id, amount, date) VALUES (?, ?, ?, NOW())";
            $stmt = $this->dbh->prepare($query);
            $stmt->execute([$json['user_id'], $json['service_id'], $json['amount']]);

            $this->dbh->commit();

        } catch (\PDOException $e) {
            $dbh->rollBack();
            return $response->withStatus(500);
        }

        return $response;
    }

    public function transfer(Request $request, Response $response)
    {
        $json = $this->validate($request);

        try {
            $this->dbh->beginTransaction();

            $query = "DELETE FROM reserve WHERE order_id = ? AND user_id = ? AND service_id = ? AND amount = ?";
            $stmt = $this->dbh->prepare($query);
            $stmt->execute([$json['order_id'], $json['user_id'], $json['service_id'], $json['amount']]);

            if ($stmt->rowCount() != 1) throw new \Exception();

            $query = "INSERT INTO history (user_id, service_id, amount, date) VALUES (?, ?, ?, NOW())";
            $stmt = $this->dbh->prepare($query);
            $stmt->execute([$json['user_id'], $json['service_id'], $json['amount']]);

            $this->dbh->commit();

        } catch (\Exception $e) {
            $this->dbh->rollBack();
            return $response->withStatus(500);
        }
        return $response;
    }

    public function getReport(Request $request, Response $response)
    {
        $json = $this->validate($request);

        $query = "SELECT s.title, SUM(amount) as profit
                  FROM history
                  LEFT JOIN service s on history.service_id = s.id
                  WHERE MONTH(date) = ? AND YEAR(date) = ?
                  GROUP BY history.service_id";
        $stmt = $this->dbh->prepare($query);
        $stmt->execute([$json['month'], $json['year']]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $f = fopen("../tmp/report_" . $json['month'] . "_" . $json['year'], 'w');
        foreach ($result as $line) {
            fputcsv($f, $line, ';');
        }
        fclose($f);

        return $response;
    }

    public function getHistory(Request $request, Response $response)
    {
        $json = $this->validate($request);

        $pagination = new Pagination($request);

        $query = "SELECT s.title, amount, date 
                  FROM transactions 
                  LEFT JOIN service s on s.id = transactions.service_id
                  WHERE user_id = ? 
                  ORDER BY date DESC 
                  LIMIT $pagination->start, $pagination->perPage";
        $stmt = $this->dbh->prepare($query);
        $stmt->execute([$json['user_id']]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [
            "data" => $result,
            "links" => $pagination->getLinks()
        ];

        $response->getBody()->write(json_encode($data));
        return $response;
    }
}