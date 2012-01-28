<?php

require_once __DIR__.'/../app/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Knp\Bundle\RadBundle\HttpKernel\RadKernel;

$loader = require(__DIR__.'/../vendor/.composer/autoload.php');
RadKernel::autoload($loader, __DIR__);

$kernel = new RadKernel('prod', false);
$kernel->loadClassCache();
//$kernel = new Knp\Bundle\RadBundle\AppCache($kernel);
$kernel->handle(Request::createFromGlobals())->send();
