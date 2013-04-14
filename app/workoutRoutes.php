<?php

$workoutRoutes = $app['controllers_factory'];

$workoutRoutes->get('/new', function() use ($app) {
  $date = $app['request']->get('date');
  $date = new DateTime($date);

  $db = $app['mongo'];
  // $workoutCol = $db->selectCollection('workouts');
  $userCol = $db->selectCollection('users');
  $users = $userCol->find();
  return $app->render('workout_new.twig', array(
    'users' => iterator_to_array($users),
    'date' => $date
  ));
});

$workoutRoutes->post('/new', function() use($app) {
  $db = $app['mongo'];
  $workoutCol = $db->selectCollection('workouts');
  $userCol = $db->selectCollection('users');



  $workoutCol->insert(array(
    'users' => array(
    ),
    'date' => $date,
    'description' => $description
  ))
});

return $workoutRoutes;