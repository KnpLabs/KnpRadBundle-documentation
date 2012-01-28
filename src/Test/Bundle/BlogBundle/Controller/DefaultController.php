<?php

namespace Test\Bundle\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function index($name)
    {
        $name = $this->get('blog.name_wrapper')->wrap($name);

        return array('name' => $name);
    }
}
