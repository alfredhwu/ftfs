<?php

namespace FTFS\DashboardBundle\Controller;

use FTFS\CrudBundle\Controller\CrudController as BaseController;

class MyServiceController extends BaseController
{
    public function __construct()
    {
        parent::__construct(array(
            'model' => "FTFS/ServiceBundle/ServiceTicket",
            'controller' => "FTFS/DashboardBundle/MyService",
        ));
    }

    /**
     * get entity list for index Action
     */
    protected function getEntityList()
    {
        $context = $this->get('security.context');
        $current_user = $context->getToken()->getUser();
        // status filter
        $status = $this->getRequest()->query->get('status');

        // general ordering
        $queryBuilder = $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())
                ->createQueryBuilder('e');

        /** 
         * role_client covers the role_agent, that is to say, if a user is both granted as role_client 
         * and role_agent, or any other sup role, only get priviledge of role_client
         *
         */
        if($context->isGranted('ROLE_CLIENT'))
        {
            /**
             * in case of an client user, only his/her own records or shared records is visible
             */
            $queryBuilder
                ->andWhere('e.requested_by = :requested_by')
                ->setParameter('requested_by', $current_user)
                ->add('orderBy', 'e.status asc, e.severity asc, e.last_modified_at desc');

        }elseif($context->isGranted('ROLE_AGENT')){
            $queryBuilder
                ->add('orderBy', 'e.status asc, e.priority asc, e.severity asc, e.last_modified_at desc');
            // additional channel for agent
            // all services     'status' = 'alldeployed'
            // all new requests 'status' = 'allunassigned'
            if($status == 'alldeployed')
            {
                $queryBuilder
                    ->andWhere("e.status <> 'created'");
                return $queryBuilder->getQuery()->getResult();
            }elseif($status == 'allunassigned'){
                $queryBuilder
                    ->andWhere("e.status = 'submitted'");
                return $queryBuilder->getQuery()->getResult();
            }
            // default, filtered by assignedTo
            $queryBuilder
                ->andWhere('e.assigned_to = :assigned_to')
                ->setParameter('assigned_to', $current_user);
        }else{
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Normally you have to be granted as client or agent in order to enter this controller : MyServiceController:list ! You see this error page because of an internal error or you tried to access a ressource without permission.');
        }
        
        switch($status)
        {
            //case 'submitted':
            case 'assigned':
            case 'created':
            case 'interrupted':
            case 'opened':
            case 'closed':
                $queryBuilder
                    ->andWhere('e.status = :status')
                    ->setParameter('status', $status);
                break;
            case 'awaiting': // both submitted(=unassigned) and assigned
                $queryBuilder
                    ->andWhere("e.status = 'submitted' or e.status = 'assigned'");
                break;
            case 'all':
                break;
            default:        // by default if no filter is set, return no closed
                $queryBuilder
                    ->andWhere("e.status <> 'closed'");
        }
        
        return $queryBuilder->getQuery()->getResult();
    }

    protected function getEntity($action, $id = -1)
    {
        $context = $this->get('security.context');
        $current_user = $context->getToken()->getUser();
        $entity = parent::getEntity($action, $id);

        switch($action)
        {
            // no restriction
            case 'new':
                break;
            // restricted to its owner (assigned to) ant his share list and of cause all agents 
            // ToDo: share group ToDo ########################################################
            case 'show':
            case 'edit':
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
            // status: 
            case 'take':
                if(!$context->isGranted('ROLE_AGENT'))
                {
                    throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The operation "take" of entity '.$entity->getName().'" is reserved for ROLE_AGENT !');
                }else{
                    if($entity->getAssignedTo()==$current_user)
                    {
                        throw new \Exception('Your have already taken this service ticket !');
                    }elseif($entity->getStatus()=='created' || $entity->getStatus()=='closed'){
                        throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The service request "'.$entity->getName().'" has already been taken !');
                    }
                }
            case 'transfer':
            // interdit
            case 'delete':
                if($entity->getStatus()!='created' or $entity->getRequestedBy()!=$current_user)
                {
                    throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The operation "delete" is only possible for the ticket with status "created" and reserved to its creator ! ');
                }
                break;
            default:
                throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Unknow operation !');
        }
        return $entity;
    }

    protected function flushEntity($entity, $action, $request=null)
    {
        // get context info
        $current_user = $this->get('security.context')->getToken()->getUser();
        // get option
        if($request)
        {
            $mode = $request->get('mode');
            $role = $request->get('role');
        }
        // pre flush
        $entity->setLastModifiedAt(new \DateTime('now'));
        switch($action)
        {
            case 'new':
                if($role == 'client')
                {
                    $entity->setName('randomed service ticket no.');
                    $entity->setPriority($entity->getSeverity());
                    $entity->setStatus('created');
                    $entity->setRequestedVia('web');
                    $entity->setCreatedAt(new \DateTime('now'));
                    if($mode == 'new_submit')
                    {
                        $entity->setStatus('submitted');
                        $entity->setRequestedAt(new \DateTime('now'));
                    }
                }elseif($role == 'agent'){
                    $entity->setName('randomed service ticket no.');
                    $entity->setStatus('opened');
                    $entity->setCreatedAt(new \DateTime('now'));
                    $entity->setOpenedAt(new \DateTime('now'));
                }
                break;
            case 'edit':
                if($role == 'client')
                {
                //    $entity->setName('randomed service ticket no.');
                //    $entity->setPriority($entity->getSeverity());
                //    $entity->setStatus('created');
                //    $entity->setRequestedVia('web');
                //    $entity->setCreatedAt(new \DateTime('now'));
                    $status = $entity->getStatus();
                    if($mode == 'edit_submit' and ('created' == $status or 'interrupted' ==$status))
                    {
                        $entity->setStatus('submitted');
                        $entity->setRequestedAt(new \DateTime('now'));
                    }
                }elseif($role == 'agent'){
                throw new \Exception('not available yet');
                    $entity->setName('randomed service ticket no.');
                    $entity->setStatus('opened');
                    $entity->setCreatedAt(new \DateTime('now'));
                    $entity->setOpenedAt(new \DateTime('now'));
                }
                break;
            case 'take':
                if(is_null($entity->getAssignedTo()))
                {
                    $entity->setStatus('assigned');
                }
                $entity->setAssignedTo($current_user);
                break;
        }

        // flush options for new, edit, delete 
        parent::flushEntity($entity, $action, $request);
    }

    protected function initNewEntity($entity){
        $context = $this->get('security.context');
        
        // pre init new entity

        if($context->isGranted('ROLE_CLIENT'))
        {
            $entity->setRequestedBy($context->getToken()->getUser());
        }elseif($context->isGranted('ROLE_AGENT')){
            $entity->setAssignedTo($context->getToken()->getUser());
            $entity->setRequestedAt(new \DateTime('now'));
        }
    }

    /**
     * get an entity type object
     * namespace defined by 'model' entry in constructer
     * by default, \Vendor\Bundle\Form\EntityNameType
     */
    protected function getEntityType(array $options)
    {
        $context = $this->get('security.context');

        if($context->isGranted('ROLE_CLIENT'))
        {
            $options['role'] = 'client';
        }elseif($context->isGranted('ROLE_AGENT')){
            $options['role'] = 'agent';
        }

        return parent::getEntityType($options);
    }

    /**
     * transfer 
     * granted only to ROLE_AGENT
     *
     * reserved to owner
     */
    public function transferAction($id)
    {
        // either to a certain agent
        // or null, if to the admin
        throw new \Exception('not available yet');
        $entity = $this->getEntity('transfer', $id);
        $entity->setStatus('20_delivered');
        $this->getDoctrine()->getEntityManager()->flush();
        return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_index'));
    }

    /**
     * take 
     * granted only to ROLE_AGENT
     *
     * reserved to owner
     */
    public function takeAction($id)
    {
        $entity = $this->getEntity('take', $id);
        $this->flushEntity($entity, 'take');
        return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array('id' => $entity->getId())));
    }
}
