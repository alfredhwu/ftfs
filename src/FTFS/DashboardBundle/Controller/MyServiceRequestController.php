<?php

namespace FTFS\DashboardBundle\Controller;

use FTFS\CrudBundle\Controller\CrudController as BaseController;

class MyServiceRequestController extends BaseController
{
    public function __construct()
    {
        parent::__construct(array(
            'model' => "FTFS/ServiceBundle/ServiceRequest",
            'controller' => "FTFS/DashboardBundle/MyServiceRequest",
        ));
    }

    protected function postInitEntity($entity, $request)
    {
        $entity->setLastModifiedAt(new \DateTime('now'));

        $entity->setRequestedBy($this->get('security.context')->getToken()->getUser());
        $session = $this->get('session');

        $init_mode = $request->get('init_mode');
        if('send'==$init_mode)
        {
            $entity->setRequestedAt(new \DateTime('now'));
            $entity->setStatus('30_awaiting');
            $session->setFlash('ftfs.crud.flash.success', $this->getRoutingPrefix().'.form.action.new.save.send.success.flash');
        }else{
            $entity->setStatus('20_unsent');
            $session->setFlash('ftfs.crud.flash.success', $this->getRoutingPrefix().'.form.action.new.save.nosend.success.flash');
        }
    }

    protected function postUpdateEntity($entity, $request)
    {
        $entity->setLastModifiedAt(new \DateTime('now'));
        $session = $this->get('session');
        
        $update_mode = $request ? $request->get('update_mode') : null;
        if('nosend'==$update_mode)
        {
            $session->setFlash('ftfs.crud.flash.success', $this->getRoutingPrefix().'.form.action.edit.update.nosend.success.flash');
        }else{
            if(!$entity->getRequestedAt())
            {
                $entity->setRequestedAt(new \DateTime('now'));
            }
            $entity->setStatus('30_awaiting');
            $session->setFlash('ftfs.crud.flash.success', $this->getRoutingPrefix().'.form.action.edit.update.send.success.flash');
        }
    }

    protected function getEntityType(array $options)
    {
        $entityTypeClass = '\FTFS\DashboardBundle\Form\\'.$this->namespaces['model']['entity'].'Type';
        return new $entityTypeClass($options);
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
                            'severity' => 'asc',
                            'last_modified_at' => 'desc',
                        )
                    );
                    break;
                case 'urgent':
                    // new arriving urgent requests
                    $er = $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath());
                    return $er->createQueryBuilder('e')
                                ->where('e.severity < 300')
                                ->andWhere("e.status = '30_awaiting'")
                                ->andWhere("e.assigned_to is NULL")
                                ->orderBy('e.severity', 'asc')
                                ->orderBy('e.requested_at', 'desc')
                                ->getQuery()->getResult();
                    break;
                case 'newassigned':
                    // all new requests assigned to me
                    return $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())->findBy(
                        array(
                            'status' => '30_awaiting',
                            'assigned_to' => $this->get('security.context')->getToken()->getUser(),
                        ),
                        array(
                            'status' => 'asc',
                            'severity' => 'asc',
                            'requested_at' => 'desc',
                        )
                    );
                    break;
                case 'all':
                    // all requests not yet assigned
                    return $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())->findBy(
                        array(
                        ),
                        array(
                            'status' => 'asc',
                            'severity' => 'asc',
                            'last_modified_at' => 'desc',
                        )
                    );
                    break;
                default:
                    // all requests assigned to me
                    return $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())->findBy(
                        array(
                            'assigned_to' => $this->get('security.context')->getToken()->getUser(),
                        ),
                        array(
                            'status' => 'asc',
                            'severity' => 'asc',
                            'requested_at' => 'desc',
                        )
                    );
            }
        }elseif($context->isGranted('ROLE_CLIENT')){
            $status = $this->getRequest()->query->get('status');
            if($status)
            {
                return $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())->findBy(
                    array(
                        'requested_by' => $this->get('security.context')->getToken()->getUser(),
                        'status' => $status,
                    ),
                    array(
                        'status' => 'asc',
                        'severity' => 'asc',
                        'last_modified_at' => 'desc',
                    )
                );
            }
            // all my requests
            return $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())->findBy(
                array(
                    'requested_by' => $this->get('security.context')->getToken()->getUser(),
                ),
                array(
                    'status' => 'asc',
                    'severity' => 'asc',
                    'last_modified_at' => 'desc',
                )
            );
        }        
        throw new \Exception('You don\'t have the right to see this page !');
    }

    public function sendAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository($this->getEntityPath())->find($id);

        if(!$entity)
        {
            throw $this->createNotFoundException('Entity not found !');        
        }
        
        // check no sending a sent request
        if(is_null($entity->getStatus()) || '20_unsent'==$entity->getStatus())
        {
            $this->postUpdateEntity($entity, null);
            $entity->setStatus("30_awaiting"); 
            $em->flush();
            return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_index'));
        }else{
            throw new \Exception("Error: the request is alfready sent, you can only send a request with status 'unsent' or null!"); 
        }
    }

    public function takeAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository($this->getEntityPath())->find($id);

        if(!$entity)
        {
            throw $this->createNotFoundException('Entity not found !');        
        }
        
        // check not already taken
        if(is_null($entity->getAssignedTo()) && "30_awaiting"==$entity->getStatus())
        {
            $entity->setLastModifiedAt(new \DateTime('now'));
            $entity->setAssignedTo($this->get('security.context')->getToken()->getUser());
            $this->get('session')->setFlash('ftfs.crud.flash.success', $this->getRoutingPrefix().'.table.action.take.success.flash');
            $em->flush();
            return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array( 
                'id' => $entity->getId(),
            )));
        }
        throw new \Exception("Error: you cannot take this request, either because it has already been taken, or it is not sent by the client");
    }
}
