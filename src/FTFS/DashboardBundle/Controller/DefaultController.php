<?php

namespace FTFS\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction()
    {
        $request = $this->get('request');
        $request->getSession()->set('navbar',$request->getRequestUri());
        if($this->get('security.context')->isGranted('ROLE_CLIENT') ||
            $this->get('security.context')->isGranted('ROLE_AGENT'))
        {
        //    return $this->render('FTFSDashboardBundle:Default:dashboard.html.twig');
            return $this->redirect($this->generateUrl('ftfs_dashboardbundle_myservice_index'));
        }
        return $this->render('FTFSDashboardBundle:Default:index.html.twig');
    }
}
