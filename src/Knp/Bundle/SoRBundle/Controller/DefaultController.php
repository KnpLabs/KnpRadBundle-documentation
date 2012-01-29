<?php

namespace Knp\Bundle\SoRBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{

    public function indexAction($name)
    {
        return $this->render('KnpSoRBundle:Default:index.html.twig', array('name' => $name));
    }
}
