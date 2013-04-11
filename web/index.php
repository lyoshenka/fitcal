<?php

# Enable PHP dev cli-server
$filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

require_once __DIR__.'/../vendor/autoload.php';

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
        $dates[] = array(
            'long' => $date->format('Y-m-d'),
            'day' => $date->format('d'),
            'inMonth' => $startMonth == $date->format('m')
        );
        $date->modify('+1 day');
    }

    return $app->render('cal.twig.html', array(
        'startDate' => $startDate,
        'dates' => $dates,
        'prevYear' => $prevMonthDate->format('Y'),
        'prevMonth' => $prevMonthDate->format('m'),
        'nextYear' => $nextMonthDate->format('Y'),
        'nextMonth' => $nextMonthDate->format('m')
    ));
})
->bind('calendar')
->value('year', date('Y'))->value('month',date('m'));

$app->run();