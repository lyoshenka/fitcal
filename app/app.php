<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MyApp extends Silex\Application
{
  use Silex\Application\UrlGeneratorTrait;
  use Silex\Application\TwigTrait;
}

$app = new MyApp();
$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
  'twig.path' => __DIR__.'/../views',
));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
// $app->register(new Silex\Provider\SymfonyBridgesServiceProvider(), array(
//   'symfony_bridges.class_path'  => __DIR__.'/vendor/symfony/src',
// ));
// $app->register(new Silex\Provider\SessionServiceProvider(), array(
//   'session.storage.save_path' => __DIR__.'/tmp'
// ));

$app['mongo'] = $app->share(function() {
  $m = new MongoClient();
  return $m->selectDB('fitcal');
});

$app->mount('/u', include 'userRoutes.php');
$app->mount('/w', include 'workoutRoutes.php');