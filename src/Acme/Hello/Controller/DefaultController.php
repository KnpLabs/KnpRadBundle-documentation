<?php

namespace Acme\Hello\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function index($name)
    {
        $name = $this->get('hello.name_wrapper')->wrap($name);

        return array('name' => $name);
    }
}
