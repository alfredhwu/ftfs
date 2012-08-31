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
     * get entity list for index Action
     */
    protected function getEntityList()
    {
        $context = $this->get('security.context');
        $current_user = $context->getToken()->getUserName();
        // status filter
        $status = $this->getRequest()->query->get('status');

        $queryBuilder = $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())
                ->createQueryBuilder('e')
                ->add('orderBy', 'e.status asc, e.priority asc, e.severity asc, e.last_modified_at desc');

        /** 
         * channel for client, filter by status
         * Read_only
         * maybe cancel oper in the future
         *
         * will cover the role_agent, that is to say, if a user is both granted as role_client 
         * and role_agent, or any other sup role, only get priviledge of role_client
         */
        if($context->isGranted('ROLE_CLIENT'))
        {
            /**
             * in case of an client user, only his/her own records or shared records is visible
             */
            $queryBuilder
                ->andWhere('e.requested_by = :requested_by')
                ->setParameter('requested_by', $current_user);

            switch($status)
            {
                case 'all':
                    break;
                case '10_opened':
                case '20_delivered':
                case '30_closed':
                    $queryBuilder
                        ->andWhere('e.status = :status')
                        ->setParameter('status', $status);
                    break;
                default:        // by default if no filter is set, return opened, delivered
                    $queryBuilder
                        ->add('orderBy', 
                                'e.status desc, e.priority asc, e.severity asc, e.last_modified_at desc')
                        ->andWhere("e.status = '10_opened' or e.status = '20_delivered'");
            }
            return $queryBuilder->getQuery()->getResult();
        }
        /** 
         * channel for agent, filter by filter
         * 
         */
        if($context->isGranted('ROLE_AGENT'))
        {
            switch($status)
            {
                case 'all':     // all services
                    break;
                case '10_opened':
                case '20_delivered':
                case '30_closed':
                    $queryBuilder
                        ->andWhere('e.status = :status')
                        ->setParameter('status', $status);
                case 'allassigned':
                    $queryBuilder
                        ->andWhere('e.assigned_to = :assigned_to')
                        ->setParameter('assigned_to', $current_user);
                    break;
                default:        // all my services active, opened & delivered
                    $queryBuilder
                        ->andWhere("e.status = '10_opened' or e.status = '20_delivered'")
                        ->andWhere('e.assigned_to = :assigned_to')
                        ->setParameter('assigned_to', $current_user);
            }
            return $queryBuilder->getQuery()->getResult();
        }        
        throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Normally you have to be granted as client or agent in order to enter this controller : MyServiceController:list ! You see this error page because of an internal error or you tried to access a ressource without permission.');
    }

    protected function getEntity($action, $id = -1)
    {
        $context = $this->get('security.context');
        $current_user = $context->getToken()->getUserName();
        $entity = parent::getEntity($action, $id);

        switch($action)
        {
            // restricted to its owner (assigned to) ant his share list and of cause all agents 
            // ToDo: share group ToDo ########################################################
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
            // restricted to its owner: assigned_to, status: 10_opened
            case 'take':
                if(!is_null($entity->getAssignedTo()))
                {
                    throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The service request "'.$entity->getName().'" has already been taken !');
                }
            case 'transfer':
            case 'edit':
            case 'open':
            case 'deliver':
            case 'cancel':
                if($entity->getStatus()!= '10_opened')
                {
                    throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The operation "'.$action.'" cannot apply on a request '.$entity->getStatus().' !');
                }
                if($entity->getAssignedTo()!=$current_user)
                {
                    throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The operation "'.$action.'" is reserved to its owner !');
                }
                break;
            case 'new':
                if(!$context->isGranted('ROLE_AGENT'))
                {
                    throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The operation "'.$action.'" is reserved to syst agent !');
                }
                break;
            // interdit
            case 'delete':
                throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The operation "delete" is banned for security reasons, please contact the system administrator, if you really want to delete this service ticket ! By the way, you can cancel it if you want ! ');
                break;
            default:
                throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Unknow operation !');
        }
        return $entity;
    }

    protected function flushEntity($entity, $action, $request=null)
    {
        // pre flush
        $entity->setLastModifiedAt(new \DateTime('now'));
        switch($action)
        {
            case 'deliver':
                $entity->setStatus('20_delivered');
                break;

        }

        // flush options for new, edit, delete 
        parent::flushEntity($entity, $action, $request);
    }

    protected function initNewEntity($entity){
        // ?? ticket without request ??
        $entity->setStatus('10_opened');
        $entity->setAssignedTo($this->get('security.context')->getToken()->getUser());
        $entity->setRequestReceivedAt(new \DateTime('now'));
        $entity->setOpenedAt(new \DateTime('now'));
        $entity->setLastModifiedAt(new \DateTime('now'));
    }

    /**
     * deliver
     * granted only to ROLE_AGENT
     *
     * reserved to owner
     */
    public function deliverAction($id)
    {
        // declare that the service requested has been delivered to the client
        // waiting for the confirmation of the client
        // the ticket will automatically be closed by syst in several days defined
        // even if the client didn't click on the confirm button
        //
        // however, if the client think the service has not been delivered as declaired
        // he can interrupt the close process by click on the reject button
        // in this case, the agent has to review the process to ensure the delivrance
        //
        // the admin shall get an alarm message in this case
        //
        // at the moment, for a 1st quick brain storming, we ignore all following process **********ToDo
        $entity = $this->getEntity('deliver', $id);
        $this->flushEntity($entity, 'deliver');
        return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_index'));
    }

    /**
     * cancel
     * granted only to ROLE_AGENT
     *
     * reserved to owner
     */
    public function cancelAction($id)
    {
        $entity = $this->getEntity('cancel', $id);
        throw new \Exception('not available yet');
    }

    /**
     * open 
     * granted only to ROLE_AGENT
     *
     * reserved to owner
     */
    public function openAction($id)
    {
        $entity = $this->getEntity('open', $id);
        throw new \Exception('not available yet');
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
        throw new \Exception('not available yet');
    }
}
