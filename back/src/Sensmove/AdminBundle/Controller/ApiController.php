<?php

namespace Sensmove\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;

class ApiController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return new Response('Test !');
    }

    /**
     * @Route("/points")
     * 
     */
    public function addPointsAction()
    {
        return new Response('Here are the points');
    }


    /**
     * @Route("/points")
     *
     */
    public function getPointsAction()
    {
        return new Response('Here are the points GET');
    }

}
