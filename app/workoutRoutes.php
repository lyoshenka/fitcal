<?php

$workoutRoutes = $app['controllers_factory'];

$workoutRoutes->get('/new', function() use ($app) {
  $date = $app['request']->get('date');
  $date = new DateTime($date);

  $db = $app['mongo'];
  $users = $db->selectCollection('users')->find();
  return $app->render('workout_new.twig', array(
    'users' => iterator_to_array($users),
    'date' => $date
  ));
});

$workoutRoutes->post('/new', function() use($app) {
  $db = $app['mongo'];
  $userCol = $db->selectCollection('users');

  $userIds = $app['request']->get('users');
  if  (!$userIds)
  {
    return 'You must select at least one user';
  }

  try
  {
    $date = new DateTime($app['request']->get('date'));
  }
  catch (\Exception $e)
  {
    return 'Could not parse date.';
  }


  $userIdQuery = array();
  foreach ($userIds as $id)
  {
    $userIdQuery[] = new MongoId($id);
  }
  $users = $userCol->find(array('_id' => array('$in' => $userIdQuery)));

  if ($users->count() < count($userIds))
  {
    return 'Some users could not be found.';
  }



  $db->selectCollection('workouts')->insert(array(
    'users' => $app->pluck(iterator_to_array($users), '_id'),
    'date' => $date,
    'description' => $app['request']->get('description')
  ));

  $app->setFlash('success', 'Workout created.');

  return $app->redirect($app->path('calendar', array(
    'year' => $date->format('Y'), 'month' => $date->format('m')
  )));
});

$workoutRoutes->get('/{date}', function() use ($app) {
  try
  {
    $date = new DateTime($app['request']->get('date'));
  }
  catch (\Exception $e)
  {
    return 'Could not parse date.';
  }


  $db = $app['mongo'];
  $workouts = $db->selectCollection('workouts')->find(array(
    'date' => $date
  ));
  $workouts = iterator_to_array($workouts);

  foreach($workouts as &$workout)
  {
    $workout['users'] = $db->selectCollection('users')->find(array(
      '_id' => array('$in' => $workout['users'])
    ));
  }

  return $app->render('workout_list_date.twig', array(
    'workouts' => $workouts,
    'date' => $date
  ));
})->bind('workout_show');


return $workoutRoutes;