<?php

namespace FTFS\SystemBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        return $this->render('FTFSSystemBundle:Default:index.html.twig', array('name' => $name));
    }
}
