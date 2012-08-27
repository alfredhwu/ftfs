<?php

namespace FTFS\DashboardBundle\Controller;

use FTFS\CrudBundle\Controller\CrudController as BaseController;

class MyServiceController extends BaseController
{
    public function __construct()
    {
        parent::__construct(array(
            'model' => "FTFS/ServiceBundle/Service",
            'controller' => "FTFS/DashboardBundle/MyService",
        ));
    }

    protected function getEntityList()
    {
        $context = $this->get('security.context');
        if($context->isGranted('ROLE_AGENT'))
        {
            $filter = $this->getRequest()->query->get('filter');
            switch($filter)
            {
                case 'new':
                    // all new arriving requests not yet assigned
                    return $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())->findBy(
                        array(
                            'status' => '30_awaiting',
                            'assigned_to' => null,
                        ),
                        array(
                            'status' => 'asc',
                            'last_modified_at' => 'desc',
                        )
                    );
                    break;
            }
        }elseif($context->isGranted('ROLE_CLIENT')){
            $status = $this->getRequest()->query->get('status');
            if($status)
            {
                return $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())->findBy(
                    array(
                       // 'requested_by' => $this->get('security.context')->getToken()->getUser(),
                        'status' => $status,
                    ),
                    array(
                        'status' => 'asc',
                        'last_modified_at' => 'desc',
                    )
                );
            }
            // all my requests
            return $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())->findBy(
                array(
                   // 'requested_by' => $this->get('security.context')->getToken()->getUser(),
                ),
                array(
                    'status' => 'asc',
                    'last_modified_at' => 'desc',
                )
            );
        }        
        throw new \Exception('You don\'t have the right to see this page !');
    }
}
