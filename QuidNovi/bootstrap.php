<?php

use Slim\Slim;

// Create the application
$app = new Slim(array(
    'templates.path' => __DIR__.'/../web',
));

$app->config('debug', true);

// Error handling
$app->notFound(function () use ($app) {
    $app->render('404.html');
});

$app->error(function (\Exception $ex) use ($app) {
    $app->getLog()->alert($ex);
});

return $app;
