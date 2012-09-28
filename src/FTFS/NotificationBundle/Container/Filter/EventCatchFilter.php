<?php

namespace FTFS\NotificationBundle\Container\Filter;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\User\UserInterface;
use FTFS\NotificationBundle\Entity\Event;
use FTFS\NotificationBundle\Entity\NotificationMethod;

/**
 *
 * event catch filter 
 */
class EventCatchFilter
{
    private $em;
    private $entityRepository;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
        $this->entityRepository = $entityManager->getRepository('FTFSNotificationBundle:EventCatchFilter');
    }

    public function getNotificationMethods(UserInterface $user, Event $event) {
        //
        $filters = $this->entityRepository->findBy(array(
                    'user' => $user->getId(), 
                    'event' => $event->getId(),
                ));
        $methods_allow = array();
        $methods_deny = array();
        foreach($filters as $filter) {
            if($filter->getAllow()) {
                $methods_allow[] = $filter->getMethod();
            }else{
                $methods_deny[] = $filter->getMethod();
            }
        }

        //
        $default_methods_allow = array();
        $default_filters = $this->em->getRepository('FTFSNotificationBundle:EventCatchFilterDefault')->findBy(array(
            'event' => $event->getId(),
        ));
        foreach($default_filters as $filter) {
            if(!in_array($filter->getMethod(), $methods_deny)) {
                $default_methods_allow[] = $filter->getMethod();
            }
        }

        $methods = array_unique(array_merge($methods_allow, $default_methods_allow));

        return $methods;
    }
}
