<?php

namespace Acme\Hello\Frontend\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function index($name)
    {
        $name = $this->get('frontend.name_wrapper')->wrap($name);

        return array('name' => $name);
    }
}
