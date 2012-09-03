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
            switch($status)
            {
                case 'all':
                    break;
                case '10_rejected':
                case '20_unsent':
                case '30_awaiting':
                case '40_accepted':
                    $queryBuilder
                        ->andWhere('e.status = :status')
                        ->setParameter('status', $status);
                    break;
                default:        // by default if no filter is set, return rejected, unsent, awaiting requests
                    $queryBuilder
                        ->andWhere("e.status = '10_rejected' or e.status = '20_unsent' or e.status = '30_awaiting'");
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
                case 'assignedawaiting': // all new requests assgined to me 
                    $queryBuilder->andWhere("e.status = '30_awaiting'");
                case 'assigned':        // assigned, all requests assgined to me
                    $queryBuilder->andWhere('e.assigned_to = :agent')
                                 ->setParameter('agent', $current_user);
                    break;
                default:            // all requestes assigned to me, awaiting or rejected
                    $queryBuilder->andWhere("e.status = '30_awaiting' or e.status = '10_rejected'");
                    $queryBuilder->andWhere('e.assigned_to = :agent')
                                 ->setParameter('agent', $current_user);
                    $queryBuilder->add('orderBy', 'e.status desc, e.severity asc, e.last_modified_at desc');
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
        $entity = parent::getEntity($action, $id);

        // access control
        //
        $context = $this->get('security.context');
        $current_user = $context->getToken()->getUserName();
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

            // restricted to its owner: requested_by, status: 10_rejected or 20_unsent 
            case 'send':
            case 'edit':
            case 'delete':
                if(('10_rejected' != $entity->getStatus()) && ('20_unsent' != $entity->getStatus()) )
                {
                    throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The operation "'.$action.'" cannot apply on a request '.$entity->getStatus().'!');
                }
                if($entity->getRequestedBy()!=$current_user)
                {
                    throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The operation "'.$action.'" is reserved to its owner !');
                }
                break;      
            // restricted to its owner: assigned_to , status: 20_awaiting
            case 'transfer':
            case 'accept':
            case 'reject':
                if($entity->getStatus()!= '30_awaiting')
                {
                    throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The operation "'.$action.'" cannot apply on a request '.$entity->getStatus().' !');
                }
                if($entity->getAssignedTo()!=$current_user)
                {
                    throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The operation "'.$action.'" is reserved to its owner !');
                }
                break;      
            // should be granted as ROLE_AGENT
            case 'take':
                if($entity->getStatus()!= '30_awaiting')
                {
                    throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The operation "'.$action.'" cannot apply on a request '.$entity->getStatus().' !');
                }
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

        // other flush options
        switch($action)
        {
            case 'send':
                // check no sending a sent request
                if(!is_null($entity->getStatus()) && '20_unsent'!=$entity->getStatus() and '10_rejected'!=$entity->getStatus())
                {
                    throw new \Exception("Error: the request is alfready sent, you can only send a request with status 'unsent' or null!"); 
                }
                $entity->setStatus("30_awaiting"); 
                $entity->setRequestedAt(new \DateTime('now')); 
                break;
            case 'take':
                $entity->setAssignedTo($this->get('security.context')->getToken()->getUser());
                break;
            case 'transfer':
                $entity->setAssignedTo(null);
                break;
            case 'reject':
                $entity->setStatus('10_rejected');
                break;
            case 'accept':
                // defined in action
                break;
        }
        // flush options for new, edit, delete 
        parent::flushEntity($entity, $action, $request);

        // redirect
        if($request && 'send' == $request->get('redirect'))
        {
            $this->sendAction($entity->getId());
        }
    }

    protected function initNewEntity($entity)
    {
        $entity->setRequestedBy($this->get('security.context')->getToken()->getUserName());
        $entity->setStatus('20_unsent');
    }

    /**
     * send a request to the ftfs support service
     * granted only to ROLE_CLIENT
     */
    public function sendAction($id)
    {
        $entity = $this->getEntity('send', $id);
        $this->flushEntity($entity, 'send');
        return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_index'));
    }

    /**
     * take a new request
     * granted only to ROLE_AGENT
     */
    public function takeAction($id)
    {
        $entity = $this->getEntity('take', $id);
        $this->flushEntity($entity, 'take');
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
        // transfer a request means to give up the control of a request to a list of people
        // before the destination confirms the transferance, 
        // the request is still in the original agent responsibility
        // he cannot edit it though ;-) except he retakes it 
        // maybe choose from a list of people, for instant, only to all
        // here simple reassign the request to null
        //
        // only request awaiting can be transfered...
        $entity = $this->getEntity('transfer', $id);
        $this->flushEntity($entity, 'transfer');
        return $this->redirect($this->generateUrl('ftfs_dashboardbundle_myservicerequest_show', array('id' => $entity->getId())));
    }

    /**
     * accept a new request
     * granted only to ROLE_AGENT
     *
     * reserved to owner
     */
    public function acceptAction($id)
    {
        // accept a request and use its information to open a new service ticket
        // assign the new service ticket to user
        // inform the client
        $request = $this->getEntity('accept', $id);
        $service = new \FTFS\ServiceBundle\Entity\Service;

        $service->setName(substr_replace($request->getName(), 'ST', -2));
        $service->setSeverity($request->getSeverity());
        $service->setPriority(300);
        $service->setStatus('10_opened');
        $service->setAssignedTo($this->get('security.context')->getToken()->getUserName());

        $service->setRequestedBy($request->getRequestedBy());
        $service->setRequestedVia($request->getRequestedVia());
        $service->setRequestReceivedAt($request->getRequestedAt());
        // ManyToOne, check if is null
        // very dangerous here, in case that if we delete a service type,
        // many problems would occured in the future
        $servicetype = $request->getType();
        if(!is_null($servicetype))
        {
            $service->setType($servicetype);
        }
        $service->setSummary($request->getSummary());
        $service->setDetail($request->getDetail());

        $service->setAssetName($request->getAssetName());

        $service->setOpenedAt(new \DateTime('now'));
        $service->setLastModifiedAt(new \DateTime('now'));

        $request->setService($service);
        $request->setStatus('40_accepted');
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($service);
        $em->flush();

        $this->notify('accept');
        return $this->redirect($this->generateUrl('ftfs_dashboardbundle_myservice_edit', array('id' => $service->getId())));
    }

    /**
     * reject a new request
     * granted only to ROLE_AGENT
     *
     * reserved to owner
     */
    public function rejectAction($id)
    {
        // popout a dialog to type into the observation:
        // information not complete, please describe in detail
        // 
        //
        // change the status as 10_rejected
        $entity = $this->getEntity('reject', $id);
        $this->flushEntity($entity, 'reject');
        return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array('id' => $id)));
    }
}
