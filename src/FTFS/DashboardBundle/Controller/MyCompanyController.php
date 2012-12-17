<?php

namespace FTFS\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class MyCompanyController extends Controller
{
    public function statisticsIndexAction()
    {
        $performance = $this->get('ftfs_statisticsbundle.performance_observer');

        $current_user = $this->get('security.context')->getToken()->getUser();
        $stats = $performance->getStatistics($current_user);

        $em = $this->getDoctrine()->getEntityManager();
        $services = $em->getRepository('FTFSServiceBundle:Service')->findBy(
                array(
                    'open_to_client' => true,
                )
            );


        $statistics = array();
        foreach($services as $service) {
            $name = $service->getName();
            if(array_key_exists($name, $stats) && array_key_exists('my_company_statistics', $stats[$name])) {
                $statistics[$name] = $stats[$name]['my_company_statistics'];
            }
        }

        // 
        $er = $em->getRepository('FTFSServiceBundle:ServiceTicket');
        $number_all = count($er->findAll());
        $number_created = count($er->findByStatus('created'));
        $number_submitted = count($er->findByStatus('submitted'));
        $number_opened = count($er->findByStatus('opened'));
        $number_closed = count($er->findByStatus('closed'));

        //throw new \Exception(print_r($statistics));
        return $this->render('FTFSDashboardBundle:MyCompany:statistics_index.html.twig', array(
            'general' => array(
                'all_created' => $number_all,
                'all_submitted' => $number_all-$number_created,
                'all_opened' => $number_opened+$number_closed,
                'all_closed' => $number_closed,
            ),
            'statistics' => $statistics,
        ));
    }

    public function staffListAction()
    {
        $context = $this->get('security.context');
        $current_user = $context->getToken()->getUser();
        if(! $context->isGranted('ROLE_CLIENT_COMPANY')) {
            throw new \Exception('You don\'t have the right to access this page ! Please contact your system manager !');
        }
        $company = $current_user->getCompany();
        $staff = $this->getDoctrine()->getEntityManager()
            ->getRepository('FTFSUserBundle:User')->findBy(
                array(
                    'company' => $company->getId(),
                ),
                array(
                    'surname' => 'asc',
                    'first_name' => 'asc', 
                )
            );
        return $this->render('FTFSDashboardBundle:MyCompany:staff_list.html.twig', array(
            'staff' => $staff,
        ));
    }
}
