<?php

namespace FTFS\NotificationBundle\Container\Notifier;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManager;
use DateTime;

/**
 * event register 
 * register an event in the event log
 * trigger event notify process by the event listener
 */
class EventRegistrationNotifier
{
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function register($eventkey, UserInterface $actor=null, array $action=null, DateTime $acted_at=null)
    {
        //throw new \Exception('event ['.$eventkey.'] triggered !');

        // find the $event; if not found, throw the exception
        $event = $this->em->getRepository('FTFSNotificationBundle:Event')->findOneBy(array('event_key' => $eventkey));
        if(!$event) {
            throw new \Exception('Undefined event ['.$eventkey.']');
        }
        // get entity manager, register the event $event
        $eventlog = new \FTFS\NotificationBundle\Entity\EventLog($event, $actor, $acted_at, $action);
        $this->em->persist($eventlog);
        //$this->em->flush();

    }
}
