<?php

namespace Netgen\EzSyliusBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('NetgenEzSyliusBundle:Default:index.html.twig', array('name' => $name));
    }
}
