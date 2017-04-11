<?php

kirby()->routes(array(
	array(
    'pattern' => array('logthisevent', '(:all)/logthisevent'),
    'action'  => function() {
      // visit default langage
  	  site()->visit('/');
      $sessionid = s::id();
      $data = $_POST;
      $data['IP'] = $_SERVER['REMOTE_ADDR'];
      log_event($sessionid, $data);
    },
    'method' => 'POST'
	)
));