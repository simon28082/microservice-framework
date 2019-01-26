<?php

/* @var $dispatcher \CrCms\Microservice\Dispatching\Dispatcher */
$dispatcher = \CrCms\Microservice\Foundation\Application::getInstance()->make('caller');

$dispatcher->register('test',function(){
   return ['data' => ['z'=>1]];
});

