<?php

namespace FTFS\StatisticsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    public function indexAction()
    {
        $current_user = $this->get('security.context')->getToken()->getUser();
        $observer = $this->get('ftfs_statisticsbundle.performance_observer');
        $statistics = array();
        foreach($observer->getStatistics($current_user) as $service => $stat) {
            if(array_key_exists('all_client_company_statistics', $stat)){
                $statistics[$service] = $stat['all_client_company_statistics'];
            }
        };
        //throw new \Exception(print_r($statistics));
        return $this->render('FTFSStatisticsBundle:Default:index.html.twig', array(
            'statistics' => $statistics,
        ));
    }
}
