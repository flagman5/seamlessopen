<?php

//need to use the FB SDK

$request = new FacebookRequest(
  $session,
  'GET',
  '/search',
  array(
    'q' => $query,
    'type' => 'page'
  )
);

$response = $request->execute();
$graphObject = $response->getGraphObject();
/* handle the result */
