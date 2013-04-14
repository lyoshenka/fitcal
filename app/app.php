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


$app->get('/w/{id}', function($id) use ($app) {
    $db = $app['mongo'];
    $workouts = $db->selectCollection('workouts')->find(array('name' => 'pop'));
    return var_export($workouts, true);
});

$app->get('/{year}/{month}', function($year, $month) use ($app) {
    $date = new DateTime($year . '-' . $month . '-01');
    $startDate = clone $date;
    $date->modify('-' . $startDate->format('w') . ' days');
    $endDate = clone $startDate;
    $endDate->modify('+' . $startDate->format('t') . ' days');
    $endDate->modify('+' . (6 - $endDate->format('w')) . ' days');
    $startMonth = $startDate->format('m');

    $prevMonthDate = clone $startDate;
    $prevMonthDate->modify('-1 month');
    $nextMonthDate = clone $startDate;
    $nextMonthDate->modify('+1 month');

    $dates = array();

    while ($date <= $endDate)
    {
      $dates[$date->format('Y-m-d')] = array(
        'long' => $date->format('Y-m-d'),
        'day' => $date->format('d'),
        'inMonth' => $startMonth == $date->format('m'),
        'workouts' => array()
      );
      $date->modify('+1 day');
    }

    $db = $app['mongo'];
    $workouts = $db->selectCollection('workouts')->find(array(
      'date' => array(
        '$gte' => new DateTime(min(array_keys($dates))),
        '$lte' => new DateTime(max(array_keys($dates)))
      )
    ));

    $workout = $workouts->getNext();
    while($workout)
    {
      $workout['date'] = new DateTime($workout['date']['date']);
      $dates[$workout['date']->format('Y-m-d')]['workouts'][] = $workout;
      $workout = $workouts->getNext();
    }

    return $app->render('cal.twig', array(
        'startDate' => $startDate,
        'dates' => $dates,
        'prevYear' => $prevMonthDate->format('Y'),
        'prevMonth' => $prevMonthDate->format('m'),
        'nextYear' => $nextMonthDate->format('Y'),
        'nextMonth' => $nextMonthDate->format('m')
    ));
})
->bind('calendar')
->assert('year', '\d+')->assert('month', '\d+')
->value('year', date('Y'))->value('month',date('m'));