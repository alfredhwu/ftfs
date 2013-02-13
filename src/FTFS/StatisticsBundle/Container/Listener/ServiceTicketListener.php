<?php

namespace FTFS\StatisticsBundle\Container\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use FTFS\ServiceBundle\Entity\ServiceTicket;
use FTFS\ServiceBundle\Entity\ServiceTicketObservation;
use Symfony\Component\Security\Core\User\UserInterface;

use FTFS\NotificationBundle\Entity\EventLog;

/**
 * 
 **/
class ServiceTicketListener
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    private function timer($qui, $quand, $quoi, $pourquoi, ServiceTicket $ticket, $alias, $flag='')
    {
        //$quand = new \DateTime('now');
        //$qui = $this->container->get('security.context')->getToken()->getUser();

        $timer = new \FTFS\ServiceBundle\Entity\ServiceTicketTimer;
        $timer->setQuand($quand);
        $timer->setQui($qui);
        $timer->setQuoi($quoi);
        $timer->setPourquoi($pourquoi);
        $timer->setFlag($flag);  // tic, tac
        $timer->setAlias($alias); // at_submit, at_open, at_close

        $timer->setTicket($ticket);

        //throw new \Exception('blabla');
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->persist($timer);
        $em->flush();
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if($entity instanceof EventLog) {
            $qui = $entity->getActor();
            $quand = $entity->getActedAt();
            $action = $entity->getAction();

            $ignore = true;
            //throw new \Exception(print_r($action));
            switch($action['name']) {
                case 'created':
                    $quoi = 'ticket created';
                    $reason = 'new created';
                    $flag = '';
                    $alias = 'at_create';
                    $ignore = false;
                    break;
                case 'submitted':
                    $quoi = 'submitted';
                    $reason = 'new created';
                    $flag = 'tic';
                    $alias = 'at_submit';
                    $ignore = false;
                    break;
                case 'opened':
                    $quoi = 'ticket opened';
                    $reason = 'new created';
                    $flag = '';
                    $alias = 'at_open';
                    $ignore = false;
                    break;
                case 'message_sended':
                    // 
                    break;
                case 'intervention_added':
                    $content = $action['observation'];
                    $qui = $content['agent'];
                    $quand = $content['from'];
                    $reason = $content['report'];
                    $flag = '';
                    if($content['category']==='telephone') {
                        $quoi = 'telephone investigation added';
                        $alias = 'at_investigation_telephone';
                    }else{
                        $quoi = 'on site investigation added';
                        $alias = 'at_investigation_on_site';
                    }
                    $ignore = false;
                    break;
                case 'logistics':
                    throw new \Exception('not available yet');
                    break;
                case 'pended':
                    $quoi = 'ticket pended';
                    $reason = $action['observation']['reason'];
                    $flag = 'tac';
                    $alias = 'at_pend';
                    $ignore = false;
                    break;
                case 'continued':
                    $quoi = 'ticket continued';
                    $reason = $action['observation']['reason'];
                    $flag = 'tic';
                    $alias = 'at_continue';
                    $ignore = false;
                    break;
                case 'closed':
                    $quoi = 'ticket closed';
                    $reason = 'We have closed your ticket. Please refer to the close report.';
                    $flag = 'tac';
                    $alias = 'at_close';
                    $ignore = false;
                    break;
                case 'reopened':
                    $quoi = 'ticket reopened';
                    $reason = $action['observation']['reason'];
                    $flag = '';
                    $alias = 'at_reopen';
                    $ignore = false;
                    break;
            }

            if(!$ignore) {
                $this->timer($qui, $quand, $quoi, $reason, $this->getSubject('serviceticket', $action), $alias, $flag);
            }
        }
    }

    /**
     *
     * Extract subject classes form $action parameters of $eventlog
     */
    protected function getSubject($subject, array $action)
    {
        if(array_key_exists($subject.'_class', $action) && 
            array_key_exists($subject.'_name', $action)) {
                $entity = $this->container->get('doctrine.orm.entity_manager')->getRepository($action[$subject.'_class'])
                    ->findOneBy(array('name' => $action[$subject.'_name']));
                if($entity) {
                    return $entity;
                }else{
                    return $this->getNewTicket($subject, $action);
                }
        }
        return null;
    }

    protected function getNewTicket($subject, array $action)
    {
        $entity = new \FTFS\ServiceBundle\Entity\ServiceTicket;
        $entity->setName($action['serviceticket_name']);
        $entity->setReferenceClient($action['serviceticket_reference_client']);
        $entity->setStatus($action['serviceticket_status']);
        $entity->setLastModifiedAt($action['serviceticket_last_modified_at']);
        $entity->setCreatedAt($action['serviceticket_created_at']);
        $entity->setRequestedAt($action['serviceticket_requested_at']);
        $entity->setRequestedBy($this->container->get('doctrine.orm.entity_manager')->getRepository('FTFSUserBundle:User')->find($action['serviceticket_requested_by']));

        $user = $this->container->get('doctrine.orm.entity_manager')->getRepository('FTFSUserBundle:User')->find($action['serviceticket_assigned_to']);
        if($user) {
            $entity->setAssignedTo($user);
        }

        $entity->setSummary($action['serviceticket_summary']);
        $entity->setDetail($action['serviceticket_detail']);
        $entity->setService($action['serviceticket_service']);
        $entity->setSeverity($action['serviceticket_severity']);
        $entity->setPriority($action['serviceticket_priority']);
        return $entity;
    }
}
