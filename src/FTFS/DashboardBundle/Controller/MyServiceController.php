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

    /**
     * post init entity while construction
     */
    protected function postInitEntity($entity, $request){
        // ?? ticket without request ??
        $entity->setStatus('10_opened');
        $entity->setAssignedTo($this->get('security.context')->getToken()->getUser());
        $entity->setLastModifiedAt(new \DateTime('now'));
        $entity->setRequestReceivedAt(new \DateTime('now'));
        $entity->setOpenedAt(new \DateTime('now'));
        $this->get('session')->setFlash('ftfs.crud.flash.success', 'ftfs.crud.flash.created.sucess'); 
    }

    /**
     * get entity list for index Action
     */
    protected function getEntityList()
    {
        $context = $this->get('security.context');

        if($context->isGranted('ROLE_CLIENT'))
        {
            $status = $this->getRequest()->query->get('status');
            if($status)
            {
                return $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())->findBy(
                    array(
                        'requested_by' => $this->get('security.context')->getToken()->getUser(),
                        //'assigned_to' => $this->get('security.context')->getToken()->getUser(),
                        'status' => $status,
                    ),
                    array(
                        'status' => 'asc',
                        'priority' => 'asc',
                        'severity' => 'asc',
                        'last_modified_at' => 'desc',
                    )
                );
            }
            // all my requests
            return $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())->findBy(
                array(
                    'requested_by' => $this->get('security.context')->getToken()->getUser(),
                    //'assigned_to' => $this->get('security.context')->getToken()->getUser(),
                ),
                array(
                    'status' => 'asc',
                    'priority' => 'asc',
                    'severity' => 'asc',
                    'last_modified_at' => 'desc',
                )
            );
        }
        if($context->isGranted('ROLE_AGENT')){
            $status = $this->getRequest()->query->get('status');
            if('all'==$status)
            {
                return $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())->findBy(
                    array(),
                    array(
                        'status' => 'asc',
                        'priority' => 'asc',
                        'severity' => 'asc',
                        'last_modified_at' => 'desc',
                    )
                );
            }
            if($status)
            {
                return $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())->findBy(
                    array(
                       // 'requested_by' => $this->get('security.context')->getToken()->getUser(),
                        'assigned_to' => $this->get('security.context')->getToken()->getUser(),
                        'status' => $status,
                    ),
                    array(
                        'status' => 'asc',
                        'priority' => 'asc',
                        'severity' => 'asc',
                        'last_modified_at' => 'desc',
                    )
                );
            }
            // all my requests
            return $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())->findBy(
                array(
                   // 'requested_by' => $this->get('security.context')->getToken()->getUser(),
                    'assigned_to' => $this->get('security.context')->getToken()->getUser(),
                ),
                array(
                    'status' => 'asc',
                    'priority' => 'asc',
                    'severity' => 'asc',
                    'last_modified_at' => 'desc',
                )
            );
        }        
        // return null if not authorized
        return null;
    }
}
