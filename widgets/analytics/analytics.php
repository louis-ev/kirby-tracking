<?php

return array(
  'title' => 'Kirby Tracking',
  // 'options' => array(
  //   array(
  //     'text' => 'Optional option',
  //     'icon' => 'pencil',
  //     'link' => 'link/to/option'
  //   )
  // ),
  'html' => function() {
    return tpl::load(__DIR__ . DS . 'templates' . DS . 'user-counter.php');
  }
);
