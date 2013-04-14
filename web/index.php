<?php

# Enable PHP dev cli-server
$filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

require_once __DIR__.'/../app/app.php';



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
        $dates[] = array(
            'long' => $date->format('Y-m-d'),
            'day' => $date->format('d'),
            'inMonth' => $startMonth == $date->format('m')
        );
        $date->modify('+1 day');
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

$app->run();