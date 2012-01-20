<?php

require_once __DIR__.'/../waste/bootstrap.php.cache';

use Symfony\Component\HttpFoundation\Request;

$kernel = new Knp\Bundle\RadBundle\AppKernel('prod', false);
$kernel->loadClassCache();
//$kernel = new Knp\Bundle\RadBundle\AppCache($kernel);
$kernel->handle(Request::createFromGlobals())->send();
