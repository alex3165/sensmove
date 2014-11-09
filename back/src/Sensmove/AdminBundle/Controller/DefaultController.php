<?php

namespace Sensmove\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('SensmoveAdminBundle:Default:index.html.twig', array('name' => $name));
    }
}
