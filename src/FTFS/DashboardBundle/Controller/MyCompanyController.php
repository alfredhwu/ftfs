<?php

namespace FTFS\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class MyCompanyController extends Controller
{
    public function statisticsIndexAction()
    {
        return $this->render('FTFSDashboardBundle:MyCompany:statistics_index.html.twig', array(
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
