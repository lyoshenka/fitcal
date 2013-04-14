<?php

$workoutRoutes = $app['controllers_factory'];

$workoutRoutes->get('/new', function() use ($app) {
  $date = $app['request']->get('date');
  $date = new DateTime($date);

  $db = $app['mongo'];
  $userCol = $db->selectCollection('users');
  $users = $userCol->find();
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

  if (count($users) < count($usersIds))
  {
    return 'Some users could not be found.';
  }

  $db->selectCollection('workouts')->insert(array(
    'users' => $users,
    'date' => $date,
    'description' => $app['request']->get('description')
  ));

  return $app->redirect($app->path('calendar', array(
    'year' => $date->format('Y'), 'month' => $date->format('m')
  )));
});

return $workoutRoutes;