<?php

namespace App\Models;

use Psr\Http\Message\RequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use PDO;

class Pagination
{
    protected $page;
    public $start = 0;
    public $perPage = 2;
    protected $pages;

    public function __construct(Request $request)
    {
        $json = $request->getParsedBody();

        $this->page = $this->getPage($request);
        $this->pages = $this->countPages($json['user_id']);
        if($this->page > $this->pages) throw new HttpNotFoundException($request);
        $this->start = $this->perPage * ($this->page - 1);
    }

    protected function getPage($request): int
    {
        $page = 1;
        if(key_exists('page', $request->getQueryParams())) {
            $page = $request->getQueryParams()['page'];
            if(empty($page) || $page < 1) throw new HttpBadRequestException($request);
        }
        return $page;
    }

    protected function countPages($user_id): int
    {
        $pdo = new Database();
        $dbh = $pdo->connect();

        $query = "SELECT COUNT(1) as pages FROM transactions WHERE user_id = ?";
        $stmt = $dbh->prepare($query);
        $stmt->execute([$user_id]);

        return ceil($stmt->fetch(PDO::FETCH_ASSOC)['pages'] / $this->perPage);
    }

    public function getLinks(): array
    {
        if($this->page == 1) {
            if($this->pages > 1) return ["next" => "/history?page=" . ($this->page + 1)];
            return [];
        }
        if($this->page == $this->pages) {
            return ["prev" => "/history?page=" . ($this->page - 1)];
        }
        return [
            "prev" => "/history?page=" . ($this->page - 1),
            "next" => "/history?page=" . ($this->page + 1)
        ];
    }
}