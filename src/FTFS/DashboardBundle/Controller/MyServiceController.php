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
            // any agent and client
            // restricted to its owner (assigned to) ant his share list and of cause all agents 
            // ToDo: share group ToDo ########################################################
            case 'show':
            case 'observation_add':
            case 'attachment_upload':
            case 'attachment_download':
            case 'attachment_delete':
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
            // agent except owner 
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
                break;
            // reserved to agent owner 
            case 'open':    // status: submitted, assigned, not opened
            case 'transfer':    // status: assigned, opened
            case 'close':   // status: opened
                if($entity->getAssignedTo()!=$current_user){
                    throw new \Exception('Action: '.$action.' is reserved to its agent owner!');
                }
                break;
            // reserved to client owener
            case 'submit':
            case 'delete':
                if($entity->getStatus()!='created' or $entity->getRequestedBy()!=$current_user)
                {
                    throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The operation '.$action.' is only possible for the ticket with status "created" and reserved to its creator ! ');
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
        $status = $entity->getStatus();
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
                    if($mode == 'edit_submit' and ('created' == $status or 'interrupted' == $status))
                    {
                        $entity->setStatus('submitted');
                        $entity->setRequestedAt(new \DateTime('now'));
                    }
                }elseif($role == 'agent'){
                    switch($mode)
                    {
                        case 'edit_open':
                            //parent::flushEntity($entity, $action, $request);
                            if('assigned' == $status or 'interrupted' == $status)
                            {
                                $entity->setStatus('opened');
                                $entity->setOpenedAt(new \DateTime('now'));
                            }else{
                                throw new \Exception('impossible to open a ticket with status: '.$status);
                            }
                            break;
                        case 'edit_close':
                            if('closed' == $status)
                            {
                                $entity->setStatus('closed');
                                $entity->setClosedAt(new \DateTime('now'));
                            }else{
                                throw new \Exception('impossible to close a ticket with status: '.$status);
                            }
                            break;
                        case 'edit_transfer':
                            throw new \Exception("unavailable operation: ".$mode." of ".$role);
                            break;
                        case 'edit_only':
                            if('colsed' == $status)
                            {
                                throw new \Exception("impossible to edit a closed ticket");
                            }
                            break;
                        default:
                            throw new \Exception("unknow operation: ".$mode." of ".$role);
                    }
                }
                break;
            case 'take':
                if(is_null($entity->getAssignedTo()))
                {
                    $entity->setStatus('assigned');
                }
                $entity->setAssignedTo($current_user);
                break;
            case 'open':
                $entity->setStatus('opened');
                break;
            case 'close':
                $entity->setStatus('closed');
                break;
            case 'submit':
                $entity->setStatus('submitted');
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
     * open 
     * granted only to ROLE_AGENT
     *
     * reserved to owner
     */
    public function openAction($id)
    {
        $entity = $this->getEntity('open', $id);
        $this->flushEntity($entity, 'open');
        return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array('id' => $entity->getId())));
    }

    /**
     * close 
     * granted only to ROLE_AGENT
     *
     * reserved to owner
     */
    public function closeAction($id)
    {
        $entity = $this->getEntity('close', $id);
        $this->flushEntity($entity, 'close');
        return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array('id' => $entity->getId())));
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

    /**
     * submit 
     * granted only to ROLE_CLIENT
     *
     * reserved to owner
     */
    public function submitAction($id)
    {
        $entity = $this->getEntity('submit', $id);
        $this->flushEntity($entity, 'submit');
        return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array('id' => $entity->getId())));
    }


    /** **********************************************************************  attachment & observation **/

    /** 
     * attachement file management: download an attachment
     */
    public function attachmentDownloadAction($id, $attachment_id)
    {
        $ticket = $this->getEntity('attachment_download', $id);
        $attachment = $this->getDoctrine()
                        ->getEntityManager()
                        ->getRepository('FTFSServiceBundle:ServiceTicketAttachment')
                        ->find($attachment_id);
        if(!$attachment){
            throw $this->createNotFoundException('Attachment demanded not found !');        
        }

        $content = file_get_contents($attachment->getAbsolutePath());
        $response = new \Symfony\Component\HttpFoundation\Response();

        $response->headers->set('Content-Type', 'mime/type');
        $response->headers->set('Content-Disposition', 'attachment;filename='.$attachment->getName());

        $response->setContent($content);

        return $response;
    }

    /** 
     * attachement file management: upload an attachment
     */
    public function attachmentUploadAction($id)
    {
        $ticket = $this->getEntity('attachment_upload', $id);
        $uploaded_by = $this->get('security.context')->getToken()->getUser();
        $attachment = new \FTFS\ServiceBundle\Entity\ServiceTicketAttachment($ticket, $uploaded_by);
        $form = $this->createFormBuilder($attachment)
                ->add('file')
                ->add('filename')
                ->getForm()
                ;

        if($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            if($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($attachment);
                $em->flush();
            }
            return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array(
                'id' => $id,
            )));
        }

        return $this->render('FTFSServiceBundle:ServiceTicketAttachment:upload_form.html.twig', array(
            'id' => $id,
            'prefix' => $this->getRoutingPrefix(),
            'attachment_upload_form' => $form->createView(),
        ));
    }

    /** 
     * attachement file management: delete an attachment identified by $attachment_id
     */
    public function attachmentDeleteAction($id, $attachment_id)
    {
        // access control ...
        $ticket = $this->getEntity('attachment_delete', $id);
        $em = $this->getDoctrine()->getEntityManager();
        $attachment = $em->getRepository('FTFSServiceBundle:ServiceTicketAttachment')
                        ->find($attachment_id);
        if(!$attachment){
            throw $this->createNotFoundException('Attachment demanded not found !');        
        }
        $em->remove($attachment);
        $em->flush();

        return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array(
            'id' => $id,
        )));
    }

    /**
     * AJAX provider: New Ticket Observation 
     */
    public function observationAddAction($id)
    {
        $request = $this->get('request');
        $add_to_id = $request->get('add-to-id');

        $observation = new \FTFS\ServiceBundle\Entity\ServiceTicketObservation;
        $em = $this->getDoctrine()->getEntityManager();
        $attach = $em->getRepository('FTFSServiceBundle:ServiceTicketObservation')->find($add_to_id);
        $form = $this->createFormBuilder($observation);
        if($attach)
        {
            // if not null, retrive messages from ancient messages...
            // $attach->retrive(3);
            $form->add('content');
        }else{
            $form->add('content');
        }
        $form = $form->getForm();

        if('POST'===$request->getMethod())
        {
            $form->bindRequest($request);
            if($form->isValid())
            {
                if($attach)
                {
                    $observation->setAttachTo($attach);
                }

                $observation->setSendAt(new \DateTime('now'));
                $observation->setSendBy($this->get('security.context')->getToken()->getUser());
                $observation->setTicket($this->getEntity('observation_add', $id));

                $em->persist($observation);
                $em->flush();
                return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array('id' => $id)));
            }
        }

        return $this->render('FTFSServiceBundle:ServiceTicketObservation:add_form.html.twig', array(
            'id' => $id,
            'prefix' => $this->getRoutingPrefix(),
            'add_to_id' => $add_to_id,
            'observation_add_form' => $form->createView(),
        ));
    }
}
