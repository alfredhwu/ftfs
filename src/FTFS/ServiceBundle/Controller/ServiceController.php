<?php

namespace FTFS\ServiceBundle\Controller;

use FTFS\CrudBundle\Controller\CrudController as BaseController;


class ServiceController extends BaseController
{
    public function __construct()
    {
        parent::__construct("FTFS/ServiceBundle/Service");
    }

    protected function initEntity($entity)
    {
        $entity->setRequestReceivedAt(new \DateTime('now'));
        $entity->setOpenedAt(new \DateTime('now'));
        $entity->setStatus("opened");
        return $entity;
    }
}
