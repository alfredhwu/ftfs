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
    private $entityRepository;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityRepository = $entityManager->getRepository('FTFSNotificationBundle:EventCatchFilter');
    }

    public function getNotificationMethod(UserInterface $user, Event $event) {
        return $this->entityRepository->findBy(array(
                    'user' => $user->getId(), 
                    'event' => $event->getId(),
                ));
    }
}
