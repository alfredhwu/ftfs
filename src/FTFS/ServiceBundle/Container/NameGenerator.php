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
}
