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

    public function getExportationFormAction()
    {
        $container = array('export' => 'export form');
        $action_form = $this->createFormBuilder($container)
            ->add('filename', 'text', array(
                'required' => false,
            ))
            ->add('filetype', 'choice', array(
                'choices' => array(
                    'csv' => 'csv',
                ),
            ))
            ->getForm();

        $request = $this->getRequest();
        if($request->getMethod() === 'POST') {
            $action_form->bindRequest($request);
            if($action_form->isValid()) {
                // exportation
                $data = $action_form->getData();
                $filename = $data['filename'] == '' ? 'selected_tickets' : $data['filename'];
                $filetype = $data['filetype'];
                $filename = $filename.'.'.$filetype;

                $response = new \Symfony\Component\HttpFoundation\Response();

                $response = $this->render($this->getViewPath().':crud_box_index_table.csv.twig', array(
                    'list' => $this->getEntityList(array(
                        'pagination' => false,
                    )),
                ));

                $content = $response->getContent();
                $sep = ';';
                $content = str_replace('<span>', '', $content);
                $content = str_replace('</span>', $sep, $content);
                $response->setContent($content);

                $response->headers->set('Content-Type', 'text/csv');
                $response->headers->set('Content-Disposition', 'attachment;filename='.$filename);
                return $response;
            }
            return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_index'));
        }
        return $this->render('FTFSServiceBundle:ServiceTicket:exportation_form.html.twig', array(
            'action_form' => $action_form->createView(),
            'action' => $this->generateUrl($this->getRoutingPrefix().'_get_exportation_form'),
            'prefix' => $this->getRoutingPrefix(),
        ));
    }

    public function getMyServiceListAction()
    {

        return $this->render($this->getViewPath().':crud_box_index_table.html.twig', array(
            'list' => $this->getEntityList(array(
                'pagination' => false,
            )),
            'prefix' => $this->getRoutingPrefix(),
        ));
    }

    public function getMyServiceListFileAction()
    {
        /*
        $content = file_get_contents($attachment->getAbsolutePath());
        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->headers->set('Content-Type', 'mime/type');
        $response->headers->set('Content-Disposition', 'attachment;filename='.$attachment->getName());
        $response->setContent($content);
         */
        $response = new \Symfony\Component\HttpFoundation\Response();

        $filename = $this->getRequest()->get('filename');
        $filename = $filename && strlen($filename) > 0 ? $filename : 'selected-tickets.csv';

        $response = $this->render($this->getViewPath().':crud_box_index_table.csv.twig', array(
            'list' => $this->getEntityList(array(
                'pagination' => false,
            )),
        ));
        $content = $response->getContent();
        $sep = ';';
        $content = str_replace('<span>', '', $content);
        $content = str_replace('</span>', $sep, $content);
        $response->setContent($content);

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment;filename='.$filename);
        //throw new \Exception($content);
        return $response;
    }


    /**
     * get entity list for index Action
     * getEntityListDefinition
     */
    protected function getEntityList(array $options = array())
    {
        // get request object
        $request = $this->getRequest();

        // general query builder with ordering
        $queryBuilder = $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())
            ->createQueryBuilder('e')
            ->leftJoin('e.service', 's')
            ->leftJoin('e.requested_by', 'u');

        /* type filter ************************************************************************************/
        $type = $request->get('type');
        if(is_numeric($type) && $type > 0) 
        {
            $queryBuilder
                ->andWhere('s.id = :id')
                ->setParameter('id', $type);
        }


        /** 
         * role filter ***************************************************************************************
         * role_client covers the role_agent, that is to say, if a user is both granted as role_client 
         * and role_agent, or any other sup role, only get priviledge of role_client
         *
         */
        $context = $this->get('security.context');
        $current_user = $context->getToken()->getUser();

        if($context->isGranted('ROLE_CLIENT')){
            /**
             * in case of an client user, only his/her own records or shared records is visible
             */
            $status = $request->get('status');
            $company = $current_user->getCompany();

            if($context->isGranted('ROLE_CLIENT_COMPANY') && $status==='company'){
                $queryBuilder
                    ->andWhere('u.company = :company')
                    ->setParameter('company', $company)
                    //->add('orderBy', 'e.status asc, e.severity asc, e.last_modified_at desc');
                    ->add('orderBy', 'e.last_modified_at desc, e.status asc, e.severity asc');
            }else{
                $queryBuilder
                    ->andWhere('e.requested_by = :requested_by')
                    ->setParameter('requested_by', $current_user)
                    //->add('orderBy', 'e.status asc, e.severity asc, e.last_modified_at desc');
                    ->add('orderBy', 'e.last_modified_at desc, e.status asc, e.severity asc');
            }
        }elseif($context->isGranted('ROLE_AGENT')){
            $queryBuilder
                //->add('orderBy', 'e.status asc, e.priority asc, e.severity asc, e.last_modified_at desc');
                ->add('orderBy', 'e.last_modified_at desc, e.priority asc, e.severity asc, e.status asc');

            // filter by user if not alldeployed, nor allunassgiend
            $status = $request->get('status');
            if($status != 'alldeployed' && $status != 'allunassigned') {
                // default, filtered by assignedTo
                $queryBuilder
                    ->andWhere('e.assigned_to = :assigned_to')
                    ->setParameter('assigned_to', $current_user);
            }
        }else{
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Normally you have to be granted as client or agent in order to enter this controller : MyServiceController:list ! You see this error page because of an internal error or you tried to access a ressource without permission.');
        }
        
        /* status filter *****************************************************************************/
        $status = $request->get('status');
        switch($status)
        {
            // general filter
            //case 'submitted':
            case 'opened':
                $queryBuilder
                    ->andWhere("e.status = 'opened' or e.status = 'reopened'")
                    ->andWhere("e.pending = 0");
                break;
            case 'created':
            case 'assigned':
                $queryBuilder
                    ->andWhere("e.pending = 0");
            case 'closed':
                $queryBuilder
                    ->andWhere('e.status = :status')
                    ->setParameter('status', $status);
            case 'company':
                break;
            case 'awaiting': // both submitted(=unassigned) and assigned
                $queryBuilder
                    ->andWhere("e.status = 'submitted' or e.status = 'assigned'")
                    ->andWhere("e.pending = 0");
                break;
            case 'allassigned': // all agent assigned
            case 'all':  // all client owned
                break;
            // additional filter for agent
            case 'alldeployed':
                $queryBuilder
                    ->andWhere("e.status <> 'created'");
                break;
            case 'allunassigned':
                $queryBuilder
                    ->andWhere("e.status = 'submitted'");
                break;
            // by default if no filter is set, return no closed
            case 'pending':
                $queryBuilder
                    ->andWhere("e.pending = 1");
                break;
            default:        
                $queryBuilder
                    ->andWhere("e.status <> 'closed'");
        }

        /* fime filter ***************************************************************************************************/
        //
        $from = $request->get('from');
        if(preg_match('/^[0-9]+\/[0-9]+\/[0-9]+$/', $from)) {
            $from = strtotime($from);
            $queryBuilder
                ->andWhere("e.last_modified_at >= :from")
                ->setParameter('from', date('Y-m-d H:i:s', $from));
        }
        $to = $request->get('to');
        if(preg_match('/^[0-9]+\/[0-9]+\/[0-9]+$/', $to)) {
            $to = strtotime('+1 day', strtotime($to));
            $queryBuilder
                ->andWhere("e.last_modified_at <= :to")
                ->setParameter('to', date('Y-m-d H:i:s', $to));
        }
        //throw new \Exception(date('Y-m-d H:i:s', $from));
        //throw new \Exception($from.'::'.date('Y-m-d H:i:s', strtotime('-1 day', strtotime($from))));


        // pagination
        $count = count($queryBuilder->getQuery()->getResult());
        $limit = 8; // default limit and page value
        $page = 1;
        $pagination = false; 
        if(!array_key_exists('pagination', $options) or $options['pagination']) {
            $request_limit = $request->get('limit');
            $limit = is_numeric($request_limit) ? $request_limit : $limit;

            $request_page = $request->get('page');
            $page = (is_numeric($request_page) && $request_page >0) ? $request_page : $page;

            $offset = $limit*($page-1);
            $queryBuilder
                ->setFirstResult($offset)
                ->setMaxResults($limit)
                ;
            $pagination = true;
        }

        $entities = $queryBuilder->getQuery()->getResult();
        $meta = array();
        if($type == 2) {// show rma in meta when repair
            $rma_er = $this->getDoctrine()->getEntityManager()->getRepository('FTFSServiceBundle:RMA');
            $rma_meta = array();
            foreach($entities as $entity) {
                $rma = $rma_er->findOneByTicket($entity->getId());
                if($rma) { // if exists
                    $rma_meta[$entity->getName()] = $rma->getName();
                }
            }
            //throw new \Exception(print_r($rma_meta));
            $meta['rma'] = $rma_meta;
        }

        return new \Doctrine\Common\Collections\ArrayCollection(array(
            'count' => $count,
            'page' => $page,
            'limit' => $limit,
            'entities' => $entities,
            'meta' => $meta,
            'pagination' => $pagination,
        ));
    }

    /*
     * getEntityDefinition
     */
    protected function getEntity($action, $id = -1)
    {
        $context = $this->get('security.context');
        $current_user = $context->getToken()->getUser();
        $entity = parent::getEntity($action, $id);

        switch($action)
        {
            // no restriction
            case 'new':
            case 'print':
                break;
            // any agent and client
            // restricted to its owner (assigned to) ant his share list and of cause all agents 
            // visible for all company memebers
            case 'ticket_timer_list':
            case 'show':
                $rma = $this->getDoctrine()->getEntityManager()
                    ->getRepository('FTFSServiceBundle:RMA')->findOneByTicket($entity->getId());
                if($rma) {
                    $this->setMeta('rma', $rma->getName());
                }
            case 'attachment_download':
                if($context->isGranted('ROLE_AGENT'))
                {
                    // role_agent, bypass the protection
                    break;
                }else{
                    // maybe restricted for the share list in the future
                    // at the moment, if granted as role_client, senctioned for username ToDo
                    if($entity->getRequestedBy()->getCompany()!=$current_user->getCompany())
                    {
                        throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The operation "'.$action.'" is reserved to its owner ant its owner\'s share list !');
                    }
                }
                break;
            case 'continue':
            case 'observation_add':
            case 'attachment_upload':
            case 'attachment_delete':
            case 'sharelist_add':
            case 'sharelist_delete':
            case 'devices_add':
            case 'devices_edit':
            case 'devices_delete':
            case 'edit':
                if($context->isGranted('ROLE_AGENT') || $context->isGranted('ROLE_CLIENT_COMPANY') && $entity->getRequestedBy()->getCompany()===$current_user->getCompany())
                {
                    // role_agent, bypass the protection
                    // or company owner
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

            // all agent only
            case 'generate_rma':
                if($context->isGranted('ROLE_AGENT'))
                {
                    break;
                }else{
                        throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The operation "'.$action.'" is reserved to its owner ant its owner\'s share list !');
                }
                break;

            // agent except owner 
            case 'take':
                if(!$context->isGranted('ROLE_AGENT'))
                {
                    throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The operation "take" of entity '.$entity->getName().'" is reserved for ROLE_AGENT !');
                }else{
                    if($entity->getAssignedTo()==$current_user && $entity->getStatus()!='submitted')
                    {
                        throw new \Exception('Your have already taken this service ticket !');
                    }elseif($entity->getStatus()=='created' || $entity->getStatus()=='closed'){
                        throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('The service request "'.$entity->getName().'" has already been taken !');
                    }
                }
                break;

            // reserved to agent owner 
            case 'open':    // status: submitted, assigned, not opened
            case 'pend':    // status: submitted, assigned, opened
            case 'reopen':    // status: submitted, assigned, not opened
            case 'transfer':    // status: assigned, opened
            case 'close':   // status: opened
                if($entity->getAssignedTo()!=$current_user){
                    throw new \Exception('Action: '.$action.' is reserved to its agent owner!');
                }
                break;

            // reserved to client owener && new ticket
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
                $entity->setName($this->get('ftfs_servicebundle.name_generator')->getNewServiceTicketName());
                $entity->setCreatedAt(new \DateTime('now'));
                if($role == 'client')
                {
                    $entity->setPriority($entity->getSeverity());
                    $entity->setStatus('created');
                    $entity->setRequestedVia('web');
                    if($mode == 'new_submit')
                    {
                        $entity->setStatus('submitted');
                        $entity->setRequestedAt(new \DateTime('now'));
                    }
                }elseif($role == 'agent'){
                    $entity->setStatus('opened');
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
                if('submitted' == $entity->getStatus())
                {
                    $entity->setStatus('assigned');
                }
                $entity->setAssignedTo($current_user);
                break;
            case 'open':
                $entity->setOpenedAt(new \DateTime('now'));
                $entity->setStatus('opened');
                break;
            case 'reopen':
                $entity->setStatus('reopened');
                break;
            case 'pend':
                //$entity->setOpenedAt(new \DateTime('now'));
                //$entity->setStatus('opened');
                $entity->setPending(true);
                break;
            case 'continue':
                //$entity->setOpenedAt(new \DateTime('now'));
                //$entity->setStatus('opened');
                $entity->setPending(false);
                break;
            case 'close':
                $entity->setClosedAt(new \DateTime('now'));
                $entity->setStatus('closed');
                break;
            case 'submit':
                $entity->setRequestedAt(new \DateTime('now'));
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
        //    $options['client'] = $context->getToken()->getUser()->getCompany()->getId();
        }elseif($context->isGranted('ROLE_AGENT')){
            $options['role'] = 'agent';
        }

        return parent::getEntityType($options);
    }



    public function indexAction()
    {
        // set index into session
        $request = $this->getRequest();
        $request->getSession()->set('index', $request->getRequestUri());

        $context = $this->get('security.context');
        if($context->isGranted('ROLE_AGENT')){
            $services = $this->getDoctrine()->getEntityManager()
                ->getRepository('FTFSServiceBundle:Service')->findAll();
        }else{
            $services = $this->getDoctrine()->getEntityManager()
                ->getRepository('FTFSServiceBundle:Service')->findBy(array(
                'open_to_client' => true,
            ));
        }


        // general twig rendering
        return $this->render($this->getViewPath().':index2.html.twig', array(
            'list' => $this->getEntityList(),
            'services' => $services,
            'prefix' => $this->getRoutingPrefix(),
        ));
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
     * reopen 
     * granted only to ROLE_AGENT
     *
     * reserved to owner
     */
    public function reopenAction($id)
    {
        $entity = $this->getEntity('reopen', $id);

        $container = array('reopen' => 'reopen detail');
        $action_form = $this->createFormBuilder($container)
            ->add('reason', 'textarea')
            ->getForm();

        $request = $this->getRequest();
        if($request->getMethod() === 'POST') {
            $action_form->bindRequest($request);
            if($action_form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();

                // observation to client
                $observation = new \FTFS\ServiceBundle\Entity\ServiceTicketObservation;
                $observation->setSendAt(new \DateTime('now'));
                $observation->setSendBy($this->get('security.context')->getToken()->getUser());
                $data = $action_form->getData();
                $content['type'] = 'reopen';
                $content['timer'] = 'reopened'; // reg in timer log
                $content['reason'] = $data['reason'];
                $observation->setContent($content);

                $observation->setTicket($entity);
                $entity->setStatus('reopened');
                $em->persist($observation);
                $this->flushEntity($entity, 'reopen');
            }
            return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array(
                'id' => $id,
            )));
        }
        return $this->render($this->getViewPath().':show.html.twig', array(
            'entity' => $entity,
            'meta' => $this->getMeta(),
            'prefix' => $this->getRoutingPrefix(),
            'action_form' => $action_form->createView(),
        ));
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

        $container = array('closure_report' => 'closure_report detail');
        $action_form = $this->createFormBuilder($container)
            ->add('report', 'textarea')
            ->getForm();

        $request = $this->getRequest();
        if($request->getMethod() === 'POST') {
            $action_form->bindRequest($request);
            if($action_form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();

                // closure report
                $closure_report = new \FTFS\ServiceBundle\Entity\ServiceTicketObservation;
                $closure_report->setSendAt(new \DateTime('now'));
                $closure_report->setSendBy($this->get('security.context')->getToken()->getUser());
                $content = array();
                $content['type'] = 'close_report';
                $content['timer'] = 'closed';
                $data = $action_form->getData();
                $content['report'] = $data['report'];
                $closure_report->setContent($content);

                $closure_report->setTicket($entity);
                //$entity->addServiceTicketObservation($observation);
                $entity->setStatus('closed');
                $em->persist($closure_report);
                $this->flushEntity($entity, 'close');
            }
            return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array(
                'id' => $id,
            )));
        }
        return $this->render($this->getViewPath().':show.html.twig', array(
            'entity' => $entity,
            'meta' => $this->getMeta(),
            'prefix' => $this->getRoutingPrefix(),
            'action_form' => $action_form->createView(),
        ));
    }

    /**
     * pend 
     * granted only to ROLE_AGENT
     *
     * reserved to owner
     */
    public function pendAction($id)
    {
        $entity = $this->getEntity('pend', $id);
        if($entity->getPending()) { // already pending...
            return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array(
                'id' => $id,
            )));
        }

        $container = array('observation' => 'observation detail');
        $action_form = $this->createFormBuilder($container)
            ->add('reason', 'textarea')
            ->getForm();

        $request = $this->getRequest();
        if($request->getMethod() === 'POST') {
            $action_form->bindRequest($request);
            if($action_form->isValid()) {
                $observation = new \FTFS\ServiceBundle\Entity\ServiceTicketObservation;
                $observation->setSendAt(new \DateTime('now'));
                $observation->setSendBy($this->get('security.context')->getToken()->getUser());
                $content = array();
                $content['type'] = 'pend';
                $content['timer'] = 'pended'; // reg in timer log
                $data = $action_form->getData();
                $content['reason'] = $data['reason'];
                $observation->setContent($content);

                $observation->setTicket($entity);
                //$entity->addServiceTicketObservation($observation);
                $entity->setPending(true);
                $this->getDoctrine()->getEntityManager()->persist($observation);
                $this->flushEntity($entity, 'pend');
            }
            return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array(
                'id' => $id,
            )));
        }
        return $this->render($this->getViewPath().':show.html.twig', array(
            'entity' => $entity,
            'meta' => $this->getMeta(),
            'prefix' => $this->getRoutingPrefix(),
            'action_form' => $action_form->createView(),
        ));
    }

    /**
     * continue 
     * granted only to ROLE_AGENT
     *
     * reserved to owner
     */
    public function continueAction($id)
    {
        $entity = $this->getEntity('continue', $id);
        if(!$entity->getPending()) { // not pending...
            return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array(
                'id' => $id,
            )));
        }

        $container = array('observation' => 'observation detail');
        $action_form = $this->createFormBuilder($container)
            ->add('reason', 'textarea')
            ->getForm();

        $request = $this->getRequest();
        if($request->getMethod() === 'POST') {
            $action_form->bindRequest($request);
            if($action_form->isValid()) {
                $observation = new \FTFS\ServiceBundle\Entity\ServiceTicketObservation;
                $observation->setSendAt(new \DateTime('now'));
                $observation->setSendBy($this->get('security.context')->getToken()->getUser());
                $content = array();
                $content['type'] = 'continue';
                $content['timer'] = 'continued'; // reg in timer log
                $data = $action_form->getData();
                $content['reason'] = $data['reason'];
                $observation->setContent($content);

                //$entity->addServiceTicketObservation($observation);
                $observation->setTicket($entity);
                $entity->setPending(false);
                $this->getDoctrine()->getEntityManager()->persist($observation);
                $this->flushEntity($entity, 'continue');
            }
            return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array(
                'id' => $id,
            )));
        }
        return $this->render($this->getViewPath().':show.html.twig', array(
            'entity' => $entity,
            'meta' => $this->getMeta(),
            'prefix' => $this->getRoutingPrefix(),
            'action_form' => $action_form->createView(),
        ));
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
        $entity = $this->getEntity('transfer', $id);
        $current_user = $this->get('security.context')->getToken()->getUser();

        $data = array(
            'agent' => $current_user,
        );
        $agent_form_options = array(
                'class' => 'FTFSUserBundle:User',
                'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                                ->where('u.is_agent = 1');
                },
                'label' => 'Select the agent to assign to:',
            );
        if($entity->getStatus()=='assigned') {
            $agent_form_options['empty_value'] = '<Any Agent>';
            $agent_form_options['required'] = false;
        }
        $action_form = $this->createFormBuilder($data)
            ->add('agent', 'entity', $agent_form_options)
            ->getForm();

        $request = $this->getRequest();
        if($request->getMethod() === 'POST') {
            $action_form->bindRequest($request);
            if($action_form->isValid()) {
                $data = $action_form->getData();
                $target = $data['agent'];
                if($target) {
                    if($target !== $current_user) {
                        $entity->setAssignedTo($target);
                    }
                }else{
                    // to all agents
                    if($entity->getStatus()=='assigned') {
                        $entity->clearAssignedTo();
                        $entity->setStatus('submitted');
                    }
                }
                $this->getDoctrine()->getEntityManager()->flush();
            }
            return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array(
                'id' => $id,
            )));
        }
        return $this->render($this->getViewPath().':show.html.twig', array(
            'entity' => $entity,
            'meta' => $this->getMeta(),
            'prefix' => $this->getRoutingPrefix(),
            'action_form' => $action_form->createView(),
        ));
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
     * add an entry to share list
     */
    public function sharelistAddAction($id)
    {
        $ticket = $this->getEntity('sharelist_add', $id);

        $current_list = $ticket->getShareList();
        $current_user = $this->get('security.context')->getToken()->getUser();
        $user_list = $this->get('ftfs_configurator')->get('sharelist', $current_user);
        if(!$user_list) {
            $user_list = array();
        }

        $available_choices = array();
        $available_list = array();
        foreach($user_list as $email => $name) {
            if(!array_key_exists($email, $current_list)) {
                $available_choices[$email] = $name.' ('.$email.')';
                $available_list[$email] = $name;
            }
        }

        //throw new \Exception(print_r($available_choices));

        $share_entry = array('sharelist' => 'entry detail');
        $form = $this->createFormBuilder($share_entry)
                ->add('email')
                ->add('title', 'choice', array(
                    'choices' => array(
                        'Ms.' => 'Ms.',
                        'Mrs.' => 'Mrs.',
                        'Mr.' => 'Mr.',
                    ),
                ))
                ->add('firstname')
                ->add('surname')
                ->add('add_to_default', 'checkbox', array(
                    'label' => 'Add to default list?',
                    'required' => false,
                ))
                ->add('available_emails', 'choice', array(
                    'choices' => $available_choices,
                    'multiple' => true,
     //               'expanded' => true,
                    'required' => false,
                ))
                ->getForm()
                ;

        if($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());

            $data = $form->getData();
            $type = $this->getRequest()->get('type');
            switch($type) {
                case 'select':
                    $selected = array();
                    foreach($data['available_emails'] as $s) {
                        $selected[$s] = $available_list[$s];
                    }
                    $ticket->addShareList($selected);
                    //throw new \Exception(print_r($selected));
                    break;
                case 'add':
                    $name = $data['title'].' '.$data['firstname'].' '.$data['surname'];
                    $ticket->addShareList(array(
                        $data['email'] => $name,
                    ));
                    // if add to list
                    if($data['add_to_default']) {
                        $configurator = $this->get('ftfs_configurator');
                        $sharelist = $configurator->get('sharelist', $current_user);
                        $sharelist = $sharelist ? $sharelist : array();
                        if(!array_key_exists($data['email'], $sharelist)) {
                            $sharelist[$data['email']] = $name;
                            $configurator->set('sharelist', $sharelist, $current_user);
                        }
                        //throw new \Exception(print_r($sharelist));
                    }
                    break;
            }

            // flush
            $this->getDoctrine()->getEntityManager()->flush();

            return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array(
                'id' => $id,
            )));
        }

        return $this->render('FTFSServiceBundle:ServiceTicketSharelist:sharelist_add_form.html.twig', array(
            'id' => $id,
            'prefix' => $this->getRoutingPrefix(),
            'sharelist_add_form' => $form->createView(),
            'available_list' => $available_list,
        ));
    }

    public function bodyMenuCountAction()
    {
        $session = $this->getRequest()->getSession();
        $filter = $this->getRequest()->query->get('status');
        $filter = $filter ? $filter : 'current';
        $entityList = $this->getEntityList(array(
            'pagination' => false,
        ));
        if($entityList) {
            $count = count($entityList['entities']);
            // refresh the session
            $name = 'counter-menu-'.$filter;
            if($session->has($name)) {
                $session->remove($name);
            }
            $session->set($name, $count);
            return new \Symfony\Component\HttpFoundation\Response($count);
        }
        return new \Symfony\Component\HttpFoundation\Response(0);
    }

    /** 
     *
     */
    public function sharelistDeleteAction($id, $email)
    {
        // access control ...
        $ticket = $this->getEntity('sharelist_delete', $id);

        $ticket->removeSharelist($email);

        $em = $this->getDoctrine()->getEntityManager();
        $em->flush();

        return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array(
            'id' => $id,
        )));
    }

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

        // prepare the attachment
        $content = file_get_contents($attachment->getAbsolutePath());
        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->headers->set('Content-Type', 'mime/type');
        $response->headers->set('Content-Disposition', 'attachment;filename='.$attachment->getName());
        $response->setContent($content);

        // flash notification
        $this->notify('attachment_download'); 

        // return the resouces
        return $response;
    }

    /** 
     * attachement file management: upload an attachment
     */
    public function attachmentUploadAction($id)
    {
        $action = $this->getRequest()->get('action');
        //throw new \Exception($action);
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
            'action' => $action,
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
        //****************************************************************************
        //***ToDo: prob transaction
        $em->flush();

        // flash notification
        $this->notify('attachment_delete'); 

        return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array(
            'id' => $id,
        )));
    }

    /** 
     * observation attachement file management: upload an attachment to an observation
     */
    public function observationAttachmentUploadAction($id)
    {
        $request = $this->getRequest();
        $action = $request->get('action');
        $action = urldecode($action);
        //throw new \Exception($action);
        $obid = $request->get('obid');
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
                // find obid
                $ob = $em->getRepository('FTFSServiceBundle:ServiceTicketObservation')->find($obid);
                if($ob) {
                    $content = $ob->getContent();
                    if(!array_key_exists('attachment', $content)) {
                        $content['attachment'] = array();
                    }
                    $content['attachment'][] = $attachment->getId();
                    $ob->setContent($content);
                    $em->flush();
                }
            }
            return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array(
                'id' => $id,
            )));
        }

        return $this->render('FTFSServiceBundle:ServiceTicketAttachment:upload_form.html.twig', array(
            'id' => $id,
            'prefix' => $this->getRoutingPrefix(),
            'attachment_upload_form' => $form->createView(),
            'action' => $action,
        ));
    }

    /**
     * AJAX provider: New Ticket Observation 
     */
    public function observationAddAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $ticket = $this->getEntity('observation_add', $id);
        $request = $this->get('request');

        foreach($ticket->getDevices() as $device) {
            $name = $device->getLocalSite();
            $sites[$name] = $name;
        }
        $sites = array_unique($sites);

        $add_to_id = $request->get('add-to-id');

        $container = array(
            'observation' => 'observation detail',
            'intervention_agent' => $this->get('security.context')->getToken()->getUser(),
            'intervention_from' => new \DateTime('now'),
            'intervention_to' =>  new \DateTime('now'),
        );
        $form = $this->createFormBuilder($container)
            // message
            ->add('message_message', 'textarea')
            // intervention
            ->add('intervention_site', 'choice', array(
                'choices' => $sites,
            ))
            ->add('intervention_category', 'choice', array(
                'choices' => array(
                    'telephone' => 'telephone',
                    'in site' => 'in site',
                ),
            ))
            ->add('intervention_agent', 'text')
            ->add('intervention_from', 'datetime', array(
                'date_widget' => 'single_text',
                'time_widget' => 'choice',
            ))
            ->add('intervention_to', 'datetime', array(
                'date_widget' => 'single_text',
                'time_widget' => 'choice',
            ))
            ->add('intervention_report', 'textarea')
            // logistic
            ->add('logistic_operator', 'text')
            ->add('logistic_package_name', 'text')
            ->add('logistic_at', 'text', array(
                'required' => false,
            ))
            ->add('logistic_operation', 'choice', array(
                'choices' => array(
                    'Send' => 'Send',
                    'Receive' => 'Receive',
                ),
            ))
            ->add('logistic_by', 'choice', array(
                'choices' => array(
                    'Nippon Express' => 'Nippon Express',
                    'DHL' => 'DHL',
                ),
            ))
            ->getForm();

        if('POST'===$request->getMethod())
        {
            $form->bindRequest($request);

            $observation = new \FTFS\ServiceBundle\Entity\ServiceTicketObservation();
            $observation->setSendAt(new \DateTime('now'));
            $observation->setSendBy($this->get('security.context')->getToken()->getUser());

            $data = $form->getData();
            $type = $request->get('type');
            $content['type']=$type;
            $flush = false;
            switch($type) {
                case 'message':
                    $message = $data['message_message'];
                    if($message == '') {
                        break;
                    }
                    $attach = $em->getRepository('FTFSServiceBundle:ServiceTicketObservation')
                                ->find($add_to_id);
                    if($attach)
                    {
                        $observation->setAttachTo($attach);
                    }
                    $content['message'] = $message;
                    $flush = true;
                    break;
                case 'intervention':
                    $site = $data['intervention_site'];
                    $category = $data['intervention_category'];
                    $from = $data['intervention_from'];
                    $to = $data['intervention_to'];
                    $agent = $data['intervention_agent'];
                    $report = $data['intervention_report'];
                    if($report == '') {
                        break;
                    }

                    $content['type'] = $type;
                    $content['site'] = $site;
                    $content['category'] = $category;
                    $content['timer'] = 'intervention_added';
                    $content['from'] = $from;
                    $content['to'] = $to;
                    $content['agent'] = $agent;
                    $content['report'] = $report;
                    $flush = true;
                    break;
                case 'logistic':
                    $by = $data['logistic_by'];
                    $at = $data['logistic_at'];
                    $operation = $data['logistic_operation'];
                    $package = $data['logistic_package_name'];
                    $operator = $data['logistic_operator'];
                    $content['by'] = $by;
                    $content['at'] = $at;
                    $content['operation'] = $operation;
                    $content['operator'] = $operator;
                    $content['package'] = $package;
                    $flush = true;
                    break;
                default:
            }
            //throw new \Exception(print_r($content));
            if($flush) {
                $observation->setContent($content);
                //$ticket->addServiceTicketObservation($observation);
                $observation->setTicket($ticket);
                $em->persist($observation);
                $em->flush();
                return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array('id' => $id)));
            }
        }

        return $this->render('FTFSServiceBundle:ServiceTicketObservation:add_form.html.twig', array(
            'id' => $id,
            'prefix' => $this->getRoutingPrefix(),
            'add_to_id' => $add_to_id,
            'ticket' => $ticket,
            'observation_add_form' => $form->createView(),
        ));
    }

    public function devicesAddAction($id)
    {
        $ticket = $this->getEntity('devices_add', $id);
        $device = new \FTFS\AssetBundle\Entity\Device;
        $ticket->addDevice($device);
        $form = $this->createForm(new \FTFS\AssetBundle\Form\DeviceType, $device);

        if($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            if($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($device);
                $em->flush();
            }
            return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array(
                'id' => $id,
            )));
        }

        return $this->render('FTFSServiceBundle:ServiceTicketDevices:devices_add_form.html.twig', array(
            'id' => $id,
            'prefix' => $this->getRoutingPrefix(),
            'devices_add_form' => $form->createView(),
            'action' => $this->generateUrl($this->getRoutingPrefix().'_devices_add', array(
                'id' => $id
            )),
        ));
    }

    public function devicesEditAction($id, $device_id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $device = $em->getRepository('FTFSAssetBundle:Device')->find($device_id);
        if(!$device) {
            throw $this->createNotFoundException('Device to be edited not found !');
        }
        $form = $this->createForm(new \FTFS\AssetBundle\Form\DeviceType, $device);

        if($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            if($form->isValid()) {
                $em->flush();
            }
            return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array(
                'id' => $id,
            )));
        }

        return $this->render('FTFSServiceBundle:ServiceTicketDevices:devices_add_form.html.twig', array(
            'id' => $id,
            'prefix' => $this->getRoutingPrefix(),
            'devices_add_form' => $form->createView(),
            'action' => $this->generateUrl($this->getRoutingPrefix().'_devices_edit', array(
                'id' => $id,
                'device_id' => $device_id,
            )),
        ));
    }

    public function devicesDeleteAction($id, $device_id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $this->getEntity('devices_delete', $id);
        $device = $em->getRepository('FTFSAssetBundle:Device')->find($device_id);
        if(!$device) {
            throw $this->createNotFoundException('Device to be deleted not found !');
        }
        $entity->deleteDevice($device);
        $em->remove($device);
        $em->flush();
        return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array('id' => $id)));
    }

    // ajax ticket timer list
    public function ticketTimerListAction($id)
    {
        $entity = $this->getEntity('ticket_timer_list', $id);
        $timers = $this->getDoctrine()->getEntityManager()
            ->getRepository('FTFSServiceBundle:ServiceTicketTimer')->findBy(
            array(
                'ticket' => $entity->getName()
            ),
            array(
                'quand' => 'desc'
            )
        );

        return $this->render('FTFSServiceBundle:ServiceTicketTimer:content.html.twig', array(
            'timers' => $timers,
            'prefix' => $this->getRoutingPrefix(),
        ));
    }


    public function generateRMAAction($id)
    {
        $entity = $this->getEntity('generate_rma', $id);

        $em = $this->getDoctrine()->getEntityManager();
        $rma = new \FTFS\ServiceBundle\Entity\RMA;
        $rma->setTicket($entity);
        $rma->setName('generating...');
        $em->persist($rma);
        $em->flush();
        //throw new \Exception($rma->getId());
        $rma->setName($this->get('ftfs_servicebundle.name_generator')->getNextRMAName($rma->getId()));
        $em->flush();

        return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array('id' => $id)));
    }

    public function showByNameAction($name)
    {
        $entity = $this->getDoctrine()->getEntityManager()->getRepository('FTFSServiceBundle:ServiceTicket')->findOneByName($name);
        if(!$entity) {
            throw $this->createNotFoundException('Service ticket: '.$name.' not found !');
        }
        return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array('id' => $entity->getId())));
    }

    public function printAction($id)
    {
        $entity = $this->getEntity('print', $id);
        $timer = $this->getDoctrine()->getEntityManager()
            ->getRepository('FTFSServiceBundle:ServiceTicketTimer')
            ->findByTicket($entity->getName());
        return $this->render($this->getViewPath().':print.html.twig', array(
            'subject' => $entity,
            'timer' => $timer,
        ));
        
    }
}
