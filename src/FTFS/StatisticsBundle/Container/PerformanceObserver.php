<?php

namespace FTFS\StatisticsBundle\Container;

use Doctrine\ORM\EntityManager;
use \FTFS\ServiceBundle\Entity\ServiceTicket;
use \FTFS\ServiceBundle\Entity\Service;
use Symfony\Component\Security\Core\User\UserInterface;
use \FTFS\UserBundle\Entity\Company;

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
     * of current available ticket indicators
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
        $at_open = isset($at_open) ? $at_open : $at_submit;
        $at_close = isset($at_close) ? $at_close : new \DateTime('now');


        $indicators['gross_delay_close'] = array(
            'type' => 'date_interval',
            'value' => date_diff($at_close, $at_submit),
        );
        $indicators['gross_delay_open'] = array(
            'type' => 'date_interval',
            'value' => date_diff($at_open, $at_submit),
        );
        $indicators['gross_delay_first_telephone'] = -1===$at_first_telephone ? array('type'=>'string', 'value'=>'') : array(
            'type' => 'date_interval',
            'value' => date_diff($at_first_telephone, $at_submit),
        );
        $indicators['gross_delay_first_on_site'] = -1===$at_first_on_site ? array('type'=>'string', 'value'=>'') : array(
            'type' => 'date_interval',
            'value' => date_diff($at_first_on_site, $at_submit),
        );

        return $indicators;
    }

    /**
     *
     * return array (
     *      #service1 => array(
     *          'result' => array(
     *              #ind1 => array(
     *              ),
     *              #ind2 => array(
     *              ),
     *              ...
     *          ),
     *          'data' => array(
     *              #ticket1 => array(
     *                  #ind1 => array(
     *                      'type' => $type,
     *                      'value' => $value,
     *                  ),
     *                  #ind2 => array(
     *                      'type' => $type,
     *                      'value' => $value,
     *                  ),
     *                  ...
     *              ),
     *              #ticket2 => array(
     *                  #ind1 => array(
     *                      'type' => $type,
     *                      'value' => $value,
     *                  ),
     *                  #ind2 => array(
     *                      'type' => $type,
     *                      'value' => $value,
     *                  ),
     *                  ...
     *              ),
     *              ...
     *          ),
     *      ),
     * )
     */
    public function getStatistics(UserInterface $user, Service $servicetype=null)
    {
        // return statistics for a given service
        // if not specified, return statistics for all type of service
        $service = $servicetype ? $servicetype->getId() : -1;
        // mean, median, mode, range
        $statistics = array();
        
        if($service instanceof Service) {
            // get one
            $statistics[$service->getName()] = $this->getStatisticsByService($user, $service);
            //throw new \Exception('one');
        }else{
            // get all service statistics
            $services = $this->em->getRepository('FTFSServiceBundle:Service')->findAll();
            foreach($services as $s) {
                $statistics[$s->getName()] = $this->getStatisticsByService($user, $s);
            }
            //throw new \Exception(print_r($statistics));
        }

        return $statistics;
    }

    /**
     * return statistics for given service type of given user
     */
    private function getStatisticsByService(UserInterface $user, Service $servicetype)
    {
        $statistics = array();
        $indicators = array();
        // general indicators
        $indicators[] = 'gross_delay_open';
        $indicators[] = 'gross_delay_close';
        // ToDo: add your special indicators here
        //
        //

        // check $servicetype
        if(!$servicetype instanceof Service) {
            return $statistics;
        }

        // cote client
        if($user->hasRole('ROLE_CLIENT')) {
            $samples = $this->em->getRepository('FTFSServiceBundle:ServiceTicket')->findBy(
                array(
                    'requested_by' => $user->getId(),
                    'service' => $servicetype->getId(),
                )
            );
            $statistics['my_statistics'] = $this->getStatisticsOfSamples($samples, $indicators);
        }

        if($user->hasRole('ROLE_CLIENT_COMPANY')) {
            $samples = $this->getClientCompanyPerformanceSamples($servicetype, $user->getCompany());
            $statistics['my_company_statistics'] = $this->getStatisticsOfSamples($samples, $indicators);
        }

        // cote agent
        if($user->hasRole('ROLE_ADMIN')) {
            // my statistics
            $samples = $this->em->getRepository('FTFSServiceBundle:ServiceTicket')->findBy(
                array(
                    'assigned_to' => $user->getId(),
                    'service' => $servicetype->getId(),
                )
            );
            $statistics['my_statistics'] = $this->getStatisticsOfSamples($samples, $indicators);
            // all company stat
            $companies = $this->em->getRepository('FTFSUserBundle:Company')->findBy(
                array(
                    'is_client' => true,
                )
            );
            foreach($companies as $company) {
                $samples = $this->getClientCompanyPerformanceSamples($servicetype, $company);

                $statistics['all_client_company_statistics'][$company->getName()] =  $this->getStatisticsOfSamples($samples, $indicators);
            }
        }

        return $statistics;
    }
    
    private function getClientCompanyPerformanceSamples(Service $servicetype, Company $company)
    {
        $samples = $this->em->getRepository('FTFSServiceBundle:ServiceTicket')
            ->createQueryBuilder('t')
            ->leftJoin('t.requested_by', 'u')
            ->leftJoin('u.company', 'c')
            ->where('c.id = :id')
            ->setParameter('id', $company->getId())
            ->andWhere('t.service = :service')
            ->setParameter('service', $servicetype->getId())
            ->getQuery()
            ->getResult()
            ;
        return $samples;
    }

    /** 
     *
     * return statistics for given indicators of the $samples
     * $samples as objects ServiceTicket with indicators
     */
    private function getStatisticsOfSamples(array $samples, array $indicators)
    {
        $statistics = array();
        $data = array();
        //
        foreach($samples as $sample) {
            $sample_indicators = $sample->getIndicators();
            foreach($indicators as $indicator) {
                if(array_key_exists($indicator, $sample_indicators)) {
                    $data[$indicator][] = $this->getIndicatorScalarValue($sample_indicators[$indicator]);
                }
            }
        }

        foreach($indicators as $indicator) {
            // generate $data and $result
            if(array_key_exists($indicator, $data)) {
                $statistics[$indicator] = array(
                    'result' => $this->getStatisticsResult($data[$indicator]),
                    'data' => $data[$indicator],
                );
            }
        }

        return $statistics;
    }

    public function getStatisticsResult(array $data)
    {
        $results = array();

        $count = count($data);
        if($count<=0) {
            return array();
        }

        $mean = array_sum($data)/$count;

        $results['count'] = $count;
        $results['mean'] = $mean;
        $results['max'] = max($data);
        $results['min'] = min($data);
        $squared_sum = 0;
        for($i=0;$i<$count;$i++) {
            $dev = $data[$i]-$mean;
            $squared_sum += $dev*$dev;
        }
        $results['deviation'] = sqrt($squared_sum);
        //throw new \Exception(print_r($results));
        return $results;
    }

    private function getIndicatorScalarValue(array $indicator)
    {
        $type = $indicator['type'];
        $value = $indicator['value'];
        switch($type) {
            case 'date_interval':
                return $this->date_interval_to_scalar($value);
                break;
            case 'scalar':
                return $value;
                break;
        }
        throw new \Exception('unknown indicator type: '.$type.' ! please specify the scalarization of this type in PerformanceObserver');
    }

    private function date_interval_to_scalar(\DateInterval $interval, $unit = 'm')
    {
        $seconds = (($interval->days*24+$interval->h)*60+$interval->i)*60+$interval->s;
        return $seconds/60;
    }

}
