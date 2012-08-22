<?php

namespace FTFS\ServiceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class ServiceRequestController extends Controller
{
    
    public function indexAction()
    {
        return $this->render('FTFSServiceBundle:ServiceRequest:index.html.twig');
    }
}
