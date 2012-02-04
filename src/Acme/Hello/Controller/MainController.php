<?php

namespace Acme\Hello\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MainController extends Controller
{
    public function index(Request $request, $name)
    {
        $name = $this->get('app.name_wrapper')->wrap($name);

        return array('name' => $name);
    }

    public function form(Request $request)
    {
        $form = $this->createForm(new \Acme\Hello\Form\Type\ExampleType());

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);
            $form->isValid();
        }

        return array('form' => $form->createView());
    }
}
