<?php

namespace FTFS\ServiceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        return $this->render('FTFSServiceBundle:Default:index.html.twig', array('name' => $name));
    }
}
