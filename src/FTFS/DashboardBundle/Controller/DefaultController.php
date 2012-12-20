<?php

namespace FTFS\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    public function indexAction()
    {
        $locale = $this->get('ftfs_configurator')->get('locale', $this->get('security.context')->getToken()->getUser());
        if($locale && count($locale)>0) {
            $locale = $locale[0];
        }else{
            $locale = 'en_US';
        }
        if($this->get('security.context')->isGranted('ROLE_CLIENT') ||
            $this->get('security.context')->isGranted('ROLE_AGENT')) {
        //    return $this->render('FTFSDashboardBundle:Default:dashboard.html.twig');
                return $this->redirect($this->generateUrl('ftfs_dashboardbundle_myservice_index', array(
                    '_locale' => $locale,
                )));
        }
        return $this->render('FTFSDashboardBundle:Default:index.html.twig');
    }
}
