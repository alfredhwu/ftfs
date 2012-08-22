<?php

namespace FTFS\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction()
    {
        if($this->get('security.context')->isGranted('ROLE_CLIENT') | 
            $this->get('security.context')->isGranted('ROLE_AGENT'))
        {
            return $this->render('FTFSDashboardBundle:Default:dashboard.html.twig');
        }
        return $this->render('FTFSDashboardBundle:Default:index.html.twig');
    }
}
