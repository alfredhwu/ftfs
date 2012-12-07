<?php

namespace FTFS\StatisticsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        return $this->render('FTFSStatisticsBundle:Default:index.html.twig', array('name' => $name));
    }
}
