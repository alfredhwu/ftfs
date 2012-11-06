<?php

namespace FTFS\ServiceBundle\Container;

/**
 * 
 **/
class NameGenerator
{
    
    function __construct()
    {
    }

    public function getNewServiceTicketName()
    {
        $date = new \DateTime('now');
        $date = $date->format('Ymd');
        $name = strtoupper('FTFS'.$date.'ST'.substr(uniqid(),-5));
        return $name;
    }

    public function getNextRMAName($offset)
    {
        $base = 500;
        $next = strval($base + $offset);
        //$date = new \DateTime('now');
        //$date = $date->format('Ymd');
        //$name = strtoupper('RMA'.$date).'______';
        $name = strtoupper('RMA').'-______';
        $name = substr($name, 0, strlen($name)-strlen($next)).$next;
        $name = preg_replace('/_/', '0', $name);
        return $name;
    }
}
