<?php

$userRoutes = $app['controllers_factory'];

$userRoutes->get('/', function() use ($app) {
    $db = $app['mongo'];
    $userCol = $db->selectCollection('users');
    $users = $userCol->find();
    return $app->render('user_list.twig', array(
      'users' => iterator_to_array($users)
    ));
})->bind('user_list');

$userRoutes->get('/new', function() use ($app) {
    return $app->render('user_new.twig');
});

$userRoutes->post('/new', function() use ($app) {
  $db = $app['mongo'];
  $userCol = $db->selectCollection('users');
  $name = $app['request']->get('name');

  if ($userCol->count(array('name' => $name)))
  {
    return "User with name $name already exists.";
  }

  $userCol->insert(array('name' => $name));
  return $app->redirect($app->path('user_list'));
});

$userRoutes->get('/{id}', function() use ($app) {
  $db = $app['mongo'];
  $user = $db->selectCollection('users')->findOne(array(
    '_id' => new MongoId($app['request']->get('id'))
  ));

  if (!$user)
  {
    return 'User not found';
  }

  return $app->render('user_show.twig', array(
    'user' => $user
  ));
})->bind('user_show');

return $userRoutes;