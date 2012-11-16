<?php

namespace FTFS\ServiceBundle\Controller;

use FTFS\CrudBundle\Controller\CrudController as BaseController;


class ServiceTicketController extends BaseController
{
    public function __construct()
    {
        parent::__construct(array(
            'model' => "FTFS/ServiceBundle/ServiceTicket"
        ));
    }

    protected function preFlushEntity($entity, $action, $request)
    {
        switch($action)
        {
            case 'new':
                $entity->setRequestedAt(new \DateTime('now'));
                $entity->setCreatedAt(new \DateTime('now'));
                $entity->setOpenedAt(new \DateTime('now'));
                $entity->setLastModifiedAt(new \DateTime('now'));
                $entity->setStatus("opened");
                break;
        }
    }

    protected function getEntityList(array $options = array())
    {
        return $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())->findBy(
            array(
            ),
            array(
                'last_modified_at' => 'desc',
            )
        );
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
        $ticket = $this->getEntity('observation_add', $id);
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request');
        $add_to_id = $request->get('add-to-id');

        $container = array('observation' => 'observation detail');
        $form = $this->createFormBuilder($container)
            // message
            ->add('message_message', 'textarea')
            // intervention
            ->add('intervention_site', 'text')
            ->add('intervention_agent', 'text')
            ->add('intervention_from', 'text', array(
                'required' => false,
            ))
            ->add('intervention_to', 'text', array(
                'required' => false,
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
            $observation->setTicket($ticket);

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
                    $from = $data['intervention_from'];
                    $to = $data['intervention_to'];
                    $agent = $data['intervention_agent'];
                    $report = $data['intervention_report'];
                    if($report == '') {
                        break;
                    }
                    $content['site'] = $site;
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
}
