<?php

use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Middleware\Base;

require_once __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$errorHandler = function (
    Request $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails

) use ($app) {

    $response = $app->getResponseFactory()->createResponse();
    if($exception instanceof PDOException) return $response->withStatus(500);

    $message = ['error' => $exception->getMessage()];
    $response->getBody()->write(json_encode($message));
    return $response->withStatus($exception->getCode());
};

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

$app->add(Base::class);

$app->post('/balance', 'App\Models\Api:getBalance');
$app->post('/refill', 'App\Models\Api:refill');
$app->post('/reserve', 'App\Models\Api:reserve');
$app->post('/transfer', 'App\Models\Api:transfer');
$app->post('/report', 'App\Models\Api:getReport');
$app->post('/history', 'App\Models\Api:getHistory');

$app->run();