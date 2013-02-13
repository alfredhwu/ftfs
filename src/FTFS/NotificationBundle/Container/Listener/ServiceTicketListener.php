<?php

namespace FTFS\NotificationBundle\Container\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use FTFS\ServiceBundle\Entity\ServiceTicket;
use FTFS\ServiceBundle\Entity\ServiceTicketAttachment;
use FTFS\ServiceBundle\Entity\ServiceTicketObservation;

/**
 *
 * listen service ticket change event
 */
class ServiceTicketListener
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    // helper: calling notification services
    private function notify($event_key, array $action)
    {
        /*
        $this->container->get('ftfs_notification.notifier.event_registration_notifier')
            ->register($event_key, $current_user, $action);
         */
        $actor = $this->container->get('security.context')->getToken()->getUser();
        $this->container->get('ftfs_notification.event_notifier')
            ->notify($event_key, $actor, $action);
    }

    // helper: generating new action
    private function generateAction($action_name, $entity, $entityManager)
    {
        $action['name'] = $action_name;
        $metadata = $entityManager->getClassMetadata(get_class($entity));
        $action['serviceticket_class'] = $metadata->getName();
        //$action['serviceticket_id'] = $metadata->getIdentifierValues($entity);
        //$action['serviceticket_id'] = $entity->getName();
        $action['serviceticket_name'] = $entity->getName();
        return $action;
    }

    // lister ///////////////////////////////////////////////////////////////////////////
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if($entity instanceof ServiceTicket) {
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if($entity instanceof ServiceTicket) {
        }

        // listen ServiceTicketObservation new entity
        // event trigger by new ServiceTicketObservation persist
        if($entity instanceof ServiceTicketObservation) {
            // if timer trigger observation
            $content = $entity->getContent();

            // event type redirection
            // defined in observation.content.type
            // in order to prevent duplication, ignore the same event in the ServiceTicket notification
            $ignore = false;
            switch($content['type']) {
                case 'message':
                    $action_name = 'message_sended';
                    break;
                case 'intervention':
                    $action_name = 'intervention_added';
                    break;
                case 'logistics':
                    $action_name = 'logistics_added';
                    break;
                case 'pend':
                    $action_name = 'pended';
                    break;
                case 'continue':
                    $action_name = 'continued';
                    break;
                case 'close_report':
                    $action_name = 'closed';
                    break;
                case 'reopen':
                    $action_name = 'reopened';
                    break;
                default:
                    $ignore = true;
            }

            if(!$ignore) {
                // create fictif ticket
                $action = $this->generateAction($action_name, $entity->getTicket(), $entityManager);
                $action['observation'] = $entity->getContent(); 
                if($entity->getAttachTo()) {
                    $action['observation_to'] = $entity->getAttachTo()->getSendBy();
                }else{
                    $action['observation_to'] = null;
                }
                //throw new \Exception($type);
                $this->notify('event.serviceticket.'.$action_name, $action);
            }
        }

        // listen ServiceTicketAttachment new entity
        // event trigger by new ServiceTicketObservation persist
        if($entity instanceof ServiceTicketAttachment) {
            $action = $this->generateAction('attachment_uploaded', $entity->getTicket(), $entityManager);
            $action_name = $action['name'];
            $action['attachment_name'] = $entity->getName(); 
            $this->notify('event.serviceticket.'.$action_name, $action);
        }

        // ToDo: possibly devices listner here
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        // listen ServiceTicket new entity
        if($entity instanceof ServiceTicket) {

            /*
            $action = $this->generateAction('created', $entity, $entityManager);
            // redirect creation event
            switch($entity->getStatus()) {
                case 'submitted':
                    $option = 'create.submit';
                    break;
                case 'opened':
                    $option = 'create.open';
                    break;
                default:
                    $option = 'create.only';
            }
             */
            switch($entity->getStatus()) {
                case 'submitted':
                    $option = 'create.submit';
                    $action = $this->generateAction('submitted', $entity, $entityManager);
                    break;
                case 'opened':
                    $option = 'create.open';
                    $action = $this->generateAction('opened', $entity, $entityManager);
                    break;
                default:
                    $option = 'create.only';
                    $action = $this->generateAction('created', $entity, $entityManager);
            }

            // stocking for generating a pseudo entity later
            // otherwise, probleme with transferation of entity
            $action['option']=$option;
            $action['serviceticket_reference_client']=$entity->getReferenceClient();
            $action['serviceticket_status']=$entity->getStatus();
            $action['serviceticket_last_modified_at']=$entity->getLastModifiedAt();
            $action['serviceticket_created_at']=$entity->getCreatedAt();
            $action['serviceticket_requested_at']=$entity->getRequestedAt();
            $action['serviceticket_requested_by']=$entity->getRequestedBy()->getId();
            $action['serviceticket_requested_via']=$entity->getRequestedVia();
            $action['serviceticket_summary']=$entity->getSummary();
            $action['serviceticket_detail']=$entity->getDetail();
            $action['serviceticket_service']=$entity->getService();
            $action['serviceticket_severity']=$entity->getSeverity();
            $action['serviceticket_priority']=$entity->getPriority();
            $action['serviceticket_assigned_to']=$entity->getAssignedTo()?$entity->getAssignedTo()->getId():-1;
            $action_name = $action['name'];

            $this->notify('event.serviceticket.'.$action_name, $action);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if($entity instanceof ServiceTicket) {
            $session =  $this->container->get('session');
            // and save change_set 
            $change_set = array();
            foreach($args->getEntityChangeset() as $fieldname => $fieldchange){
                if(substr($fieldname, -3)!=='_at' && $fieldchange[0] != $fieldchange[1]) {    // filter out timestamp changes
                    $change_set[$fieldname] = $fieldchange;
                }
            }
            if(count($change_set)>0) {
                $session->set('change_set['.$entity->getName().']', $change_set);
            }elseif($session->has('change_set['.$entity->getName().']')) {
                $session->remove('change_set['.$entity->getName().']');
            }
            //$test = $session->get('change_set['.$entity->getName().']');
 //           throw new \Exception($test['service'][1]);
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if($entity instanceof ServiceTicket) {
            $session =  $this->container->get('session');
            if($session->has('change_set['.$entity->getName().']')) {

                $change_set = $session->get('change_set['.$entity->getName().']');
                
                if(array_key_exists('status', $change_set)){
                    $action = $this->generateAction($change_set['status'][1], $entity, $entityManager);
                }elseif(array_key_exists('pending', $change_set)){
                    if($entity->getPending()) {
                        $action = $this->generateAction('pended', $entity, $entityManager);
                    }else{
                        $action = $this->generateAction('continued', $entity, $entityManager);
                    }
                }elseif(array_key_exists('assigned_to', $change_set)){
                    $action = $this->generateAction('reassigned', $entity, $entityManager);
                }elseif(array_key_exists('share_list', $change_set)){
                    $action = $this->generateAction('share_list_updated', $entity, $entityManager);
                }else{
                    $action = $this->generateAction('updated', $entity, $entityManager);
                }

                // ignore the following event;
                // notified in postPersist(), ServiceTicketObservation listener
                if(!in_array($action['name'], array('closed', 'reopened', 'pended', 'continued', 'share_list_updated'))) {
                    $action_name = $action['name'];
                    $action['change_set'] = $change_set;
                    $this->notify('event.serviceticket.'.$action_name, $action);
                }

                $session->remove('change_set['.$entity->getName().']');
            }
        }
    }
}
