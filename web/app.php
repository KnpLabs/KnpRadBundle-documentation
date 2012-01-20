<?php

require_once __DIR__.'/../waste/bootstrap.php.cache';

use Symfony\Component\HttpFoundation\Request;

$kernel = new Knp\Rad\AppKernel('prod', false);
$kernel->loadClassCache();
//$kernel = new AppCache($kernel);
$kernel->handle(Request::createFromGlobals())->send();
