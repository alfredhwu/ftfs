<?php

namespace FTFS\DashboardBundle\Controller;

use FTFS\CrudBundle\Controller\CrudController as BaseController;
use JMS\SecurityExtraBundle\Annotation\Secure;

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

    /**
     *
     * for indexAction
     * indexAction() is reserved only for ROLE_AGENT & ROLE_CLIENT
     *
     * in this function, we add the filter so that
     *
     * client can only see his or his group's Service Requests;
     * agent can see all Service Requests
     *
     */
    protected function getEntityList()
    {
        $context = $this->get('security.context');
        $current_user = $context->getToken()->getUserName();

        $queryBuilder = $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())
                ->createQueryBuilder('e')
                ->add('orderBy', 'e.status asc, e.severity asc, e.last_modified_at desc');

        /** 
         * channel for client, filter by 'status'
         * 
         * will cover the role_agent, that is to say, if a user is both granted as role_client 
         * and role_agent, or any other sup role, only get priviledge of role_client
         *
         * client can only access his own or his group's requests
         *
         */
        if($context->isGranted('ROLE_CLIENT')){
            /**
             * at the moment, group is not available 
             * ToDo: Add the group share function in the future ********************************** ToDo
             */
            $queryBuilder
                ->where('e.requested_by = :requested_by')
                ->setParameter('requested_by', $current_user);
            
            // filter by 'status', if required
            $status = $this->getRequest()->query->get('status');
            if($status)
            {
                $queryBuilder
                    ->andWhere('e.status = :status')
                    ->setParameter('status', $status);
            }
            return $queryBuilder->getQuery()->getResult();
        }        

        /** 
         *
         * channel for agent, filter by 'filter'
         * 
         */
        if($context->isGranted('ROLE_AGENT'))
        {
            $filter = $this->getRequest()->query->get('filter');
            switch($filter)
            {
                case 'urgent':      // all new urgent requests not taken
                    $queryBuilder->andWhere('e.severity <300');
                case 'new':         // all new requests not taken
                    $queryBuilder->andWhere("e.status = '30_awaiting'");
                    $queryBuilder->andWhere('e.assigned_to is null');
                case 'all':         // all requests in the system
                    break;

                case 'newassigned': // all new requests assgined to me 
                    $queryBuilder->andWhere("e.status = '30_awaiting'");
                default:            // assigned, all requests assgined to me
                    $queryBuilder->andWhere('e.assigned_to = :agent')
                                 ->setParameter('agent', $current_user);
            }
            return $queryBuilder->getQuery()->getResult();
        }
        // throw exception if neither ROLE_CLIENT nor ROLE_AGENT
        throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Normally you have to be granted as client or agent in order to enter this controller : MyServiceRequestController ! You see this error page because of an internal error or you tried to access a ressource without permission.');
    }

    /**
     *
     * for all other actions
     *
     * a resource is protected and only its owner can modify/remove it 
     * here according to diff action called, use diff strategy of protection
     *
     */
    protected function getEntity($action, $id = -1)
    {
        $context = $this->get('security.context');
        $current_user = $context->getToken()->getUserName();
        $entity = parent::getEntity($action, $id);

        switch($action)
        {
            // restricted to its owner ant his share list and of cause all agents ToDo: share group ToDo
            case 'show':
                if($context->isGranted('ROLE_AGENT'))
                {
                    // role_agent, bypass the protection
                    break;
                }else{
                    // maybe restricted for the share list in the future
                    // at the moment, if granted as role_client, senctioned for username ToDo
                    if($entity->getRequestedBy()!=$current_user)
                    {
                        throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The operation "'.$action.'" is reserved to its owner ant its owner\'s share list !');
                    }
                }
                break;

            // restricted to its owner: requested_by
            case 'edit':
            case 'delete':
                if($entity->getStatus()!= '10_rejected')
                {
                    throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The operation "'.$action.'" cannot apply on a request rejected !');
                }
            case 'send':
                if($entity->getStatus()!= '20_unsent')
                {
                    throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The operation "'.$action.'" cannot apply on a request already send !');
                }
                if($entity->getRequestedBy()!=$current_user)
                {
                    throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The operation "'.$action.'" is reserved to its owner !');
                }
                break;      
            // restricted to its owner: assigned_to
            case 'transfer':
            case 'accept':
            case 'reject':
                if($entity->getAssignedTo()!=$current_user)
                {
                    throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The operation "'.$action.'" is reserved to its owner !');
                }
                break;      
            // no restriction
            // should be granted as ROLE_AGENT
            case 'take':
                if(!is_null($entity->getAssignedTo()))
                {
                    throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The service request "'.$entity->getName().'" has already been taken !');
                }
                if(!$context->isGranted('ROLE_AGENT'))
                {
                    throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The operation "'.$action.'" is reserved to ROLE_AGENT !');
                }
                break;
            // should be granted as ROLE_CLIENT
            case 'new':
                if(!$context->isGranted('ROLE_CLIENT'))
                {
                    throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The operation "'.$action.'" is reserved to ROLE_CLIENT !');
                }
            default:
        }
        return $entity;
    }


    /**
     * send a request to the ftfs support service
     * granted only to ROLE_CLIENT
     */
    public function sendAction($id)
    {
        $entity = $this->getEntity('send', $id);

        // check no sending a sent request
        if(!is_null($entity->getStatus()) && '20_unsent'!=$entity->getStatus())
        {
            throw new \Exception("Error: the request is alfready sent, you can only send a request with status 'unsent' or null!"); 
        }
        $this->postUpdateEntity($entity, null);
        $entity->setStatus("30_awaiting"); 
        $this->getDoctrine()->getEntityManager()->flush();
        return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_index'));
    }

    /**
     * take a new request
     * granted only to ROLE_AGENT
     */
    public function takeAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $this->getEntity('take', $id);
        
        $entity->setLastModifiedAt(new \DateTime('now'));
        $entity->setAssignedTo($this->get('security.context')->getToken()->getUser());
        $this->get('session')->setFlash('ftfs.crud.flash.success', $this->getRoutingPrefix().'.table.action.take.success.flash');
        $em->flush();
        return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array( 
            'id' => $entity->getId(),
        )));
    }

    /**
     * transfer a new request
     * granted only to ROLE_AGENT
     *
     * reserved to owner
     */
    public function transferAction($id)
    {
        return new \Exception('not available yet');
    }

    /**
     * accept a new request
     * granted only to ROLE_AGENT
     *
     * reserved to owner
     */
    public function acceptAction($id)
    {
        return new \Exception('not available yet');
    }

    /**
     * reject a new request
     * granted only to ROLE_AGENT
     *
     * reserved to owner
     */
    public function rejectAction($id)
    {
        return new \Exception('not available yet');
    }
}
