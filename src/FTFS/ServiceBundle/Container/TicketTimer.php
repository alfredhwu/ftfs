<?php

namespace FTFS\ServiceBundle\Container;

use Doctrine\ORM\EntityManager;
use DateTime;
use \FTFS\ServiceBundle\Entity\ServiceTicket;
use \FTFS\UserBundle\Entity\User;

/**
 * 
 **/
class TicketTimer
{
    private $em;
    
    function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     *  tic, tac, toc ==> client request
     *  ctic, ctac    ==> agent request
     */
    public function trigger(DateTime $quand, User $qui, $quoi, $pourquoi, ServiceTicket $ticket, $flag = 'tic', $alias='')
    {
        $timer = new \FTFS\ServiceBundle\Entity\ServiceTicketTimer;
        $timer->setQuand($quand);
        $timer->setQui($qui);
        $timer->setQuoi($quoi);
        $timer->setPourquoi($pourquoi);
        $timer->setFlag($flag);  // tic, tac
        $timer->setAlias($alias); // at_submit, at_open, at_close

        $timer->setTicket($ticket);
        //throw new \Exception($quoi);
        $this->em->persist($timer);
        $this->em->flush();
//        throw new \Exception($quand->format('Y-m-d H:i:s'));
    }
}
