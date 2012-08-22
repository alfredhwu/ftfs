<?php

namespace FTFS\ServiceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class ServiceController extends Controller
{
    
    public function indexAction()
    {
        return $this->render('FTFSServiceBundle:Default:index.html.twig');
    }
}
