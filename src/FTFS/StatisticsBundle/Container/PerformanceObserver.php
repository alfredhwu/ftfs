<?php

namespace FTFS\StatisticsBundle\Container;

use Doctrine\ORM\EntityManager;
use \FTFS\ServiceBundle\Entity\ServiceTicket;
use \FTFS\ServiceBundle\Entity\Service;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * 
 **/
class PerformanceObserver
{
    private $em;
    
    function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     *
     * return array
     */
    public function getServiceTicketIndicators(ServiceTicket $ticket)
    {
        // rendering ticket indicators according to 
        // -- client company
        // -- service type
        $indicators = array();
        $timers = $this->em->getRepository('FTFSServiceBundle:ServiceTicketTimer')->findBy(
            array(
                'ticket' => $ticket->getName(),
            ),
            array(
                'quand' => 'asc',
            )
        );
        // general client
        // help desk
        // calculation
        $events = array();
        // loop over time, ordered by time asc
        $at_first_telephone = -1;
        $at_first_on_site = -1;
        //throw new \Exception(print_r($timers));
        foreach($timers as $timer) {
            switch($timer->getAlias()) {
                case 'at_submit':
                    $at_submit = $timer->getQuand();
                    break;
                case 'at_open':
                    $at_open = $timer->getQuand();
                    break;
                case 'at_close':
                    $at_close = $timer->getQuand();
                    break;
                case 'at_investigation_telephone':
                    if(-1===$at_first_telephone) {
                        $at_first_telephone = $timer->getQuand();
                    }
                    break;
                case 'at_investigation_on_site':
                    if(-1===$at_first_on_site) {
                        $at_first_on_site = $timer->getQuand();
                    }
                    break;
            }

            $events[] = array(
                'alias' => $timer->getAlias(),
                'quand' => $timer->getQuand(),
            );
        }

        if(!isset($at_submit) && !isset($at_open)) {
            throw new \Exception('error !');
        }

        $at_submit = isset($at_submit) ? $at_submit : $at_open;
        $gross_delay_open = date_diff($at_open, $at_submit);
        $gross_delay_close = date_diff($at_close, $at_submit);
        if(-1===$at_first_telephone) {
            $gross_delay_first_telephone = new \DateInterval('PT0S');
        }else{
            $gross_delay_first_telephone = date_diff($at_first_telephone, $at_submit);
        }
        if(-1===$at_first_on_site) {
            $gross_delay_first_on_site = new \DateInterval('PT0S');
        }else{
            $gross_delay_first_on_site = date_diff($at_first_on_site, $at_submit);
        }

        //throw new \Exception(print_r($gross_delay_first_telephone));
        //throw new \Exception($gross_delay_first_telephone instanceof \DateInterval);

        $indicators['gross_delay_open'] = $gross_delay_open;
        $indicators['gross_delay_close'] = $gross_delay_close;
        $indicators['gross_delay_first_telephone'] = $gross_delay_first_telephone;
        $indicators['gross_delay_first_on_site'] = $gross_delay_first_on_site;
        return $indicators;
    }

    /**
     *
     * return array
     */
    public function getStatisticsByClient(UserInterface $client, Service $servicetype=null)
    {
        // mean, median, mode, range
        $service = $servicetype ? $servicetype->getId() : -1;
        $statistics = array();
        $samples = $this->em->getRepository('FTFSServiceBundle:ServiceTicket')
            ->findBy(array(
                'requested_by' => $client->getId(),
                'service' => $service,
            ));
        $samples = $this->em->getRepository('FTFSServiceBundle:ServiceTicket')->findAll();
        $subject = array(
            'gross_delay_open' => array(),
            'gross_delay_close' => array(),
        );
        foreach($samples as $sample) {
            $indicators = $sample->getIndicators();
            if(array_key_exists('gross_delay_open', $indicators)) {
                $subject['gross_delay_open'][] = $this->time_interval_to_integer($indicators['gross_delay_open']);
            }
        }
        throw new \Exception(print_r($subject));

        return $statistics;
    }

    private function integer_to_time_interval($integer)
    {
    }

    private function time_interval_to_integer(\DateInterval $interval)
    {
        return 1;
    }

    /**
     *
     * return array
     */
    public function getStatisticsByClientCompany($client_company, Service $servicetype=null)
    {
        $statistics = array();

        return $statistics;
    }
}
